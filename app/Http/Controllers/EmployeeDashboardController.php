<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\LeaveAttachment;
use App\Models\LeaveBalance;
use App\Models\LeaveLedgerEntry;
use App\Models\LeaveRecord;
use App\Models\LeaveType;
use App\Models\Office;
use App\Models\RoutingLog;
use App\Services\FileStorageService;
use App\Services\LeaveDocumentRuleService;
use App\Services\LeaveRecordService;
use App\Services\WorkingDayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * EmployeeDashboardController
 *
 * Manages the Standard Employee self-service portal:
 * - Viewing certified leave balance summary cards (VL, SL, FL, SPL, Wellness Leave).
 * - Filing new Civil Service Form No. 6 leave applications with scannable reference codes (e.g. LV-2026-0042).
 * - Generating printable pre-filled Civil Service Form 6 (Revised 2020).
 * - Viewing personal application tracking history.
 */
class EmployeeDashboardController extends Controller
{
    /**
     * Display the employee self-service dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // HRMU Staff is restricted from employee self-service; redirect to HR management portal
        if ($user && $user->role === 'hrmu_staff') {
            return redirect()->route('hr.leave-records.index');
        }

        // RBAC Isolation: Standard employees are strictly restricted to their own employee profile
        if ($user && $user->role === 'employee') {
            $employeeId = $user->employee_id ?? 1;
        } else {
            // Administrators can review other employee ledgers
            $employeeId = $request->query('employee_id') ?? $user?->employee_id ?? 1;
        }

        $employee = Employee::with(['office', 'balances.leaveType'])->findOrFail($employeeId);

        $currentYear = Carbon::now()->year;

        // Derive certified balances for current year from preloaded employee relation
        $balances = $employee->balances
            ->where('year', $currentYear)
            ->keyBy(fn($b) => $b->leaveType->code);

        // Fetch available leave types for filing
        $leaveTypes = LeaveType::orderBy('type_name', 'asc')->get();

        // Comprehensive calculation of leaves taken by the employee across both
        // official CS Form 41 leave records and active leave applications
        $leaveUsage = $this->getEmployeeLeaveUsage($employee->employee_id, $currentYear);
        $usedByCode = $leaveUsage['usedByCode'];
        $takenItems = $leaveUsage['takenItems'];

        // Remaining entitlement per leave type, shown in the filing modal and limits modal
        $leaveAllowances = $leaveTypes->mapWithKeys(function ($type) use ($employee, $balances, $usedByCode) {
            $isCredit = in_array($type->code, ['VL', 'SL'], true);
            $limit = $type->annual_limit !== null ? (float) $type->annual_limit : null;
            $used = (float) ($usedByCode[$type->code] ?? 0.0);

            return [$type->leave_type_id => [
                'name' => $type->type_name,
                'code' => $type->code,
                'is_credit' => $isCredit,
                'period' => $type->limit_period === 'per_event' ? 'per event' : 'per year',
                'limit' => $limit,
                'used' => round($used, 3),
                'remaining' => $isCredit
                    ? round((float) ($balances->get($type->code)?->balance ?? 0), 3)
                    : ($limit === null ? null : round(max($limit - $used, 0), 3)),
            ]];
        });

        // Fetch personal leave applications ordered by recent (eager-load routing logs for instant 0ms timeline modal)
        $applications = LeaveApplication::with([
            'employee.office',
            'leaveType',
            'currentLocation',
            'latestRoutingLog.office',
            'routingLogs.office',
            'routingLogs.scanner.employee',
            'attachments.verifiedBy.employee',
        ])
        ->where('employee_id', $employee->employee_id)
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        $routingController = app(DocumentRoutingController::class);
        $preloadedTimelines = $applications->getCollection()->mapWithKeys(function ($app) use ($routingController) {
            return [$app->application_id => $routingController->buildApplicationTimelinePayload($app)];
        });

        // Fetch on-screen reminders (upcoming leave start dates and expiring 7-day cancellation windows)
        $reminders = $this->getEmployeeReminders($employee->employee_id);

        // Fetch CSC Form 6 rules map for supporting document auto-determination
        $leaveDocRules = LeaveDocumentRuleService::getClientRulesMap();

        // Calculate Civil Service Commission Monetization Eligibility & Limits:
        // Automatically synced with the employee's CS Form No. 41 leave ledger
        $latestRecord = LeaveRecord::where('employee_id', $employee->employee_id)
            ->orderBy('row_order', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('record_id', 'desc')
            ->first();

        $currentVlBalance = $latestRecord?->vl_balance !== null 
            ? (float) $latestRecord->vl_balance 
            : (float) ($balances->get('VL')?->balance ?? 0.0);

        $currentSlBalance = $latestRecord?->sl_balance !== null 
            ? (float) $latestRecord->sl_balance 
            : (float) ($balances->get('SL')?->balance ?? 0.0);

        $totalCombinedBalance = round($currentVlBalance + $currentSlBalance, 3);

        // CSC Rule 1: Regular Monetization
        // - Employee must hold at least 15.000 VL days.
        // - Allowable days: between 10 and 30 whole days.
        // - Must retain a mandatory 5-day VL buffer (Max = Current VL - 5.000, capped at 30, whole days).
        $isRegularEligible = ($currentVlBalance >= 15.000);
        $regularRemainingNeeded = max(0.000, round(15.000 - $currentVlBalance, 3));
        $regularMaxDays = $isRegularEligible
            ? (int) min(30, max(0, floor($currentVlBalance - 5.000)))
            : 0;
        $regularMinDays = 10;

        // CSC Rule 2: Special Monetization (50% or more of combined VL and SL)
        // - Combined balance (VL + SL) must strictly exceed 30.000 days.
        // - Rounds allowable conversion limits to whole numbers:
        //   Setting the minimum at ceil of 50% (e.g. Math.ceil of 50% of 87.875 = 44 days).
        //   Setting the maximum at floor after retaining the mandatory 5.000-day buffer (e.g. Math.floor after 5.000 buffer = 82 days).
        // - Enforces mandatory justification and uploads for medical, calamity, or educational proof.
        $isSpecialEligible = ($totalCombinedBalance > 30.000);
        $specialRemainingNeeded = max(0.000, round(30.001 - $totalCombinedBalance, 3));
        $specialMinDays = $isSpecialEligible ? (int) ceil($totalCombinedBalance * 0.50) : 0;
        $specialMaxDays = $isSpecialEligible ? (int) max(0, floor($totalCombinedBalance - 5.000)) : 0;

        $monetizationInfo = [
            'current_vl' => $currentVlBalance,
            'current_sl' => $currentSlBalance,
            'combined_total' => $totalCombinedBalance,

            // Overall eligibility
            'is_eligible' => ($isRegularEligible || $isSpecialEligible),
            'remaining_needed' => $regularRemainingNeeded,
            'max_monetizable' => max($regularMaxDays, $specialMaxDays),

            // Regular Monetization Details
            'regular' => [
                'is_eligible' => $isRegularEligible,
                'min_vl_required' => 15.000,
                'remaining_needed' => $regularRemainingNeeded,
                'min_days' => $regularMinDays,
                'max_days' => $regularMaxDays,
                'retained_vl_buffer' => 5.000,
            ],

            // Special Monetization Details
            'special' => [
                'is_eligible' => $isSpecialEligible,
                'min_total_required' => 30.000,
                'remaining_needed' => $specialRemainingNeeded,
                'min_days' => $specialMinDays,
                'max_days' => $specialMaxDays,
                'retained_buffer' => 5.000,
            ],
        ];

        // Available employees for switcher (only needed by admin / HRMU staff)
        $allEmployees = ($user && in_array($user->role, ['admin', 'hrmu_staff'], true))
            ? Employee::with('office')->eligibleForLeave()->orderBy('full_name')->get()
            : collect();

        return view('employee.dashboard', compact(
            'employee',
            'balances',
            'leaveTypes',
            'leaveAllowances',
            'takenItems',
            'applications',
            'preloadedTimelines',
            'reminders',
            'currentYear',
            'allEmployees',
            'leaveDocRules',
            'monetizationInfo'
        ));
    }

    /**
     * File a new leave application (Generates unique reference code & Form 6).
     */
    public function storeLeave(Request $request)
    {
        $isMonetization = in_array($request->input('details_type'), ['monetization', 'monetization_special'], true);

        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,employee_id'],
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,leave_type_id'],
            'other_leave_type' => ['nullable', 'string', 'max:150'],
            'date_of_filing' => ['required', 'date'],
            'declared_salary' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'date_from' => [$isMonetization ? 'nullable' : 'required', 'date'],
            'date_to' => [$isMonetization ? 'nullable' : 'required', 'date', 'after_or_equal:date_from'],
            'days_requested' => ['required', 'numeric', 'min:0.5', 'max:120'],
            'commutation_requested' => ['nullable', 'boolean'],
            'details_type' => ['nullable', 'string', 'max:50'],
            'details_specify' => ['nullable', 'string', 'max:255'],
            'details_of_leave' => ['nullable', 'string', 'max:1000'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:25600'],
        ]);

