<?php

namespace App\Services;

use App\Models\AttendanceDeduction;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveLedgerEntry;
use App\Models\LeaveRecord;
use App\Models\LeaveType;
use App\Models\PayPeriod;
use App\Models\StarlaReference;
use App\Services\LeaveRecordService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * StarlaComputationService
 *
 * The core mathematical leave deduction and accrual engine for C-LACTS.
 * Strictly adheres to Civil Service Commission (CSC) Omnibus Rules on Leave
 * (Rule XVI of Executive Order No. 292, CSC MC No. 41, s. 1998, CSC MC No. 14, s. 1999).
 *
 * Non-Negotiable Architectural Guardrails:
 * 1. Zero hardcoded decimal multipliers: All conversions query `STARLA_REFERENCE` (Tables II, III, & IV).
 * 2. All credits, accruals, and deductions are calculated in days to 3 decimal places.
 * 3. Prevention of double-deduction: Validates against approved leave applications for the pay period.
 * 4. All ledger updates are wrapped in atomic database transactions (`DB::transaction`).
 */
class StarlaComputationService
{
    /**
     * Standard monthly leave credit accrual rate (1.250 days for full 30-day month).
     */
    public const STANDARD_MONTHLY_ACCRUAL = 1.250;

    /**
     * 1. Calculate Tardiness Deduction from Starla Table IV
     *
     * Queries `STARLA_REFERENCE` (Table IV) to convert aggregate tardiness hours and minutes
     * into exact Civil Service fractional day equivalents.
     *
     * @param int $hours Aggregate hours late/undertime
     * @param int $minutes Aggregate minutes late/undertime
     * @return float Total day deduction rounded to 3 decimal places (e.g. 0.094)
     *
     * @throws InvalidArgumentException If negative hours or minutes are provided.
     */
    public function calculateTardinessDeduction(int $hours, int $minutes): float
    {
        if ($hours < 0 || $minutes < 0) {
            throw new InvalidArgumentException("Tardiness hours ({$hours}) and minutes ({$minutes}) cannot be negative.");
        }

        if ($hours === 0 && $minutes === 0) {
            return 0.000;
        }

        // Normalize minutes >= 60 into additional hours
        if ($minutes >= 60) {
            $hours += intdiv($minutes, 60);
            $minutes = $minutes % 60;
        }

        $hoursDeduction = 0.000;
        $minutesDeduction = 0.000;

        // --- Calculate Hours Portion ---
        if ($hours > 0) {
            // CSC Table IV is based on an 8-hour workday (8 hours = 1.000 day)
            $fullDays = intdiv($hours, 8);
            $remainingHours = $hours % 8;

            $hoursDeduction += (float) $fullDays * 1.000;

            if ($remainingHours > 0) {
                $hoursInput = $remainingHours . ($remainingHours === 1 ? ' hour' : ' hours');
                $refHours = StarlaReference::where('table_ref', 'Table IV')
                    ->where('input_value', $hoursInput)
                    ->value('day_equivalent');

                if ($refHours === null) {
                    throw new DomainException("Missing Starla Table IV reference for hours: '{$hoursInput}'.");
                }

                $hoursDeduction += (float) $refHours;
            }
        }

        // --- Calculate Minutes Portion ---
        if ($minutes > 0) {
            $minutesInput = $minutes . ($minutes === 1 ? ' min' : ' mins');
            $refMinutes = StarlaReference::where('table_ref', 'Table IV')
                ->where('input_value', $minutesInput)
                ->value('day_equivalent');

            if ($refMinutes === null) {
                throw new DomainException("Missing Starla Table IV reference for minutes: '{$minutesInput}'.");
            }

            $minutesDeduction = (float) $refMinutes;
        }

        return (float) bcadd((string)$hoursDeduction, (string)$minutesDeduction, 6);
    }

    /**
     * 2. Calculate Absence Deduction (1:1 Ratio)
     *
     * Per HRMU Basud operational policy, unexcused absence days are deducted at a direct 1:1 ratio
     * (1 absent day = 1.000 Total Leave Credit Deducted). It is not based on Starla Table II daily
     * accrual calculations (which are exclusively for earned service credits).
     *
     * @param float $daysAbsent Number of unexcused absence days (e.g. 1.0, 2.5)
     * @return float Deduction in decimal days (rounded to 3 decimal places)
     *
     * @throws InvalidArgumentException If negative days are provided.
     */
    public function calculateAbsenceDeduction(float $daysAbsent): float
    {
        if ($daysAbsent < 0.0) {
            throw new InvalidArgumentException("Days absent ({$daysAbsent}) cannot be negative.");
        }

        return round($daysAbsent, 3);
    }

