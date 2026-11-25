<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDeduction;
use App\Models\Employee;
use App\Models\Office;
use App\Models\PayPeriod;
use App\Models\StarlaReference;
use App\Services\StarlaComputationService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * HrAttendanceController
 *
 * Handles semi-monthly HR DTR verified attendance encoding and Starla computation trigger:
 * - Accepts verified aggregate tardiness minutes, hours, and unexcused absences.
 * - Triggers StarlaComputationService for automated CSC Table IV and Table II/III conversion.
 * - Prevents double deduction with approved leave applications.
 * - Posts transactions to digital leave ledger cards (CS Form 41).
 */
class HrAttendanceController extends Controller
{
    /**
     * Show the HR Tardiness & Absence encoding interface.
     */
    public function create(Request $request)
    {
        $currentYear = Carbon::now()->year;

        // Municipal offices list with active regular employee counts
        $offices = Office::withCount(['employees' => function ($query) {
            $query->eligibleForLeave()->active();
        }])
        ->orderBy('office_name')
        ->get();

        // Employees list (regular personnel eligible for leave credits)
        $employees = Employee::with(['office', 'balances.leaveType'])
            ->eligibleForLeave()
            ->active()
            ->orderBy('full_name')
            ->get();

        // Pay periods for current calendar year
        $payPeriods = PayPeriod::whereYear('start_date', $currentYear)
            ->orderBy('month', 'desc')
            ->orderBy('half', 'desc')
            ->get();

        // Selected pay period (default to latest or requested)
        $selectedPeriodId = $request->query('pay_period_id') ?? $payPeriods->first()?->pay_period_id ?? 1;

        // Recent semi-monthly deductions
        $recentDeductions = AttendanceDeduction::with([
            'employee.office',
            'payPeriod',
            'encoder',
            'ledgerEntries.leaveType',
        ])
        ->orderBy('encoded_at', 'desc')
        ->paginate(15);

        return view('hr.tardiness_entry', compact(
            'offices',
            'employees',
            'payPeriods',
            'selectedPeriodId',
            'recentDeductions',
            'currentYear'
        ));
    }

    /**
     * Process semi-monthly deduction through StarlaComputationService.
     */
    public function store(Request $request, StarlaComputationService $starlaService)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,employee_id'],
            'pay_period_id' => ['required', 'integer', 'exists:pay_periods,pay_period_id'],
            'days_absent' => ['required', 'numeric', 'min:0', 'max:31'],
            'late_hours' => ['required', 'integer', 'min:0', 'max:160'],
            'late_minutes' => ['required', 'integer', 'min:0', 'max:59'],
        ]);

        $employee = Employee::findOrFail((int) $validated['employee_id']);
        if ($employee->isJobOrder()) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Job Order personnel ({$employee->full_name}) do not earn leave credits under CSC rules and cannot be encoded for leave ledger deductions.");
        }

        try {
            $result = $starlaService->processSemiMonthlyDeduction(
                (int) $validated['employee_id'],
                (int) $validated['pay_period_id'],
                (float) $validated['days_absent'],
                (int) $validated['late_hours'],
                (int) $validated['late_minutes'],
                Auth::id() ?? 2
            );

            $formattedDeduction = number_format($result['total_deduction'], 3);

            return redirect()->route('hr.tardiness.create', ['pay_period_id' => $validated['pay_period_id']])
                ->with('success', "Starla computation successful! Deducted {$formattedDeduction} from {$result['employee_no']} and recorded '{$result['leave_record_particular']}' into Leave Record card.")
                ->with('calculation_result', $result);

        } catch (DomainException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * AJAX Live Preview endpoint: Returns discrete Starla decimals before saving.
     */
    public function preview(Request $request, StarlaComputationService $starlaService): JsonResponse
    {
        $hours = (int) $request->input('late_hours', 0);
        $minutes = (int) $request->input('late_minutes', 0);
        $daysAbsent = (float) $request->input('days_absent', 0.0);

        try {
            $tardinessDeduction = $starlaService->calculateTardinessDeduction($hours, $minutes);
            $absenceDeduction = $starlaService->calculateAbsenceDeduction($daysAbsent);
            $totalDeduction = round($tardinessDeduction + $absenceDeduction, 3);

            return response()->json([
                'success' => true,
                'tardiness_deduction' => $tardinessDeduction,
                'absence_deduction' => $absenceDeduction,
                'total_deduction' => $totalDeduction,
                'tardy_formatted' => number_format($tardinessDeduction, 3),
                'absence_formatted' => number_format($absenceDeduction, 3),
                'formatted' => number_format($totalDeduction, 3),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the official Civil Service "Starla" conversion tables viewer for HR Staff and Admin.
     * Digitized from verified physical binder at HRMU Basud (Pages 13 and 15: Tables II and IV).
     * Note: Per HRMU Basud operational policy, client exclusively uses Table II and Table IV.
     */
    public function starlaTables(Request $request)
    {
        // 1. Table IV: Working Hours (1 to 8 Hours)
        $table4Hours = StarlaReference::where('table_ref', 'Table IV')
            ->where('input_value', 'like', '%hour%')
            ->get()
            ->sortBy(function ($item) {
                return (int) filter_var($item->input_value, FILTER_SANITIZE_NUMBER_INT);
            })
            ->values();

        // 2. Table IV: Working Minutes (1 to 60 Minutes)
        $table4Minutes = StarlaReference::where('table_ref', 'Table IV')
            ->where('input_value', 'like', '%min%')
            ->get()
            ->sortBy(function ($item) {
                return (int) filter_var($item->input_value, FILTER_SANITIZE_NUMBER_INT);
            })
            ->values();

        // 3. Table II: Daily Leave Credits Earned (1 to 30 Days of Service)
        $table2Daily = StarlaReference::where('table_ref', 'Table II')
            ->get()
            ->sortBy(function ($item) {
                return (int) filter_var($item->input_value, FILTER_SANITIZE_NUMBER_INT);
            })
            ->values();

        $activeTab = $request->query('tab', 'table_4');
        $searchQuery = trim($request->query('q', ''));
        $totalRecords = $table4Hours->count() + $table4Minutes->count() + $table2Daily->count();

        return view('hr.starla_tables', compact(
            'table4Hours',
            'table4Minutes',
            'table2Daily',
            'activeTab',
            'searchQuery',
            'totalRecords'
        ));
    }
}
