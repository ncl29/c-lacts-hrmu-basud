<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\LeaveRecord;
use App\Models\StarlaReference;
use App\Services\LeaveRecordService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveRecordController extends Controller
{
    protected LeaveRecordService $service;

    public function __construct(LeaveRecordService $service)
    {
        $this->service = $service;
    }

    /**
     * HR & Admin Leave Record Management Portal
     */
    public function index(Request $request)
    {
        $allEmployees = Employee::with('office')->eligibleForLeave()->active()->orderBy('full_name')->get();

        // Default to the first regular active employee eligible for leave credits
        $defaultEmpId = $allEmployees->first()?->employee_id ?? 1;

        $selectedEmployeeId = $request->query('employee_id', $defaultEmpId);
        $employee = Employee::with(['office', 'balances.leaveType'])->findOrFail($selectedEmployeeId);

        if ($employee->isJobOrder()) {
            return redirect()->route('hr.leave-records.index')
                ->with('error', "Job Order personnel ({$employee->full_name}) do not have Civil Service Form 41 leave records. Leave credits apply only to regular municipal personnel.");
        }

        // Auto-seed initial baseline / earned Table II row if employee currently has zero records
        $this->service->ensureInitialRecordForEmployee($employee, Auth::id() ?? 1);

        // Fetch records in chronological order
        $records = LeaveRecord::where('employee_id', $employee->employee_id)
            ->orderBy('row_order', 'asc')
            ->orderBy('created_at', 'asc')
            ->orderBy('record_id', 'asc')
            ->get();

        // Summaries
        // Ensure official release is registered in sequential registry
        \App\Models\LeaveRecordRelease::getOrCreateForEmployee($employee->employee_id, Auth::id() ?? 1);

        $latestRecord = $records->last();
        $latestVlBalance = $latestRecord?->vl_balance ?? '0';
        $latestSlBalance = $latestRecord?->sl_balance ?? '0';

        // Starla Table II and Table IV references for quick modal lookup
        $tableII = $this->service->getTableIIRates();
        $tableIVHours = StarlaReference::where('table_ref', 'Table IV')->where('input_value', 'like', '%hour%')->get();
        $tableIVMinutes = StarlaReference::where('table_ref', 'Table IV')->where('input_value', 'like', '%min%')->get();

        // Monthly Accrual Status for Current Month
        $currentMonthDate = Carbon::now();
        $isCurrentMonthAccrued = $this->service->hasEmployeeAccruedMonth($employee->employee_id, $currentMonthDate);

        // Active municipal personnel batch stats for current month (eligible regular personnel only)
        $activeEmployees = Employee::eligibleForLeave()->active()->get();
        $accruedCount = 0;
        foreach ($activeEmployees as $emp) {
            if ($this->service->hasEmployeeAccruedMonth($emp->employee_id, $currentMonthDate)) {
                $accruedCount++;
            }
        }
        $pendingCount = max(0, $activeEmployees->count() - $accruedCount);

        return view('leave_record.index', compact(
            'allEmployees',
            'employee',
            'records',
            'latestVlBalance',
            'latestSlBalance',
            'tableII',
            'tableIVHours',
            'tableIVMinutes',
            'isCurrentMonthAccrued',
            'currentMonthDate',
            'activeEmployees',
            'accruedCount',
            'pendingCount'
        ));
    }

    /**
     * Standard Employee View-Only Portal
     */
    public function employeeView(Request $request)
    {
        $user = Auth::user();

        // HR Staff manages official leave records; redirect to HR portal
        if ($user && $user->role === 'hrmu_staff') {
            return redirect()->route('hr.leave-records.index');
        }

        $employeeId = $user?->employee_id;

        if (!$employeeId) {
            if ($user && $user->role === 'employee') {
                abort(403, 'No employee profile is linked to this account.');
            }
            $employeeId = Employee::eligibleForLeave()->first()?->employee_id ?? 1;
        }

        $employee = Employee::with('office')->findOrFail($employeeId);

        if ($user && (int) $user->employee_id === (int) $employee->employee_id) {
            $user->setRelation('employee', $employee);
        }

        if ($employee->isJobOrder()) {
            return redirect()->route('employee.dashboard')
                ->with('error', "Job Order personnel do not have Civil Service Form 41 leave records. Leave credits apply only to regular municipal personnel.");
        }

        $records = LeaveRecord::with(['application', 'leaveApplication'])
            ->where('employee_id', $employee->employee_id)
            ->orderBy('row_order', 'asc')
            ->orderBy('created_at', 'asc')
            ->orderBy('record_id', 'asc')
            ->get();

        // Auto-seed initial baseline / earned Table II row only if employee currently has zero records
        if ($records->isEmpty()) {
            $employee->load('balances.leaveType');
            $this->service->ensureInitialRecordForEmployee($employee, Auth::id() ?? 1);
            $records = LeaveRecord::with(['application', 'leaveApplication'])
                ->where('employee_id', $employee->employee_id)
                ->orderBy('row_order', 'asc')
                ->orderBy('created_at', 'asc')
                ->orderBy('record_id', 'asc')
                ->get();
        }

        // Ensure official release is registered in sequential registry
        $release = \App\Models\LeaveRecordRelease::getOrCreateForEmployee($employee->employee_id, Auth::id() ?? 1);

        $latestRecord = $records->last();
        $latestVlBalance = $latestRecord?->vl_balance ?? '0';
        $latestSlBalance = $latestRecord?->sl_balance ?? '0';

        $simplified = $this->buildSimplifiedRecordsData($records, $latestVlBalance, $latestSlBalance);

        return view('leave_record.employee_view', array_merge([
            'employee' => $employee,
            'records' => $records,
            'release' => $release,
            'latestVlBalance' => $latestVlBalance,
            'latestSlBalance' => $latestSlBalance,
        ], $simplified));
    }

    /**
     * Store a new row in the Leave Record.
     * Automatically parses the particular and populates appropriate columns if left blank.
     */
    public function storeRow(Request $request)
    {
        $employee = Employee::findOrFail((int) $request->input('employee_id'));
        if ($employee->isJobOrder()) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Cannot create leave record for Job Order personnel ({$employee->full_name}). Leave records apply only to regular employees.");
        }

        $entryMode = $request->input('entry_mode', 'manual');
        if ($entryMode === 'dtr' || $request->filled('late_hours') || $request->filled('late_minutes') || $request->filled('undertime_hours') || $request->filled('undertime_minutes') || $request->filled('days_absent')) {
            $validated = $request->validate([
                'employee_id' => ['required', 'integer', 'exists:employees,employee_id'],
                'period' => ['required', 'string', 'max:100'],
                'late_hours' => ['nullable', 'integer', 'min:0', 'max:8'],
                'late_minutes' => ['nullable', 'integer', 'min:0', 'max:59'],
                'undertime_hours' => ['nullable', 'integer', 'min:0', 'max:8'],
                'undertime_minutes' => ['nullable', 'integer', 'min:0', 'max:59'],
                'days_absent' => ['nullable', 'numeric', 'min:0', 'max:31'],
                'absence_type' => ['nullable', 'string', 'in:with_pay,without_pay'],
            ]);

            $record = $this->service->recordDtrAttendance(
                (int)$validated['employee_id'],
                $validated['period'],
                isset($validated['late_hours']) ? (int)$validated['late_hours'] : 0,
                isset($validated['late_minutes']) ? (int)$validated['late_minutes'] : 0,
                isset($validated['undertime_hours']) ? (int)$validated['undertime_hours'] : 0,
                isset($validated['undertime_minutes']) ? (int)$validated['undertime_minutes'] : 0,
                isset($validated['days_absent']) ? (float)$validated['days_absent'] : 0.0,
                $validated['absence_type'] ?? 'with_pay',
                Auth::id() ?? 1
            );

            return redirect()->route('hr.leave-records.index', ['employee_id' => $validated['employee_id']])
                ->with('success', "DTR Attendance entry '{$record->particular}' successfully posted. Converted via Table IV and balances updated according to office deduction rules.");
        }

        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,employee_id'],
            'period' => ['required', 'string', 'max:100'],
            'particular' => ['required', 'string', 'max:255'],
            'vl_earned' => ['nullable', 'string', 'max:50'],
            'vl_absence_with_pay' => ['nullable', 'string', 'max:50'],
            'vl_balance' => ['nullable', 'string', 'max:50'],
            'vl_absence_without_pay' => ['nullable', 'string', 'max:50'],
            'sl_earned' => ['nullable', 'string', 'max:50'],
            'sl_absence_with_pay' => ['nullable', 'string', 'max:50'],
            'sl_balance' => ['nullable', 'string', 'max:50'],
            'sl_absence_without_pay' => ['nullable', 'string', 'max:50'],
            'date_action_taken' => ['nullable', 'string'],
        ]);

        $employeeId = (int)$validated['employee_id'];
        $particular = trim($validated['particular']);

        // Auto-parse particular if columns are not explicitly populated
        $parsed = $this->service->parseParticular($particular);

        $vlEarned = $validated['vl_earned'] ?? null;
        $vlAbsenceWithPay = $validated['vl_absence_with_pay'] ?? null;
        $vlAbsenceWithoutPay = $validated['vl_absence_without_pay'] ?? null;
        $slEarned = $validated['sl_earned'] ?? null;
        $slAbsenceWithPay = $validated['sl_absence_with_pay'] ?? null;
        $slAbsenceWithoutPay = $validated['sl_absence_without_pay'] ?? null;

        if ($parsed['target_column'] && empty($vlAbsenceWithPay) && empty($slAbsenceWithPay)) {
            if ($parsed['target_column'] === 'vl_absence_with_pay') {
                $vlAbsenceWithPay = $parsed['value'];
            } elseif ($parsed['target_column'] === 'sl_absence_with_pay') {
                $slAbsenceWithPay = $parsed['value'];
            }
        }

        $nextOrder = (LeaveRecord::where('employee_id', $employeeId)->max('row_order') ?? 0) + 1;

        $record = LeaveRecord::create([
            'employee_id' => $employeeId,
            'period' => $validated['period'],
            'particular' => $particular,
            'vl_earned' => $vlEarned,
            'vl_absence_with_pay' => $vlAbsenceWithPay,
            'vl_balance' => $validated['vl_balance'] ?? null,
            'vl_absence_without_pay' => $vlAbsenceWithoutPay,
            'sl_earned' => $slEarned,
            'sl_absence_with_pay' => $slAbsenceWithPay,
            'sl_balance' => $validated['sl_balance'] ?? null,
            'sl_absence_without_pay' => $slAbsenceWithoutPay,
            'date_action_taken' => $validated['date_action_taken'] ?? null,
            'row_order' => $nextOrder,
            'encoded_by' => Auth::id() ?? 1,
        ]);

        // Recalculate balances in chronological sequence
        $this->service->recalculateEmployeeBalances($employeeId);

        // Audit Trail
        AuditLog::create([
            'user_id' => Auth::id() ?? 1,
            'action' => 'ADD_LEAVE_RECORD_ROW',
            'table_name' => 'leave_records',
            'record_id' => $record->record_id,
            'details' => json_encode([
                'employee_id' => $employeeId,
                'period' => $validated['period'],
                'particular' => $particular,
            ]),
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'logged_at' => Carbon::now(),
        ]);

        return redirect()->route('hr.leave-records.index', ['employee_id' => $employeeId])
            ->with('success', "Leave Record entry '{$particular}' successfully posted. Balances automatically updated.");
    }

    /**
     * Update an existing row.
     */
    public function updateRow(Request $request, $id)
    {
        $record = LeaveRecord::findOrFail($id);

        $validated = $request->validate([
            'period' => ['required', 'string', 'max:100'],
            'particular' => ['required', 'string', 'max:255'],
            'vl_earned' => ['nullable', 'string', 'max:50'],
            'vl_absence_with_pay' => ['nullable', 'string', 'max:50'],
            'vl_absence_without_pay' => ['nullable', 'string', 'max:50'],
            'sl_earned' => ['nullable', 'string', 'max:50'],
            'sl_absence_with_pay' => ['nullable', 'string', 'max:50'],
            'sl_absence_without_pay' => ['nullable', 'string', 'max:50'],
            'date_action_taken' => ['nullable', 'string'],
        ]);

        $record->update([
            'period' => $validated['period'],
            'particular' => trim($validated['particular']),
            'vl_earned' => $validated['vl_earned'] ?: null,
            'vl_absence_with_pay' => $validated['vl_absence_with_pay'] ?: null,
            'vl_absence_without_pay' => $validated['vl_absence_without_pay'] ?: null,
            'sl_earned' => $validated['sl_earned'] ?: null,
            'sl_absence_with_pay' => $validated['sl_absence_with_pay'] ?: null,
            'sl_absence_without_pay' => $validated['sl_absence_without_pay'] ?: null,
            'date_action_taken' => $validated['date_action_taken'] ?: null,
        ]);

        $this->service->recalculateEmployeeBalances($record->employee_id);

        AuditLog::create([
            'user_id' => Auth::id() ?? 1,
            'action' => 'UPDATE_LEAVE_RECORD_ROW',
            'table_name' => 'leave_records',
            'record_id' => $record->record_id,
            'details' => json_encode(['employee_id' => $record->employee_id]),
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'logged_at' => Carbon::now(),
        ]);

        return redirect()->route('hr.leave-records.index', ['employee_id' => $record->employee_id])
            ->with('success', "Row #{$record->row_order} successfully updated and subsequent balances recalculated.");
    }

    /**
     * Delete an existing row.
     */
    public function destroyRow($id)
    {
        $record = LeaveRecord::findOrFail($id);
        $employeeId = $record->employee_id;
        $rowOrder = $record->row_order;

        $record->delete();

        // Re-number remaining rows to preserve clean sequential ordering
        $remaining = LeaveRecord::where('employee_id', $employeeId)->orderBy('row_order')->get();
        $order = 1;
        foreach ($remaining as $r) {
            $r->row_order = $order++;
            $r->saveQuietly();
        }

        $this->service->recalculateEmployeeBalances($employeeId);

        AuditLog::create([
            'user_id' => Auth::id() ?? 1,
            'action' => 'DELETE_LEAVE_RECORD_ROW',
            'table_name' => 'leave_records',
            'record_id' => $id,
            'details' => json_encode(['employee_id' => $employeeId, 'deleted_row' => $rowOrder]),
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'logged_at' => Carbon::now(),
        ]);

        return redirect()->route('hr.leave-records.index', ['employee_id' => $employeeId])
            ->with('success', "Row successfully removed. Subsequent balances automatically recalculated.");
    }

    /**
     * Recalculate balances manually for an employee.
     */
    public function recalculate($employeeId)
    {
        $employee = Employee::findOrFail($employeeId);
        $result = $this->service->recalculateEmployeeBalances($employee->employee_id);

        return redirect()->route('hr.leave-records.index', ['employee_id' => $employee->employee_id])
            ->with('recalculate_success', [
                'employee_name' => $employee->full_name,
                'count' => $result['count'],
                'latest_vl_balance' => $result['latest_vl_balance'],
                'latest_sl_balance' => $result['latest_sl_balance'],
            ]);
    }

    /**
     * Real-time AJAX endpoint to parse particulars and convert DTR durations via Table IV.
     */
    public function previewParticular(Request $request)
    {
        // Support DTR live preview mode
        if ($request->has('late_hours') || $request->has('late_minutes') || $request->has('undertime_hours') || $request->has('undertime_minutes') || $request->has('days_absent')) {
            $lateHours = (int)$request->input('late_hours', 0);
            $lateMinutes = (int)$request->input('late_minutes', 0);
            $utHours = (int)$request->input('undertime_hours', 0);
            $utMinutes = (int)$request->input('undertime_minutes', 0);
            $daysAbsent = (float)$request->input('days_absent', 0);
            $absenceType = $request->input('absence_type', 'with_pay');

            $lateConv = $this->service->convertTableIV($lateHours, $lateMinutes);
            $utConv = $this->service->convertTableIV($utHours, $utMinutes);

            $tardyFraction = LeaveRecordService::exactAdd($lateConv['total_fraction'], $utConv['total_fraction']);
            $absFraction = $daysAbsent > 0 ? LeaveRecordService::formatExact((string)$daysAbsent) : '0';

            $totalWithPay = ($absenceType === 'with_pay') ? LeaveRecordService::exactAdd($tardyFraction, $absFraction) : $tardyFraction;
            $unpaid = ($absenceType === 'without_pay') ? $absFraction : '0';

            $parts = [];
            if ($lateHours > 0 || $lateMinutes > 0) {
                $lateStr = '';
                if ($lateHours > 0) $lateStr .= $lateHours . ($lateHours == 1 ? ' hr' : ' hrs');
                if ($lateMinutes > 0) $lateStr .= (!empty($lateStr) ? ' and ' : '') . $lateMinutes . ($lateMinutes == 1 ? ' min' : ' mins');
                $parts[] = "L ({$lateStr})";
            }
            if ($utHours > 0 || $utMinutes > 0) {
                $utStr = '';
                if ($utHours > 0) $utStr .= $utHours . ($utHours == 1 ? ' hr' : ' hrs');
                if ($utMinutes > 0) $utStr .= (!empty($utStr) ? ' and ' : '') . $utMinutes . ($utMinutes == 1 ? ' min' : ' mins');
                $parts[] = "UT ({$utStr})";
            }
            if ($daysAbsent > 0) {
                $parts[] = "ABS ({$absFraction})";
            }
            $particularStr = !empty($parts) ? implode(', ', $parts) : 'No deductions entered';

            $employeeId = $request->input('employee_id');
            $currentVl = 0.0;
            if ($employeeId) {
                $latestRec = LeaveRecord::where('employee_id', $employeeId)->orderBy('row_order', 'asc')->orderBy('record_id', 'asc')->get()->last();
                $currentVl = $latestRec?->vl_balance !== null ? (float)$latestRec->vl_balance : 0.0;
            }

            return response()->json([
                'success' => true,
                'is_dtr' => true,
                'late_fraction' => $lateConv['total_fraction'],
                'undertime_fraction' => $utConv['total_fraction'],
                'tardy_total_fraction' => $tardyFraction,
                'absence_fraction' => $absFraction,
                'total_with_pay' => $totalWithPay,
                'unpaid_lwop' => $unpaid,
                'particular' => $particularStr,
                'current_vl_balance' => $currentVl,
                'projected_vl_balance' => max(0, round($currentVl - (float)$totalWithPay, 3)),
                'rule_description' => "Table IV Conversion: Tardiness and undertime deducted from VL. If VL is exhausted, excess converts to LWOP.",
            ]);
        }

        $particular = $request->query('particular', '');
        if (empty($particular)) {
            return response()->json(['success' => false, 'parsed' => null]);
        }

        $parsed = $this->service->parseParticular($particular);
        return response()->json([
            'success' => true,
            'parsed' => $parsed,
        ]);
    }

    /**
     * Printable Civil Service Leave Record Card (Matching Scanned Cardstock Form)
     * Supports both 'original' (11-column CS Form 41 cardstock) and 'simple' (Monthly Summary Statement).
     */
    public function printCard($requestOrId, $possibleId = null)
    {
        if ($requestOrId instanceof Request) {
            $request = $requestOrId;
            $employeeId = $possibleId ?? Auth::user()?->employee_id ?? 1;
        } else {
            $request = request();
            $employeeId = $requestOrId ?? Auth::user()?->employee_id ?? 1;
        }

        $employee = Employee::with(['office', 'balances.leaveType'])->findOrFail((int) $employeeId);

        if ($employee->isJobOrder()) {
            abort(403, "Job Order personnel do not have Civil Service Form 41 leave record cards.");
        }

        $records = LeaveRecord::where('employee_id', $employee->employee_id)
            ->orderBy('row_order', 'asc')
            ->orderBy('created_at', 'asc')
            ->orderBy('record_id', 'asc')
            ->get();

        // Ensure official release is registered in sequential registry
        \App\Models\LeaveRecordRelease::getOrCreateForEmployee($employee->employee_id, Auth::id() ?? 1);

        $latestRecord = $records->last();
        $latestVlBalance = $latestRecord?->vl_balance ?? '0';
        $latestSlBalance = $latestRecord?->sl_balance ?? '0';

        // Check format: 'simple' vs 'original'
        if ($request->query('format') === 'simple') {
            $simplified = $this->buildSimplifiedRecordsData($records, $latestVlBalance, $latestSlBalance);
            return view('leave_record.print_simple', array_merge([
                'employee' => $employee,
                'records' => $records,
                'latestVlBalance' => $latestVlBalance,
                'latestSlBalance' => $latestSlBalance,
            ], $simplified));
        }

        return view('leave_record.print', compact(
            'employee',
            'records',
            'latestVlBalance',
            'latestSlBalance'
        ));
    }

    /**
     * Helper to prepare structured simplified records data by year and month.
     */
    protected function buildSimplifiedRecordsData($records, $latestVlBalance, $latestSlBalance): array
    {
        $totalVlEarned = $records->sum(fn($r) => (float) ($r->vl_earned ?? 0));
        $totalVlUsed = $records->sum(fn($r) => (float) ($r->vl_absence_with_pay ?? 0));
        $totalSlEarned = $records->sum(fn($r) => (float) ($r->sl_earned ?? 0));
        $totalSlUsed = $records->sum(fn($r) => (float) ($r->sl_absence_with_pay ?? 0));
        $totalLwop = $records->sum(fn($r) => (float) ($r->vl_absence_without_pay ?? 0) + (float) ($r->sl_absence_without_pay ?? 0));
        $totalAvailableBalance = (float) $latestVlBalance + (float) $latestSlBalance;

        $recordsByYear = [];
        $monthNames = [
            '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
            '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
            '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
        ];

        foreach ($records as $record) {
            $sortKey = \App\Services\LeaveRecordService::extractSortDate($record);
            $parts = explode('-', $sortKey);
            $year = $parts[0] ?? (string) Carbon::now()->year;
            $monthNum = $parts[1] ?? '01';
            $monthName = $monthNames[$monthNum] ?? 'January';
            $monthLabel = "{$monthName} {$year}";

            if (!isset($recordsByYear[$year])) {
                $recordsByYear[$year] = [];
            }
            if (!isset($recordsByYear[$year][$sortKey])) {
                $recordsByYear[$year][$sortKey] = [
                    'sort_key' => $sortKey,
                    'month_name' => $monthName,
                    'month_num' => $monthNum,
                    'month_label' => $monthLabel,
                    'year' => $year,
                    'records' => [],
                    'closing_vl' => $record->vl_balance ?? '0',
                    'closing_sl' => $record->sl_balance ?? '0',
                    'earned_vl' => 0.0,
                    'earned_sl' => 0.0,
                    'used_vl' => 0.0,
                    'used_sl' => 0.0,
                    'has_starting_balance' => false,
                    'leaves_taken_items' => [],
                ];
            }

            $recordsByYear[$year][$sortKey]['records'][] = $record;
            if ($record->vl_balance !== null) {
                $recordsByYear[$year][$sortKey]['closing_vl'] = $record->vl_balance;
            }
            if ($record->sl_balance !== null) {
                $recordsByYear[$year][$sortKey]['closing_sl'] = $record->sl_balance;
            }
            $recordsByYear[$year][$sortKey]['earned_vl'] += (float)($record->vl_earned ?? 0);
            $recordsByYear[$year][$sortKey]['earned_sl'] += (float)($record->sl_earned ?? 0);
            $recordsByYear[$year][$sortKey]['used_vl'] += (float)($record->vl_absence_with_pay ?? 0);
            $recordsByYear[$year][$sortKey]['used_sl'] += (float)($record->sl_absence_with_pay ?? 0);

            if (stripos($record->particular, 'opening') !== false || stripos($record->particular, 'certified baseline') !== false) {
                $recordsByYear[$year][$sortKey]['has_starting_balance'] = true;
            }

            $vlUsed = (float)($record->vl_absence_with_pay ?? 0);
            $slUsed = (float)($record->sl_absence_with_pay ?? 0);
            $vlLwop = (float)($record->vl_absence_without_pay ?? 0);
            $slLwop = (float)($record->sl_absence_without_pay ?? 0);

            $leaveDates = null;
            if ($record->application && $record->application->date_from) {
                $from = Carbon::parse($record->application->date_from)->format('M d');
                $to = Carbon::parse($record->application->date_to)->format('M d, Y');
                $leaveDates = "{$from} - {$to}";
            } elseif (preg_match('/Leave:\s*([^)]+)/i', $record->date_action_taken ?? '', $dm)) {
                $leaveDates = trim($dm[1]);
            }

            if ($vlUsed > 0 || $slUsed > 0 || $vlLwop > 0 || $slLwop > 0 || stripos($record->particular, 'spl') !== false || stripos($record->particular, 'wellness') !== false || stripos($record->particular, 'forced') !== false) {
                $recordsByYear[$year][$sortKey]['leaves_taken_items'][] = [
                    'particular' => $record->particular,
                    'vl_used' => $vlUsed,
                    'sl_used' => $slUsed,
                    'vl_lwop' => $vlLwop,
                    'sl_lwop' => $slLwop,
                    'date_action_taken' => $record->date_action_taken,
                    'ref_code' => $record->application?->reference_code,
                    'leave_dates' => $leaveDates,
                ];
            }
        }

        krsort($recordsByYear);
        foreach ($recordsByYear as $yr => &$months) {
            ksort($months);
        }
        unset($months);

        $availableYears = array_keys($recordsByYear);
        if (empty($availableYears)) {
            $currentYearStr = (string) Carbon::now()->year;
            $availableYears = [$currentYearStr];
            $recordsByYear[$currentYearStr] = [];
        }

        return [
            'recordsByYear' => $recordsByYear,
            'availableYears' => $availableYears,
            'totalVlEarned' => $totalVlEarned,
            'totalVlUsed' => $totalVlUsed,
            'totalSlEarned' => $totalSlEarned,
            'totalSlUsed' => $totalSlUsed,
            'totalLwop' => $totalLwop,
            'totalAvailableBalance' => $totalAvailableBalance,
        ];
    }

    /**
     * Trigger monthly leave credit accrual (1.25 VL & 1.25 SL) for all eligible employees.
     * Guaranteed to add only once per employee per month (zero duplicate accruals).
     */
    public function runMonthlyAccrual(Request $request)
    {
        $validated = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'min:2020', 'max:2035'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,employee_id'],
        ]);

        $now = Carbon::now();
        $year = $validated['year'] ?? $now->year;
        $month = $validated['month'] ?? $now->month;
        $targetDate = Carbon::createFromDate($year, $month, 1)->startOfDay();

        // Strict rule: Do not earn 1.25 if it is not yet the first day of the target month
        if ($now->startOfDay()->lt($targetDate)) {
            return redirect()->back()
                ->with('error', "Cannot accrue leave credits for {$targetDate->format('F Y')}: It is not yet the first day of that month.");
        }

        $selectedEmployeeId = $request->input('employee_id');

        if ($selectedEmployeeId) {
            $employee = Employee::findOrFail($selectedEmployeeId);
            if ($employee->isJobOrder()) {
                return redirect()->route('hr.leave-records.index')
                    ->with('error', "Cannot accrue leave credits for {$employee->full_name}: Job Order personnel do not earn leave credits.");
            }

            if ($this->service->hasEmployeeAccruedMonth($employee->employee_id, $targetDate)) {
                return redirect()->route('hr.leave-records.index', ['employee_id' => $employee->employee_id])
                    ->with('warning', "Employee {$employee->full_name} has already received earned credits for {$targetDate->format('F Y')}. Duplicate accrual prevented.");
            }

            $record = $this->service->accrueMonthlyCreditsForEmployee(
                $employee,
                $targetDate,
                '1.25',
                '1.25',
                'Earned',
                false,
                Auth::id() ?? 1
            );

            return redirect()->route('hr.leave-records.index', ['employee_id' => $employee->employee_id])
                ->with('success', "Monthly credits (+1.250 VL / +1.250 SL) successfully credited for {$targetDate->format('F Y')}. New VL Balance: {$record->vl_balance}, New SL Balance: {$record->sl_balance}.");
        }

        $result = $this->service->accrueMonthlyCreditsForAllEligible($targetDate, Auth::id() ?? 1);

        $msg = "Monthly Accrual for {$result['target_month']} completed: {$result['total_accrued']} personnel credited (+1.250 VL / +1.250 SL), {$result['total_skipped']} skipped (already accrued). Zero duplicate guarantee enforced.";

        $redirectEmpId = $request->input('current_employee_id') ?? 1;

        return redirect()->route('hr.leave-records.index', ['employee_id' => $redirectEmpId])
            ->with('success', $msg);
    }

    /**
     * Store manual Table II prorated earned leave credits for an employee who became eligible
     * partway through a month, or returned from LWOP.
     * Preserves exact entered decimal values without rounding or truncating.
     */
    public function storeProratedAccrual(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,employee_id'],
            'period' => ['required', 'string', 'max:100'],
            'days_of_service' => ['nullable', 'integer', 'between:1,30'],
            'vl_earned' => ['required', 'string', 'max:50'],
            'sl_earned' => ['required', 'string', 'max:50'],
            'particular' => ['nullable', 'string', 'max:255'],
        ]);

        $employeeId = (int)$validated['employee_id'];
        $employee = Employee::findOrFail($employeeId);
        if ($employee->isJobOrder()) {
            return redirect()->route('hr.leave-records.index')
                ->with('error', "Cannot accrue prorated leave credits for {$employee->full_name}: Job Order personnel do not earn leave credits.");
        }

        $period = trim($validated['period']);
        $cleanVl = trim($validated['vl_earned']);
        $cleanSl = trim($validated['sl_earned']);
        $daysOfService = !empty($validated['days_of_service']) ? (int)$validated['days_of_service'] : null;

        $record = $this->service->accrueProratedCredits(
            $employeeId,
            $period,
            $cleanVl,
            $cleanSl,
            $validated['particular'] ?? null,
            $daysOfService,
            Auth::id() ?? 1
        );

        return redirect()->route('hr.leave-records.index', ['employee_id' => $employeeId])
            ->with('success', "Table II Prorated Credits (+{$cleanVl} VL / +{$cleanSl} SL) successfully credited for {$period}. New VL Balance: {$record->vl_balance}, New SL Balance: {$record->sl_balance}.");
    }

    /**
     * Updates and populates all municipal employees' leave records,
     * ensuring every month starts with "Earned" (1.25 VL / 1.25 SL) in Particular and Earned days,
     * placing all records in chronological sequence.
     */
    public function updateAllRecords(Request $request)
    {
        $year = (int)($request->input('year', 2026));
        $upToMonth = $request->input('up_to_month') ? (int)$request->input('up_to_month') : null;

        $results = $this->service->updateAllLeaveRecordsWithMonthlyEarned($year, $upToMonth, Auth::id() ?? 1);

        AuditLog::create([
            'user_id' => Auth::id() ?? 1,
            'action' => 'UPDATE_ALL_LEAVE_RECORDS_MONTHLY_EARNED',
            'table_name' => 'leave_records',
            'record_id' => 0,
            'details' => json_encode($results),
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'logged_at' => Carbon::now(),
        ]);

        $redirectEmpId = $request->input('employee_id');

        return redirect()->route('hr.leave-records.index', $redirectEmpId ? ['employee_id' => $redirectEmpId] : [])
            ->with('success', "Updated all Leave Records: {$results['records_created']} monthly Earned entries created, {$results['employees_updated']} personnel ledgers re-ordered and recalculated.");
    }
}