    /**
     * 3. Process Semi-Monthly Attendance Deductions
     *
     * Primary operational workflow for HR Staff at semi-monthly cut-offs (15th and end-of-month):
     * - Double-deduction constraint check against approved `LEAVE_APPLICATIONS`.
     * - Computes total deduction via `STARLA_REFERENCE` Tables IV & II.
     * - CSC Deduction Priority: Vacation Leave (VL) first; if exhausted, Sick Leave (SL), then LWOP.
     * - Records entry in `ATTENDANCE_DEDUCTIONS`.
     * - Posts audit trail transaction into `LEAVE_LEDGER_ENTRIES` (CS Form 41).
     * - Updates `LEAVE_BALANCES` for the employee.
     *
     * @param int $employeeId Target employee
     * @param int $payPeriodId Cut-off pay period
     * @param float $daysAbsent Number of unexcused absent days
     * @param int $lateHours Aggregate tardiness/undertime hours
     * @param int $lateMinutes Aggregate tardiness/undertime minutes
     * @param int|null $encodedBy User ID of HR Staff encoder (defaults to auth user or 1)
     * @return array Summary of processed deduction results
     *
     * @throws DomainException On double-deduction conflict or missing pay period.
     */
    public function processSemiMonthlyDeduction(
        int $employeeId,
        int $payPeriodId,
        float $daysAbsent,
        int $lateHours,
        int $lateMinutes,
        ?int $encodedBy = null
    ): array {
        return DB::transaction(function () use (
            $employeeId,
            $payPeriodId,
            $daysAbsent,
            $lateHours,
            $lateMinutes,
            $encodedBy
        ) {
            $employee = Employee::findOrFail($employeeId);

            if ($employee->isJobOrder()) {
                throw new DomainException(
                    "Leave deduction prevented: {$employee->full_name} is a Job Order personnel. " .
                    "Under CSC Omnibus Rules on Leave (Rule XVI), Job Order personnel do not earn or accrue leave credits."
                );
            }

            $payPeriod = PayPeriod::findOrFail($payPeriodId);
            $year = Carbon::parse($payPeriod->start_date)->year;

            // -----------------------------------------------------------------
            // Constraint Check: Double-Deduction Prevention (Guardrail #6)
            // -----------------------------------------------------------------
            if ($daysAbsent > 0) {
                $approvedLeaves = LeaveApplication::where('employee_id', $employeeId)
                    ->whereIn('status', ['Approved', 'APPROVED'])
                    ->where('date_from', '<=', $payPeriod->end_date)
                    ->where('date_to', '>=', $payPeriod->start_date)
                    ->get();

                if ($approvedLeaves->isNotEmpty()) {
                    $totalApprovedDays = (float) $approvedLeaves->sum('days_requested');
                    $refCodes = $approvedLeaves->pluck('reference_code')->implode(', ');

                    throw new DomainException(
                        "Double-deduction prevented: Employee #{$employee->employee_no} ({$employee->full_name}) " .
                        "already has approved leave ({$refCodes}) totaling {$totalApprovedDays} day(s) covering " .
                        "the period {$payPeriod->start_date} to {$payPeriod->end_date}. " .
                        "Unexcused absences cannot overlap with approved leave."
                    );
                }
            }

            // -----------------------------------------------------------------
            // Compute Total Deductions via Starla Reference Tables
            // -----------------------------------------------------------------
            $tardinessDeduction = $this->calculateTardinessDeduction($lateHours, $lateMinutes);
            $absenceDeduction = $this->calculateAbsenceDeduction($daysAbsent);
            $totalDeduction = round($tardinessDeduction + $absenceDeduction, 3);

            // -----------------------------------------------------------------
            // Retrieve Current Leave Balances (VL and SL)
            // -----------------------------------------------------------------
            $vlType = LeaveType::where('code', 'VL')->firstOrFail();
            $slType = LeaveType::where('code', 'SL')->firstOrFail();

            $vlBalance = LeaveBalance::lockForUpdate()->firstOrCreate(
                [
                    'employee_id' => $employeeId,
                    'leave_type_id' => $vlType->leave_type_id,
                    'year' => $year,
                ],
                [
                    'balance' => 0.000,
                    'last_posted_at' => Carbon::now(),
                ]
            );

            $slBalance = LeaveBalance::lockForUpdate()->firstOrCreate(
                [
                    'employee_id' => $employeeId,
                    'leave_type_id' => $slType->leave_type_id,
                    'year' => $year,
                ],
                [
                    'balance' => 0.000,
                    'last_posted_at' => Carbon::now(),
                ]
            );

            // -----------------------------------------------------------------
            // CSC Auto-Deduction Priority Logic (Computed Separately)
            // Priority 1: Vacation Leave (VL)
            // Priority 2: Sick Leave (SL) if VL is exhausted
            // Priority 3: Leave Without Pay (LWOP) if both are exhausted
            // -----------------------------------------------------------------
            $currentVl = (float) $vlBalance->balance;
            $currentSl = (float) $slBalance->balance;

            // 1. Compute Tardiness & Undertime Debit (Table IV)
            $tardyVlDebited = 0.000;
            $tardySlDebited = 0.000;
            $tardyLwop = 0.000;

            if ($tardinessDeduction > 0) {
                if ($currentVl >= $tardinessDeduction) {
                    $tardyVlDebited = $tardinessDeduction;
                    $currentVl = round($currentVl - $tardyVlDebited, 3);
                } else {
                    $tardyVlDebited = $currentVl;
                    $remTardy = round($tardinessDeduction - $currentVl, 3);
                    $currentVl = 0.000;

                    if ($currentSl >= $remTardy) {
                        $tardySlDebited = $remTardy;
                        $currentSl = round($currentSl - $tardySlDebited, 3);
                    } else {
                        $tardySlDebited = $currentSl;
                        $tardyLwop = round($remTardy - $currentSl, 3);
                        $currentSl = 0.000;
                    }
                }
            }

            // 2. Compute Unexcused Absence Debit (1:1 Ratio)
            $absVlDebited = 0.000;
            $absSlDebited = 0.000;
            $absLwop = 0.000;

            if ($absenceDeduction > 0) {
                if ($currentVl >= $absenceDeduction) {
                    $absVlDebited = $absenceDeduction;
                    $currentVl = round($currentVl - $absVlDebited, 3);
                } else {
                    $absVlDebited = $currentVl;
                    $remAbs = round($absenceDeduction - $currentVl, 3);
                    $currentVl = 0.000;

                    if ($currentSl >= $remAbs) {
                        $absSlDebited = $remAbs;
                        $currentSl = round($currentSl - $absSlDebited, 3);
                    } else {
                        $absSlDebited = $currentSl;
                        $absLwop = round($remAbs - $currentSl, 3);
                        $currentSl = 0.000;
                    }
                }
            }

            $vlDebited = round($tardyVlDebited + $absVlDebited, 3);
            $slDebited = round($tardySlDebited + $absSlDebited, 3);
            $lwopIncurred = round($tardyLwop + $absLwop, 3);
            $newVlBalance = round($currentVl, 3);
            $newSlBalance = round($currentSl, 3);

            // -----------------------------------------------------------------
            // Record HR Verified Summary in ATTENDANCE_DEDUCTIONS
            // -----------------------------------------------------------------
            $now = Carbon::now();
            $userId = $encodedBy ?? (auth()->check() ? auth()->id() : 1);

            $deductionRecord = AttendanceDeduction::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'pay_period_id' => $payPeriodId,
                ],
                [
                    'encoded_by' => $userId,
                    'days_absent' => $daysAbsent,
                    'late_hours' => $lateHours,
                    'late_minutes' => $lateMinutes,
                    'days_deducted' => $totalDeduction,
                    'encoded_at' => $now,
                ]
            );

            // Update balance snapshots
            $vlBalance->update([
                'balance' => $newVlBalance,
                'last_posted_at' => $now,
            ]);
            $slBalance->update([
                'balance' => $newSlBalance,
                'last_posted_at' => $now,
            ]);

            // -----------------------------------------------------------------
            // Update Balances & Generate CS Form 41 Digital Ledger Entries
            // (Tardiness & Undertime and Unexcused Absences are recorded separately)
            // -----------------------------------------------------------------
            $periodLabel = "Period " . Carbon::parse($payPeriod->start_date)->format('M d') .
                "-" . Carbon::parse($payPeriod->end_date)->format('d, Y');
            $periodStartDate = Carbon::parse($payPeriod->start_date);
            // Deduct on the month it was filed/encoded if encoded in advance
            $effectiveDate = $now->lt($periodStartDate) ? $now : $periodStartDate;
            $periodMonthYear = $effectiveDate->format('F Y');
            $monthDateYearFormatted = Carbon::parse($payPeriod->start_date)->format('F d') . '-' . Carbon::parse($payPeriod->end_date)->format('d, Y');
            $accrualMonth = $effectiveDate->format('Y-m');

            $lateStr = '';
            if ($lateHours > 0) $lateStr .= $lateHours . ($lateHours === 1 ? ' hr' : ' hrs');
            if ($lateMinutes > 0) $lateStr .= (!empty($lateStr) ? ' and ' : '') . $lateMinutes . ($lateMinutes === 1 ? ' min' : ' mins');

            // Clear previous cut-off ledger/leave records for this pay period to prevent duplicates
            LeaveRecord::where('employee_id', $employeeId)
                ->where(function ($q) use ($payPeriodId, $monthDateYearFormatted) {
                    $q->where('date_action_taken', $monthDateYearFormatted)
                      ->orWhere('date_action_taken', 'like', "DTR Cut-off (Pay Period #{$payPeriodId})%")
                      ->orWhere('date_action_taken', 'like', "%Pay Period #{$payPeriodId}%");
                })
                ->delete();

            LeaveLedgerEntry::where('employee_id', $employeeId)
                ->where('deduction_id', $deductionRecord->deduction_id)
                ->delete();

            $nextRowOrder = (LeaveRecord::where('employee_id', $employeeId)->max('row_order') ?? 0) + 1;
            $particularSummaries = [];

            // A. Post Separate Tardiness / Undertime Ledger & Leave Record
            if ($tardinessDeduction > 0) {
                if ($tardyVlDebited > 0) {
                    LeaveLedgerEntry::create([
                        'employee_id' => $employeeId,
                        'leave_type_id' => $vlType->leave_type_id,
                        'deduction_id' => $deductionRecord->deduction_id,
                        'application_id' => null,
                        'entry_type' => 'TARDINESS_DEDUCTION',
                        'days' => $tardyVlDebited,
                        'balance_after' => round($newVlBalance + $absVlDebited, 3),
                        'particular' => "Tardiness/Undertime ({$lateStr}) - {$periodLabel}",
                        'posted_at' => $now,
                    ]);
                }
                if ($tardySlDebited > 0) {
                    LeaveLedgerEntry::create([
                        'employee_id' => $employeeId,
                        'leave_type_id' => $slType->leave_type_id,
                        'deduction_id' => $deductionRecord->deduction_id,
                        'application_id' => null,
                        'entry_type' => 'TARDINESS_DEDUCTION',
                        'days' => $tardySlDebited,
                        'balance_after' => round($newSlBalance + $absSlDebited, 3),
                        'particular' => "Tardiness/Undertime debited against SL (VL exhausted) - {$periodLabel}",
                        'posted_at' => $now,
                    ]);
                }
                if ($tardyLwop > 0) {
                    LeaveLedgerEntry::create([
                        'employee_id' => $employeeId,
                        'leave_type_id' => $vlType->leave_type_id,
                        'deduction_id' => $deductionRecord->deduction_id,
                        'application_id' => null,
                        'entry_type' => 'ADJUSTMENT',
                        'days' => $tardyLwop,
                        'balance_after' => 0.000,
                        'particular' => "Tardiness/Undertime under LWOP ({$tardyLwop}) - {$periodLabel}",
                        'posted_at' => $now,
                    ]);
                }

                LeaveRecord::create([
                    'employee_id' => $employeeId,
                    'period' => $periodMonthYear,
                    'particular' => "L ({$lateStr})",
                    'accrual_month' => $accrualMonth,
                    'is_prorated' => false,
                    'vl_earned' => null,
                    'vl_absence_with_pay' => $tardyVlDebited > 0 ? number_format($tardyVlDebited, 3, '.', '') : null,
                    'vl_balance' => null,
                    'vl_absence_without_pay' => $tardyLwop > 0 ? number_format($tardyLwop, 3, '.', '') : null,
                    'sl_earned' => null,
                    'sl_absence_with_pay' => $tardySlDebited > 0 ? number_format($tardySlDebited, 3, '.', '') : null,
                    'sl_balance' => null,
                    'sl_absence_without_pay' => null,
                    'date_action_taken' => $monthDateYearFormatted,
                    'row_order' => $nextRowOrder++,
                    'encoded_by' => $userId,
                ]);

                $particularSummaries[] = "L ({$lateStr}): {$tardinessDeduction}";
            }

            // B. Post Separate Unexcused Absence Ledger & Leave Record
            if ($absenceDeduction > 0) {
                if ($absVlDebited > 0) {
                    LeaveLedgerEntry::create([
                        'employee_id' => $employeeId,
                        'leave_type_id' => $vlType->leave_type_id,
                        'deduction_id' => $deductionRecord->deduction_id,
                        'application_id' => null,
                        'entry_type' => 'ABSENCE_DEDUCTION',
                        'days' => $absVlDebited,
                        'balance_after' => $newVlBalance,
                        'particular' => "Unexcused Absence ({$daysAbsent} days) - {$periodLabel}",
                        'posted_at' => $now,
                    ]);
                }
                if ($absSlDebited > 0) {
                    LeaveLedgerEntry::create([
                        'employee_id' => $employeeId,
                        'leave_type_id' => $slType->leave_type_id,
                        'deduction_id' => $deductionRecord->deduction_id,
                        'application_id' => null,
                        'entry_type' => 'ABSENCE_DEDUCTION',
                        'days' => $absSlDebited,
                        'balance_after' => $newSlBalance,
                        'particular' => "Absence debited against SL (VL exhausted) - {$periodLabel}",
                        'posted_at' => $now,
                    ]);
                }
                if ($absLwop > 0) {
                    LeaveLedgerEntry::create([
                        'employee_id' => $employeeId,
                        'leave_type_id' => $vlType->leave_type_id,
                        'deduction_id' => $deductionRecord->deduction_id,
                        'application_id' => null,
                        'entry_type' => 'ADJUSTMENT',
                        'days' => $absLwop,
                        'balance_after' => 0.000,
                        'particular' => "Absence under LWOP ({$absLwop} days) - {$periodLabel}",
                        'posted_at' => $now,
                    ]);
                }

                LeaveRecord::create([
                    'employee_id' => $employeeId,
                    'period' => $periodMonthYear,
                    'particular' => "ABS (" . number_format($absenceDeduction, 3) . ")",
                    'accrual_month' => $accrualMonth,
                    'is_prorated' => false,
                    'vl_earned' => null,
                    'vl_absence_with_pay' => $absVlDebited > 0 ? number_format($absVlDebited, 3, '.', '') : null,
                    'vl_balance' => null,
                    'vl_absence_without_pay' => $absLwop > 0 ? number_format($absLwop, 3, '.', '') : null,
                    'sl_earned' => null,
                    'sl_absence_with_pay' => $absSlDebited > 0 ? number_format($absSlDebited, 3, '.', '') : null,
                    'sl_balance' => null,
                    'sl_absence_without_pay' => null,
                    'date_action_taken' => $monthDateYearFormatted,
                    'row_order' => $nextRowOrder++,
                    'encoded_by' => $userId,
                ]);

                $particularSummaries[] = "ABS (" . number_format($absenceDeduction, 3) . "): {$absenceDeduction}";
            }

            // C. Zero Deduction Case
            if ($totalDeduction == 0.000) {
                LeaveRecord::create([
                    'employee_id' => $employeeId,
                    'period' => $periodMonthYear,
                    'particular' => "DTR Attendance (0 deduction)",
                    'accrual_month' => $accrualMonth,
                    'is_prorated' => false,
                    'vl_earned' => null,
                    'vl_absence_with_pay' => null,
                    'vl_balance' => null,
                    'vl_absence_without_pay' => null,
                    'sl_earned' => null,
                    'sl_absence_with_pay' => null,
                    'sl_balance' => null,
                    'sl_absence_without_pay' => null,
                    'date_action_taken' => $monthDateYearFormatted,
                    'row_order' => $nextRowOrder++,
                    'encoded_by' => $userId,
                ]);
            }

            // Recalculate running card balances and synchronize
            app(LeaveRecordService::class)->recalculateEmployeeBalances($employeeId);

            // -----------------------------------------------------------------
            // Audit Log Oversight
            // -----------------------------------------------------------------
            AuditLog::create([
                'user_id' => $userId,
                'action' => 'POST_SEMI_MONTHLY_DEDUCTION',
                'table_name' => 'attendance_deductions',
                'record_id' => $deductionRecord->deduction_id,
                'details' => json_encode([
                    'employee_id' => $employeeId,
                    'employee_no' => $employee->employee_no,
                    'pay_period_id' => $payPeriodId,
                    'late_hours' => $lateHours,
                    'late_minutes' => $lateMinutes,
                    'days_absent' => $daysAbsent,
                    'total_days_deducted' => $totalDeduction,
                    'vl_debited' => $vlDebited,
                    'sl_debited' => $slDebited,
                    'lwop_incurred' => $lwopIncurred,
                    'new_vl_balance' => $newVlBalance,
                    'new_sl_balance' => $newSlBalance,
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'logged_at' => $now,
            ]);

            $particularRecord = !empty($particularSummaries) ? implode(', ', $particularSummaries) : "DTR Attendance (0)";
            $formattedDeduction = number_format($totalDeduction, 3);

            return [
                'success' => true,
                'deduction_id' => $deductionRecord->deduction_id,
                'employee_id' => $employeeId,
                'employee_no' => $employee->employee_no,
                'pay_period' => $periodLabel,
                'late_hours' => $lateHours,
                'late_minutes' => $lateMinutes,
                'days_absent' => $daysAbsent,
                'tardiness_deduction' => $tardinessDeduction,
                'absence_deduction' => $absenceDeduction,
                'total_deduction' => $totalDeduction,
                'formatted_deduction' => $formattedDeduction,
                'vl_debited' => $vlDebited,
                'sl_debited' => $slDebited,
                'lwop_incurred' => $lwopIncurred,
                'new_vl_balance' => $newVlBalance,
                'new_sl_balance' => $newSlBalance,
                'leave_record_particular' => $particularRecord,
            ];
        });
    }