        if ($isMonetization) {
            $validated['date_from'] = $validated['date_from'] ?? $validated['date_of_filing'];
            $validated['date_to'] = $validated['date_to'] ?? $validated['date_of_filing'];
        }

        $user = Auth::user();
        if ($user && $user->role === 'employee') {
            $validated['employee_id'] = $user->employee_id;
        }

        $employee = Employee::findOrFail((int) $validated['employee_id']);
        if ($employee->isJobOrder()) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Job Order personnel ({$employee->full_name}) are not eligible to file Civil Service leave applications under CSC rules.");
        }

        // STRICT PREVENTION OF DUPLICATE / OVERLAPPING INCLUSIVE DATES FOR A SINGLE EMPLOYEE
        // Note: Excluded for monetization since monetization converts credits into cash without requiring absence from work.
        if (!$isMonetization) {
            $overlap = LeaveApplication::where('employee_id', $validated['employee_id'])
                ->whereNotIn('status', ['Disapproved', 'Cancelled'])
                ->whereNotIn('details_type', ['monetization', 'monetization_special'])
                ->where(function ($query) use ($validated) {
                    $query->where('date_from', '<=', $validated['date_to'])
                          ->where('date_to', '>=', $validated['date_from']);
                })
                ->first();

            if ($overlap) {
                $formattedFrom = Carbon::parse($overlap->date_from)->format('M d, Y');
                $formattedTo = Carbon::parse($overlap->date_to)->format('M d, Y');
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Filing Rejected: You already have an existing leave application ({$overlap->reference_code}) covering the dates {$formattedFrom} to {$formattedTo}. Duplicate or overlapping inclusive dates are strictly prohibited.");
            }
        }

        // LEAVE ENTITLEMENT LIMIT: each leave type is capped per its CSC annual / per-event limit
        $creditError = $this->checkLeaveCreditLimit(
            (int) $validated['employee_id'],
            (int) $validated['leave_type_id'],
            (float) $validated['days_requested'],
            $validated['date_from']
        );
        if ($creditError) {
            return redirect()->back()->withInput()->with('error', $creditError);
        }

        // CSC MONETIZATION OF LEAVE CREDITS VALIDATION (RULE XVI, SEC. 22-23)
        $detailsType = $validated['details_type'] ?? null;
        if (in_array($detailsType, ['monetization', 'monetization_special'], true)) {
            $latestRecord = LeaveRecord::where('employee_id', $employee->employee_id)
                ->orderBy('row_order', 'asc')
                ->orderBy('created_at', 'asc')
                ->orderBy('record_id', 'asc')
                ->get()
                ->last();

            $balances = LeaveBalance::with('leaveType')
                ->where('employee_id', $employee->employee_id)
                ->where('year', Carbon::parse($validated['date_of_filing'])->year)
                ->get()
                ->keyBy(fn($b) => $b->leaveType->code);

            $currentVl = $latestRecord?->vl_balance !== null 
                ? (float) $latestRecord->vl_balance 
                : (float) ($balances->get('VL')?->balance ?? 0.0);

            $currentSl = $latestRecord?->sl_balance !== null 
                ? (float) $latestRecord->sl_balance 
                : (float) ($balances->get('SL')?->balance ?? 0.0);

            $combinedTotal = round($currentVl + $currentSl, 3);
            $daysReq = (float) $validated['days_requested'];

            if ($detailsType === 'monetization') {
                // Regular Monetization Rules:
                // 1. Minimum 15.000 VL days required
                if ($currentVl < 15.000) {
                    $needed = number_format(15.000 - $currentVl, 3);
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Monetization Ineligible: Regular monetization requires holding at least 15.000 Vacation Leave (VL) days pursuant to CSC Omnibus Rules (Rule XVI, Sec. 22). You currently hold {$currentVl} VL days (need {$needed} more).");
                }
                // 2. Whole days only (e.g. 10, 15, 20)
                if (floor($daysReq) != $daysReq) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Monetization Limit: Regular monetization requires entering whole working days only (e.g. 10, 15, 20 days). Fractional days are not permitted.");
                }
                // 3. Between 10.000 and 30.000 days
                if ($daysReq < 10.000 || $daysReq > 30.000) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Monetization Limit: Regular monetization must be between 10 and 30 whole days pursuant to CSC rules.");
                }
                // 4. Mandatory 5-day VL buffer
                $maxAllowable = round($currentVl - 5.000, 3);
                if ($daysReq > $maxAllowable) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Monetization Limit: You must retain a mandatory 5-day Vacation Leave buffer. Maximum allowable monetization for your balance is " . number_format(min(30.000, floor($maxAllowable)), 0) . " whole days.");
                }
            } elseif ($detailsType === 'monetization_special') {
                // Special Monetization Rules (50% or more):
                // 1. Combined VL + SL must strictly exceed 30.000 days
                if ($combinedTotal <= 30.000) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Special Monetization Ineligible: Special monetization of 50% or more requires a combined VL and SL balance strictly exceeding 30.000 days pursuant to CSC Omnibus Rules (Rule XVI, Sec. 23). Current total: {$combinedTotal} days.");
                }
                // 2. Whole days only (e.g. 44, 45, 82)
                if (floor($daysReq) != $daysReq) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Monetization Limit: Special monetization requires entering whole working days only pursuant to CSC rules and standard government payroll accounting. Fractional days are not permitted.");
                }
                // 3. 50% or more of combined accumulated credits (rounded up to whole number)
                $minSpecial = (int) ceil($combinedTotal * 0.50);
                $maxSpecial = (int) max(0, floor($combinedTotal - 5.000));
                if ($daysReq < $minSpecial) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Special Monetization Limit: Special monetization requires availing at least 50% of your total combined leave credits ({$minSpecial} whole days).");
                }
                // 4. Mandatory 5-day buffer retention (rounded down to whole number)
                if ($daysReq > $maxSpecial) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Special Monetization Limit: You cannot exceed {$maxSpecial} whole days to preserve the mandatory 5.000-day buffer.");
                }
            }
        }

        // CSC FORM NO. 6 SUPPORTING DOCUMENT CONDITIONAL EVALUATION
        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);
        $docRequirement = LeaveDocumentRuleService::evaluateRequirements(
            $leaveType->code,
            (float) $validated['days_requested'],
            $validated['date_from'],
            $validated['date_of_filing'],
            $validated['details_type'] ?? null,
            $validated['other_leave_type'] ?? null
        );

        $uploadedFiles = $request->file('attachments') ?? [];
        if ($docRequirement['required'] && count($uploadedFiles) === 0) {
            $reqList = implode('; ', $docRequirement['documents']);
            return redirect()->back()
                ->withInput()
                ->with('error', "Supporting Document Required: Pursuant to CSC Form No. 6 rules for {$docRequirement['leave_title']}, supporting documents must be attached before submitting. Required: {$reqList}");
        }

        // Formulate 6.B details summary string
        $detailsSpecify = trim($validated['details_specify'] ?? '');
        $detailsType = $validated['details_type'] ?? null;
        $detailsSummary = $validated['details_of_leave'] ?? null;

        if (!$detailsSummary && $detailsType) {
            $typeLabels = [
                'within_ph' => 'Within the Philippines',
                'abroad' => 'Abroad',
                'in_hospital' => 'In Hospital',
                'out_patient' => 'Out Patient',
                'women_illness' => 'Special Leave for Women',
                'study_master' => "Completion of Master's Degree",
                'study_bar' => 'BAR/Board Examination Review',
                'monetization' => 'Regular Monetization of Leave Credits',
                'monetization_special' => 'Special Monetization of Leave Credits (50% or more)',
                'terminal' => 'Terminal Leave',
            ];
            $label = $typeLabels[$detailsType] ?? 'Details';
            $detailsSummary = !empty($detailsSpecify) ? "{$label}: {$detailsSpecify}" : $label;
        }

        return DB::transaction(function () use ($validated, $request, $detailsType, $detailsSpecify, $detailsSummary, $leaveType, $docRequirement, $uploadedFiles) {
            $employee = Employee::with('office')->findOrFail($validated['employee_id']);
            $currentYear = Carbon::parse($validated['date_of_filing'])->year;

            // Generate unique, scannable alphanumeric reference code (e.g. LV-2026-0042)
            $lastAppId = DB::table('leave_applications')->max('application_id') ?? 0;
            $nextSequence = str_pad($lastAppId + 1, 4, '0', STR_PAD_LEFT);
            $referenceCode = "LV-{$currentYear}-{$nextSequence}";

            // Ensure reference code is globally <unique></unique>
            while (LeaveApplication::where('reference_code', $referenceCode)->exists()) {
                $lastAppId++;
                $nextSequence = str_pad($lastAppId + 1, 4, '0', STR_PAD_LEFT);
                $referenceCode = "LV-{$currentYear}-{$nextSequence}";
            }

            // Default physical initial location: Employee's department <office></office>
            $initialOfficeId = $employee->office_id;

            $application = LeaveApplication::create([
                'reference_code' => $referenceCode,
                'employee_id' => $employee->employee_id,
                'leave_type_id' => $validated['leave_type_id'],
                'other_leave_type' => $validated['other_leave_type'] ?? null,
                'date_from' => $validated['date_from'],
                'date_to' => $validated['date_to'],
                'date_of_filing' => $validated['date_of_filing'],
                'declared_salary' => isset($validated['declared_salary']) ? round((float) $validated['declared_salary'], 2) : null,
                'days_requested' => round((float) $validated['days_requested'], 3),
                'status' => 'Pending',
                'current_location_office_id' => $initialOfficeId,
                'commutation_requested' => in_array($detailsType, ['monetization', 'monetization_special'], true) ? true : $request->boolean('commutation_requested'),
                'details_type' => $detailsType,
                'details_specify' => $detailsSpecify,
                'details_of_leave' => $detailsSummary,
            ]);

            // Save supporting document attachments via FileStorageService compliant with File Handling <Rules></Rules>
            $attachedCount = 0;
            if (!empty($uploadedFiles)) {
                foreach ($uploadedFiles as $uploadedFile) {
                    if ($uploadedFile && $uploadedFile->isValid()) {
                        FileStorageService::storeAttachment(
                            $uploadedFile,
                            $application,
                            $leaveType->code,
                            $docRequirement['leave_title'] ?? $leaveType->type_name
                        );
                        $attachedCount++;
                    }
                }
            }

            // Initial physical routing log: FILED by employee at department <office></office>
            // Initial physical routing log: FILED by employee at department office
            $userId = Auth::id() ?? $employee->user?->user_id ?? 1;
            $routingRemarks = "Application filed online. Printed CS Form 6 with scannable QR dispatched for Dept Head signature.";
            $isDeptHead = $employee->isDepartmentHead();

            if ($isDeptHead) {
                $routingRemarks = "Application filed online by Department Head. Pursuant to CSC Rule XVI, self-recommendation is prohibited; printed CS Form 6 dispatched to Municipal Administrator / HRMU.";
            } else {
                $routingRemarks = "Application filed online. Printed CS Form 6 with scannable QR dispatched for Dept Head signature.";
            }

            if ($attachedCount > 0) {
                $routingRemarks .= " [{$attachedCount} digital supporting document(s) uploaded for HRMU verification]";
            }

            RoutingLog::create([
                'application_id' => $application->application_id,
                'office_id' => $initialOfficeId,
                'scanned_by' => $userId,
                'action' => 'DISPATCHED',
                'method' => 'SYSTEM',
                'scanned_at' => Carbon::now(),
                'remarks' => $routingRemarks,
            ]);

            // Automatically create corresponding row in employee's existing Leave Record table
            // using submitted application details (strictly prevents duplicate entries)
            $leaveRecordService = app(\App\Services\LeaveRecordService::class);
            $leaveRecordService->createRowForLeaveApplication($application, $userId);

            // Pre-calculate Section 7.A certification values using latest applicable balance
            $sec7A = $leaveRecordService->calculateSection7AData($application);
            $application->update([
                'certified_vl_earned' => $sec7A['vl_total_earned'],
                'certified_vl_less' => $sec7A['vl_less'],
                'certified_vl_balance' => $sec7A['vl_balance'],
                'certified_sl_earned' => $sec7A['sl_total_earned'],
                'certified_sl_less' => $sec7A['sl_less'],
                'certified_sl_balance' => $sec7A['sl_balance'],
                'certified_as_of' => $sec7A['as_of_date'],
                'is_certified' => true,
            ]);

            // Audit Trail
            AuditLog::create([
                'user_id' => $userId,
                'action' => 'FILE_LEAVE_APPLICATION',
                'table_name' => 'leave_applications',
                'record_id' => $application->application_id,
                'details' => json_encode([
                    'reference_code' => $referenceCode,
                    'days_requested' => $application->days_requested,
                    'leave_type_id' => $application->leave_type_id,
                    'attachments_count' => $attachedCount,
                ]),
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'logged_at' => Carbon::now(),
            ]);

            $successMsg = "Leave Application {$referenceCode} successfully filed! You may now print your pre-filled CS Form 6.";
            if ($attachedCount > 0) {
                $successMsg .= " ({$attachedCount} supporting document" . ($attachedCount > 1 ? 's' : '') . " uploaded and queued for HRMU verification).";
                $successMsg .= " ({$attachedCount} supporting document" . ($attachedCount > 1 ? 's' : '') . " uploaded).";
            }

            DB::afterCommit(function () use ($application, $employee, $routingRemarks) {
                \App\Services\LeaveNotificationService::notifyStatusUpdate(
                    $application,
                    'FILED',
                    $employee->office,
                    $routingRemarks
                );
            });

            return redirect()->route('employee.dashboard', ['employee_id' => $employee->employee_id])
                ->with('success', $successMsg)
                ->with('new_reference_code', $referenceCode)
                ->with('new_application_id', $application->application_id);
        });
    }

    /**
     * Aggregates all leaves taken by an employee across BOTH:
     * 1. Official Civil Service Leave Record Card (leave_records)
     * 2. Filed Leave Applications (leave_applications)
     * Returns:
     * - 'usedByCode': map of leave type code => total days taken
     * - 'takenItems': list of itemized leave events taken by the employee
     */
    public function getEmployeeLeaveUsage(int $employeeId, ?int $year = null): array
    {
        $year = $year ?? Carbon::now()->year;
        $leaveTypes = LeaveType::all()->keyBy('code');

        $records = LeaveRecord::with('leaveApplication.leaveType')
            ->where('employee_id', $employeeId)
            ->get();

        $applications = LeaveApplication::with('leaveType')
            ->where('employee_id', $employeeId)
            ->whereNotIn('status', ['Disapproved', 'Cancelled'])
            ->get();

        $service = app(LeaveRecordService::class);

        $usedByCode = [];
        foreach ($leaveTypes as $code => $lt) {
            $usedByCode[$code] = 0.0;
        }

        $takenItems = [];
        $processedAppIds = [];

        // 1. Process Leave Records (Civil Service Form 41 Ledger)
        foreach ($records as $r) {
            $particular = trim($r->particular ?? '');
            if (empty($particular) || str_contains($particular, 'CERTIFIED') || str_contains($particular, 'Earned')) {
                continue;
            }

            $parsed = $service->parseParticular($particular);
            $val = (float)($parsed['value'] ?? 0);
            if ($val <= 0) {
                $val = (float)($r->vl_absence_with_pay ?: $r->sl_absence_with_pay ?: $r->vl_absence_without_pay ?: $r->sl_absence_without_pay ?: 0);
            }

            $code = null;
            if ($r->leaveApplication && $r->leaveApplication->leaveType) {
                $code = $r->leaveApplication->leaveType->code;
                $leaveName = $r->leaveApplication->leaveType->type_name;
            } else {
                $type = $parsed['type'];
                if ($type === 'FORCE_LEAVE' || preg_match('/^\s*FL\b/i', $particular)) $code = 'FL';
                elseif ($type === 'SPECIAL_PRIVILEGE_LEAVE' || preg_match('/^\s*SPL\b/i', $particular)) $code = 'SPL';
                elseif ($type === 'WELLNESS_LEAVE' || preg_match('/^\s*WL\b/i', $particular)) $code = 'WL';
                elseif ($type === 'VACATION_LEAVE' || preg_match('/^\s*VL\b/i', $particular)) $code = 'VL';
                elseif ($type === 'SICK_LEAVE' || preg_match('/^\s*SL\b/i', $particular)) $code = 'SL';
                elseif ($type === 'MATERNITY_LEAVE' || preg_match('/^\s*ML\b/i', $particular)) $code = 'ML';
                elseif ($type === 'PATERNITY_LEAVE' || preg_match('/^\s*PL\b/i', $particular)) $code = 'PL';
                elseif ($type === 'SOLO_PARENT_LEAVE' || preg_match('/^\s*SOLO\b/i', $particular)) $code = 'SOLO';
                elseif ($type === 'VAWC_LEAVE' || preg_match('/^\s*VAWC\b/i', $particular)) $code = 'VAWC';
                elseif ($type === 'STUDY_LEAVE' || preg_match('/^\s*STUDY\b/i', $particular)) $code = 'STUDY';
                elseif ($type === 'REHAB_PRIVILEGE' || preg_match('/^\s*RP\b|REHAB/i', $particular)) $code = 'REHAB';
                elseif ($type === 'WOMEN_LEAVE' || preg_match('/^\s*SLBW\b/i', $particular)) $code = 'SLBW';
                elseif ($type === 'ADOPTION_LEAVE' || preg_match('/^\s*AL\b|ADOPT/i', $particular)) $code = 'ADOPT';
                elseif ($type === 'CALAMITY_LEAVE' || preg_match('/^\s*SECL\b|CALAMITY/i', $particular)) $code = 'CALAMITY';
                elseif ($type === 'LATE' || $type === 'UNDERTIME' || $type === 'ABSENT') $code = 'VL';
                $leaveName = $leaveTypes[$code]->type_name ?? $particular;
            }

            if ($code && isset($usedByCode[$code])) {
                $usedByCode[$code] += $val;
            }

            if ($r->application_id) {
                $processedAppIds[] = $r->application_id;
            }

            if ($val > 0) {
                $source = $r->leaveApplication 
                    ? "Application Ref: {$r->leaveApplication->reference_code} ({$r->leaveApplication->status})" 
                    : "Leave Record Card (CS Form 41)";
                $takenItems[] = [
                    'period' => $r->period ?? 'Year ' . $year,
                    'leave_name' => $leaveName,
                    'code' => $code ?? 'OTHER',
                    'days' => $val,
                    'source' => $source,
                    'date_action' => $r->date_action_taken,
                ];
            }
        }

        // 2. Process Applications not yet in LeaveRecord or unlinked
        foreach ($applications as $a) {
            if (in_array($a->application_id, $processedAppIds)) {
                continue;
            }

            $code = $a->leaveType->code ?? 'OTHER';
            $days = (float)$a->days_requested;
            if (isset($usedByCode[$code])) {
                $usedByCode[$code] += $days;
            }

            $leaveName = $a->leaveType->type_name ?? 'Leave Application';
            if ($code === 'OTHER' && !empty($a->other_leave_type)) {
                $leaveName .= " ({$a->other_leave_type})";
            }

            $takenItems[] = [
                'period' => $a->date_from->format('M d, Y') . ' — ' . $a->date_to->format('M d, Y'),
                'leave_name' => $leaveName,
                'code' => $code,
                'days' => $days,
                'source' => "Application Ref: {$a->reference_code} ({$a->status})",
                'date_action' => "Filed {$a->created_at->format('M d, Y')}",
            ];
        }

        return [
            'usedByCode' => $usedByCode,
            'takenItems' => $takenItems,
        ];
    }

    /**
     * Enforces the CSC entitlement limit of each leave type (leave_types.annual_limit):
     * 'annual' types are counted per calendar year of the leave start date, 'per_event'
     * types (VAWC, maternity, etc.) are counted across all non-cancelled applications.
     * Vacation and Sick Leave are never blocked: when their credits are insufficient the
     * application is still filed and later recorded as absence/undertime without pay.
     * Returns an error message when the limit is exhausted or would be exceeded, else null.
     */
    private function checkLeaveCreditLimit(int $employeeId, int $leaveTypeId, float $daysRequested, string $dateFrom): ?string
    {
        $type = LeaveType::find($leaveTypeId);
        if (!$type || in_array($type->code, ['VL', 'SL'], true) || $type->annual_limit === null) {
            return null;
        }

        $limit = (float) $type->annual_limit;
        $isAnnual = $type->limit_period !== 'per_event';

        $year = Carbon::parse($dateFrom)->year;
        $usage = $this->getEmployeeLeaveUsage($employeeId, $year);
        $used = (float) ($usage['usedByCode'][$type->code] ?? 0.0);

        $remaining = round($limit - $used, 3);
        $period = $isAnnual ? 'annual' : 'per-event';
        $name = $type->type_name;
        $limitText = rtrim(rtrim(number_format($limit, 3), '0'), '.');

        if ($remaining <= 0) {
            return "You cannot take any more {$name}. You have already used the full {$limitText}-day {$period} limit.";
        }
        if (round($daysRequested, 3) > $remaining) {
            return "You cannot file this {$name} application. You requested "
                . number_format($daysRequested, 3) . " day(s) but only "
                . number_format($remaining, 3) . " day(s) remain of the {$limitText}-day {$period} limit.";
        }

        return null;
    }

    /**
     * Compute active on-screen reminders for the employee:
     * 1. Upcoming Leave Reminder: Leave start date (date_from) approaching within 7 days (or ongoing).
     * 2. Cancellation Window Reminder: 7-day statutory cancellation window before expiration.
     *
     * Note: Formatted for on-screen dashboard presentation, structured for upcoming automated email notifications.
     */
    public function getEmployeeReminders(int $employeeId): array
    {
        $now = Carbon::now();
        $today = Carbon::today();

        $activeApps = LeaveApplication::with(['leaveType', 'currentLocation'])
            ->where('employee_id', $employeeId)
            ->whereNotIn('status', ['Cancelled', 'Disapproved'])
            ->orderBy('date_from', 'asc')
            ->get();

        $upcoming = [];
        $cancellations = [];
        $upcomingAppIds = [];

        foreach ($activeApps as $app) {
            $dateFrom = Carbon::parse($app->date_from);
            $dateTo = Carbon::parse($app->date_to);
            $filedAt = Carbon::parse($app->created_at ?? $app->date_of_filing);
            $cancelDeadline = WorkingDayService::getCancellationDeadline($filedAt, 7);
            $leaveName = $app->leaveType->type_name ?? 'Leave';

            $isCancelActive = $now->lte($cancelDeadline);
            $hoursLeft = $isCancelActive ? (int) max(0, floor($now->diffInHours($cancelDeadline, false))) : 0;
            $workdaysLeft = $isCancelActive ? WorkingDayService::getRemainingWorkingDays($cancelDeadline, $now) : 0;
            $formattedDeadline = $cancelDeadline->format('M d, Y • h:i A');

            // 1. UPCOMING LEAVE REMINDER (within next 7 days, or starting today, or ongoing)
            $daysUntilStart = (int) $today->diffInDays($dateFrom, false);
            $formattedDates = $dateFrom->format('M d, Y') . ($dateFrom->ne($dateTo) ? ' – ' . $dateTo->format('M d, Y') : '');

            if ($daysUntilStart >= 0 && $daysUntilStart <= 7) {
                $upcomingAppIds[] = $app->application_id;

                $urgency = $daysUntilStart === 0 ? 'emerald' : ($daysUntilStart <= 2 ? 'amber' : 'sky');
                if ($daysUntilStart === 0) {
                    $badgeText = 'Starts Today';
                    $headline = "Your {$leaveName} begins today";
                } elseif ($daysUntilStart === 1) {
                    $badgeText = 'Starts Tomorrow';
                    $headline = "Your {$leaveName} begins tomorrow";
                } else {
                    $badgeText = "In {$daysUntilStart} Days";
                    $headline = "Your {$leaveName} begins in {$daysUntilStart} days";
                }

                $msg = $app->status === 'Approved'
                    ? "Your {$leaveName} ({$app->reference_code}) for {$app->days_requested} day(s) is approved for {$formattedDates}."
                    : "Your {$leaveName} ({$app->reference_code}) is scheduled for {$formattedDates}. Current status: {$app->status}. Please ensure physical document routing is finalized.";

                $upcoming[] = [
                    'id' => 'upcoming-' . $app->application_id,
                    'category' => 'upcoming_leave',
                    'urgency' => $urgency,
                    'icon' => 'ph-calendar-check',
                    'badge' => $badgeText,
                    'headline' => $headline,
                    'reference_code' => $app->reference_code,
                    'leave_type' => $leaveName,
                    'status' => $app->status,
                    'inclusive_dates' => $formattedDates,
                    'days_requested' => (float) $app->days_requested,
                    'days_until' => $daysUntilStart,
                    'message' => $msg,
                    'application_id' => $app->application_id,
                    'can_cancel' => $isCancelActive,
                    'cancel_deadline' => $formattedDeadline,
                    'days_left' => $workdaysLeft,
                    'hours_left' => $hoursLeft,
                    'action_label' => 'View CS Form 6',
                    'action_url' => route('forms.form6.print', $app->application_id),
                ];
            } elseif ($today->gte($dateFrom) && $today->lte($dateTo)) {
                // Currently on leave
                $upcomingAppIds[] = $app->application_id;
                $upcoming[] = [
                    'id' => 'ongoing-' . $app->application_id,
                    'category' => 'upcoming_leave',
                    'urgency' => 'emerald',
                    'icon' => 'ph-hourglass-medium',
                    'badge' => 'Active Leave',
                    'headline' => "Currently on {$leaveName}",
                    'reference_code' => $app->reference_code,
                    'leave_type' => $leaveName,
                    'status' => $app->status,
                    'inclusive_dates' => $formattedDates,
                    'days_requested' => (float) $app->days_requested,
                    'days_until' => 0,
                    'message' => "You are currently on {$leaveName} ({$app->reference_code}) through " . $dateTo->format('M d, Y') . ".",
                    'application_id' => $app->application_id,
                    'can_cancel' => false,
                    'action_label' => 'View CS Form 6',
                    'action_url' => route('forms.form6.print', $app->application_id),
                ];
            }

            // 2. 7-WORKING-DAY CANCELLATION WINDOW REMINDER
            // Only add separately if not already included in upcoming (to prevent duplicate cards)
            if ($isCancelActive && !in_array($app->application_id, $upcomingAppIds, true)) {
                if ($hoursLeft <= 24) {
                    $badgeText = "Last {$hoursLeft} Hours";
                    $urgency = 'rose';
                    $headline = "Cancellation Window Closing ({$hoursLeft}h left)";
                } elseif ($workdaysLeft <= 2) {
                    $badgeText = "{$workdaysLeft} Workday" . ($workdaysLeft > 1 ? 's' : '') . " Left";
                    $urgency = 'amber';
                    $headline = "Cancellation Window Closing Soon ({$workdaysLeft} workday" . ($workdaysLeft > 1 ? 's' : '') . " left)";
                } else {
                    $badgeText = "{$workdaysLeft} Workdays Left";
                    $urgency = 'slate';
                    $headline = "7-Working-Day Cancellation Window Active";
                }

                $cancellations[] = [
                    'id' => 'cancel-' . $app->application_id,
                    'category' => 'cancellation_window',
                    'urgency' => $urgency,
                    'icon' => 'ph-clock-countdown',
                    'badge' => $badgeText,
                    'headline' => $headline,
                    'reference_code' => $app->reference_code,
                    'leave_type' => $leaveName,
                    'status' => $app->status,
                    'deadline' => $formattedDeadline,
                    'days_left' => $workdaysLeft,
                    'hours_left' => $hoursLeft,
                    'message' => "Application {$app->reference_code} filed on " . $filedAt->format('M d, Y') . " can be cancelled within 7 working days until {$formattedDeadline}.",
                    'application_id' => $app->application_id,
                    'can_cancel' => true,
                    'action_label' => 'Cancel Application',
                    'cancel_deadline' => $formattedDeadline,
                ];
            }
        }

        return [
            'upcoming' => $upcoming,
            'cancellations' => $cancellations,
            'all' => array_merge($upcoming, $cancellations),
            'total_count' => count($upcoming) + count($cancellations),
        ];
    }

    /**
     * Cancel an existing leave application within the statutory 7-day period.
     * Enforces the rule:
     * - Only allowed if within 7 calendar days of filing/creation.
     * - Only allowed if status is not already finalized (Approved, Disapproved, Cancelled).
     * - Deletes any pending row in leave_records so the employee's ledger is pristine.
     */
    public function cancelLeave(Request $request, $id)
    {
        $application = LeaveApplication::where('application_id', $id)
            ->orWhere('reference_code', $id)
            ->firstOrFail();

        $user = Auth::user();
        if ($user && $user->role === 'employee' && $application->employee_id !== $user->employee_id) {
            abort(403, "Unauthorized: You may only cancel your own leave applications.");
        }

        if ($application->status === 'Cancelled') {
            return redirect()->back()->with('error', "Application {$application->reference_code} is already cancelled.");
        }

        if ($application->status === 'Disapproved') {
            return redirect()->back()->with('error', "Application {$application->reference_code} cannot be cancelled because it was disapproved.");
        }

        // Strict 7-working-day cancellation window check (excluding weekends & official holidays)
        $filedAt = Carbon::parse($application->created_at ?? $application->date_of_filing);
        $deadline = WorkingDayService::getCancellationDeadline($filedAt, 7);

        if (Carbon::now()->gt($deadline)) {
            $formattedDeadline = $deadline->format('M d, Y h:i A');
            return redirect()->back()->with(
                'error',
                "Cancellation Prohibited: The 7-working-day statutory cancellation period (excluding weekends and official holidays) for application {$application->reference_code} expired on {$formattedDeadline}. Applications past 7 working days cannot be cancelled."
            );
        }

        return DB::transaction(function () use ($application, $request, $user, $filedAt) {
            $userId = $user?->user_id ?? 1;
            $reason = trim($request->input('cancellation_reason') ?? '');
            $reasonText = !empty($reason) ? $reason : 'Cancelled by employee within the 7-working-day cancellation window.';
            $wasApproved = ($application->status === 'Approved');

            // 1. Mark application as Cancelled
            $application->update([
                'status' => 'Cancelled',
            ]);

            // 2. Log physical routing trail
            RoutingLog::create([
                'application_id' => $application->application_id,
                'office_id' => $application->current_location_office_id ?? $application->employee->office_id ?? 1,
                'scanned_by' => $userId,
                'action' => 'CANCELLED',
                'method' => 'SYSTEM',
                'scanned_at' => Carbon::now(),
                'remarks' => "Application Cancelled: {$reasonText}" . ($wasApproved ? ' (Reverted approved leave credits)' : ''),
            ]);

            // 3. If application was approved, remove ledger entry and restore specific leave balances
            if ($wasApproved) {
                LeaveLedgerEntry::where('application_id', $application->application_id)->delete();

                $year = Carbon::parse($application->date_from)->year;
                $balance = LeaveBalance::where('employee_id', $application->employee_id)
                    ->where('leave_type_id', $application->leave_type_id)
                    ->where('year', $year)
                    ->first();
                if ($balance) {
                    $balance->update([
                        'balance' => round((float)$balance->balance + (float)$application->days_requested, 3),
                        'last_posted_at' => Carbon::now(),
                    ]);
                }

                // If FL, also restore VL balance
                if ($application->leaveType && $application->leaveType->code === 'FL') {
                    $vlType = LeaveType::where('code', 'VL')->first();
                    if ($vlType) {
                        $vlBal = LeaveBalance::where('employee_id', $application->employee_id)
                            ->where('leave_type_id', $vlType->leave_type_id)
                            ->where('year', $year)
                            ->first();
                        if ($vlBal) {
                            $vlBal->update([
                                'balance' => round((float)$vlBal->balance + (float)$application->days_requested, 3),
                                'last_posted_at' => Carbon::now(),
                            ]);
                        }
                    }
                }
            }

            // 4. Remove row from employee's Leave Record Card if present, and recalculate running balances
            $record = LeaveRecord::where('application_id', $application->application_id)->first();
            if ($record) {
                $record->delete();
            }

            $leaveRecordService = app(\App\Services\LeaveRecordService::class);
            $leaveRecordService->recalculateEmployeeBalances($application->employee_id);

            // 5. Record audit log
            AuditLog::create([
                'user_id' => $userId,
                'action' => $wasApproved ? 'CANCEL_APPROVED_LEAVE_APPLICATION' : 'CANCEL_LEAVE_APPLICATION',
                'table_name' => 'leave_applications',
                'record_id' => $application->application_id,
                'details' => json_encode([
                    'reference_code' => $application->reference_code,
                    'reason' => $reasonText,
                    'was_approved' => $wasApproved,
                    'days_since_filing' => $filedAt->diffInDays(Carbon::now()),
                    'working_days_since_filing' => WorkingDayService::getRemainingWorkingDays(Carbon::now(), $filedAt),
                ]),
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'logged_at' => Carbon::now(),
            ]);

            $successMsg = $wasApproved
                ? "Approved Leave Application {$application->reference_code} has been cancelled within the 7-working-day period. Deducted credits have been restored."
                : "Leave Application {$application->reference_code} has been successfully cancelled within the 7-working-day period.";

            DB::afterCommit(function () use ($application, $reasonText) {
                $application->loadMissing(['employee.office', 'employee.user', 'leaveType']);
                \App\Services\LeaveNotificationService::notifyStatusUpdate(
                    $application,
                    'CANCELLED',
                    $application->employee?->office,
                    $reasonText
                );
            });

            return redirect()->back()
                ->with('cancellation_success', $successMsg)
                ->with('cancelled_reference_code', $application->reference_code)
                ->with('cancellation_was_approved', $wasApproved);
        });
    }

    /**
     * Generate printable official Civil Service Form No. 6 (Revised 2020).
     * Before printing, Section 7.A – Certification of Leave Credits is automatically populated
     * with the employee's latest saved balance:
     * - Total Earned: Previous balance after all previously recorded and applicable transactions.
     * - Less This Application: Leave credits chargeable for the current application.
     * - Balance: Total Earned − Less This Application.
     */
    public function printForm6($id)
    {
        $application = LeaveApplication::with([
            'employee.office',
            'leaveType',
            'currentLocation',
            'certifiedBy.employee',
            'routingLogs.office',
        ])
        ->where('application_id', $id)
        ->orWhere('reference_code', $id)
        ->firstOrFail();

        $user = Auth::user();
        if (!$user || !$user->canViewApplicationDocument($application)) {
            abort(403, "Unauthorized access to application document.");
        }

        // Before the employee prints the application, automatically populate Section 7.A
        // using the employee's latest applicable leave balance:
        // - Total Earned: Previous balance after all previously recorded and applicable transactions.
        // - Less This Application: Leave credits chargeable for the current application.
        // - Balance: Total Earned − Less This Application.
        // Ensures Section 7.A reflects the latest saved balance before printing without premature deduction.
        $leaveRecordService = app(\App\Services\LeaveRecordService::class);
        $sec7A = $leaveRecordService->calculateSection7AData($application);

        $application->update([
            'certified_vl_earned' => $sec7A['vl_total_earned'],
            'certified_vl_less' => $sec7A['vl_less'],
            'certified_vl_balance' => $sec7A['vl_balance'],
            'certified_sl_earned' => $sec7A['sl_total_earned'],
            'certified_sl_less' => $sec7A['sl_less'],
            'certified_sl_balance' => $sec7A['sl_balance'],
            'certified_as_of' => $sec7A['as_of_date'],
            'is_certified' => true,
        ]);

        $hasReachedHrmu = $application->hasReachedHrmu();
        $isCertified = true;
        $isSection7AFilled = true;
        $certifiedAsOf = $sec7A['as_of_formatted'];

        $vlBalance = (float) $sec7A['vl_total_earned'];
        $vlLess = (float) $sec7A['vl_less'];
        $vlRemain = (float) $sec7A['vl_balance'];

        $slBalance = (float) $sec7A['sl_total_earned'];
        $slLess = (float) $sec7A['sl_less'];
        $slRemain = (float) $sec7A['sl_balance'];

        return view('forms.form6_print', compact(
            'application',
            'hasReachedHrmu',
            'isCertified',
            'isSection7AFilled',
            'certifiedAsOf',
            'vlBalance',
            'slBalance',
            'vlLess',
            'slLess',
            'vlRemain',
            'slRemain'
        ));
    }
}