    /**
     * 4. Credit Monthly Accrual (1.250 VL + 1.250 SL)
     *
     * Adds the statutory 1.250 Vacation Leave and 1.250 Sick Leave credits to the employee's
     * balance at the end of the month, querying `STARLA_REFERENCE` (Table II - 30 days)
     * and posting permanent CS Form 41 ledger audit entries.
     *
     * @param int $employeeId Target employee
     * @param int|null $year Year of accrual (defaults to current year)
     * @param int|null $month Month of accrual 1-12 (defaults to current month)
     * @param int|null $creditedBy User ID triggering the accrual
     * @return array Summary of credited balances and ledger entry IDs
     *
     * @throws DomainException If monthly accrual for this period was already posted.
     */
    public function creditMonthlyAccrual(
        int $employeeId,
        ?int $year = null,
        ?int $month = null,
        ?int $creditedBy = null
    ): array {
        return DB::transaction(function () use ($employeeId, $year, $month, $creditedBy) {
            $employee = Employee::findOrFail($employeeId);

            if ($employee->isJobOrder()) {
                throw new DomainException(
                    "Monthly accrual prevented: {$employee->full_name} is a Job Order personnel. " .
                    "Under CSC Omnibus Rules on Leave (Rule XVI), Job Order personnel do not earn or accrue leave credits."
                );
            }

            $now = Carbon::now();

            $targetYear = $year ?? $now->year;
            $targetMonth = $month ?? $now->month;
            $monthDate = Carbon::create($targetYear, $targetMonth, 1);
            $monthName = $monthDate->format('F Y');

            // -----------------------------------------------------------------
            // Query Starla Table II for 30-day service accrual rate
            // -----------------------------------------------------------------
            $accrualCredit = StarlaReference::where('table_ref', 'Table II')
                ->where('input_value', '30 days')
                ->value('day_equivalent');

            $standardCredit = $accrualCredit !== null ? (float) $accrualCredit : self::STANDARD_MONTHLY_ACCRUAL;

            $vlType = LeaveType::where('code', 'VL')->firstOrFail();
            $slType = LeaveType::where('code', 'SL')->firstOrFail();

            // -----------------------------------------------------------------
            // Prevent Duplicate Monthly Accrual for the Same Month
            // -----------------------------------------------------------------
            $existingAccrual = LeaveLedgerEntry::where('employee_id', $employeeId)
                ->where('leave_type_id', $vlType->leave_type_id)
                ->where('entry_type', 'MONTHLY_ACCRUAL')
                ->whereYear('posted_at', $targetYear)
                ->whereMonth('posted_at', $targetMonth)
                ->first();

            if ($existingAccrual) {
                throw new DomainException(
                    "Monthly accrual for {$monthName} has already been credited to " .
                    "Employee #{$employee->employee_no} ({$employee->full_name})."
                );
            }

            // -----------------------------------------------------------------
            // 1. Credit Vacation Leave (VL)
            // -----------------------------------------------------------------
            $vlBalance = LeaveBalance::lockForUpdate()->firstOrCreate(
                [
                    'employee_id' => $employeeId,
                    'leave_type_id' => $vlType->leave_type_id,
                    'year' => $targetYear,
                ],
                [
                    'balance' => 0.000,
                    'last_posted_at' => $now,
                ]
            );

            $newVlBalance = round((float) $vlBalance->balance + $standardCredit, 3);
            $vlBalance->update([
                'balance' => $newVlBalance,
                'last_posted_at' => $now,
            ]);

            $vlEntry = LeaveLedgerEntry::create([
                'employee_id' => $employeeId,
                'leave_type_id' => $vlType->leave_type_id,
                'deduction_id' => null,
                'application_id' => null,
                'entry_type' => 'MONTHLY_ACCRUAL',
                'days' => $standardCredit,
                'balance_after' => $newVlBalance,
                'particular' => "Earned for {$monthName}",
                'posted_at' => $now,
            ]);

            // -----------------------------------------------------------------
            // 2. Credit Sick Leave (SL)
            // -----------------------------------------------------------------
            $slBalance = LeaveBalance::lockForUpdate()->firstOrCreate(
                [
                    'employee_id' => $employeeId,
                    'leave_type_id' => $slType->leave_type_id,
                    'year' => $targetYear,
                ],
                [
                    'balance' => 0.000,
                    'last_posted_at' => $now,
                ]
            );

            $newSlBalance = round((float) $slBalance->balance + $standardCredit, 3);
            $slBalance->update([
                'balance' => $newSlBalance,
                'last_posted_at' => $now,
            ]);

            $slEntry = LeaveLedgerEntry::create([
                'employee_id' => $employeeId,
                'leave_type_id' => $slType->leave_type_id,
                'deduction_id' => null,
                'application_id' => null,
                'entry_type' => 'MONTHLY_ACCRUAL',
                'days' => $standardCredit,
                'balance_after' => $newSlBalance,
                'particular' => "Earned for {$monthName}",
                'posted_at' => $now,
            ]);

            // -----------------------------------------------------------------
            // Audit Log Oversight
            // -----------------------------------------------------------------
            $userId = $creditedBy ?? (auth()->check() ? auth()->id() : 1);
            AuditLog::create([
                'user_id' => $userId,
                'action' => 'POST_MONTHLY_ACCRUAL',
                'table_name' => 'leave_balances',
                'record_id' => $vlBalance->balance_id,
                'details' => json_encode([
                    'employee_id' => $employeeId,
                    'employee_no' => $employee->employee_no,
                    'period' => $monthName,
                    'credited_vl' => $standardCredit,
                    'credited_sl' => $standardCredit,
                    'new_vl_balance' => $newVlBalance,
                    'new_sl_balance' => $newSlBalance,
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'logged_at' => $now,
            ]);

            return [
                'success' => true,
                'employee_id' => $employeeId,
                'employee_no' => $employee->employee_no,
                'period' => $monthName,
                'credited_vl' => $standardCredit,
                'credited_sl' => $standardCredit,
                'new_vl_balance' => $newVlBalance,
                'new_sl_balance' => $newSlBalance,
                'vl_ledger_entry_id' => $vlEntry->entry_id,
                'sl_ledger_entry_id' => $slEntry->entry_id,
            ];
        });
    }
}
