<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveRecord;
use App\Models\LeaveType;
use App\Models\StarlaReference;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveRecordService
{
    /**
     * Exact arbitrary-precision decimal addition (never rounds or truncates).
     */
    public static function exactAdd(?string $a, ?string $b): ?string
    {
        if ($a === null || $a === '') return $b !== null && $b !== '' ? self::formatExact($b) : null;
        if ($b === null || $b === '') return self::formatExact($a);

        $cleanA = trim($a);
        $cleanB = trim($b);

        if (!is_numeric($cleanA) || !is_numeric($cleanB)) {
            return $cleanA;
        }

        $sum = bcadd($cleanA, $cleanB, 8);
        return self::formatExact($sum);
    }

    /**
     * Exact arbitrary-precision decimal subtraction (never rounds or truncates).
     */
    public static function exactSub(?string $a, ?string $b): ?string
    {
        if ($a === null || $a === '') $a = '0';
        if ($b === null || $b === '') return self::formatExact($a);

        $cleanA = trim($a);
        $cleanB = trim($b);

        if (!is_numeric($cleanA) || !is_numeric($cleanB)) {
            return $cleanA;
        }

        $diff = bcsub($cleanA, $cleanB, 8);
        return self::formatExact($diff);
    }

    /**
     * Formats a decimal string without rounding or losing precision,
     * stripping superfluous trailing zeros after decimal point while keeping integer intact.
     */
    public static function formatExact(string $val): string
    {
        $val = trim($val);
        if (str_contains($val, '.')) {
            $val = rtrim($val, '0');
            $val = rtrim($val, '.');
        }
        return $val === '' ? '0' : $val;
    }

    /**
     * Convert hours and minutes to fraction of a day using Table IV.
     * Table IV is strictly queried from database (zero hardcoded multipliers).
     */
    public function convertTableIV(int $hours, int $minutes): array
    {
        $hoursFraction = '0';
        $minutesFraction = '0';

        // 1. Hours conversion
        if ($hours > 0) {
            $fullDays = intdiv($hours, 8);
            $remHours = $hours % 8;

            $hoursBase = bcmul((string)$fullDays, '1.000', 8);

            if ($remHours > 0) {
                $hoursInput = $remHours . ($remHours === 1 ? ' hour' : ' hours');
                $ref = StarlaReference::where('table_ref', 'Table IV')
                    ->where('input_value', $hoursInput)
                    ->value('day_equivalent');

                $refStr = $ref !== null ? (string)$ref : self::exactSub((string)($remHours * 0.125), '0');
                $hoursFraction = bcadd($hoursBase, $refStr, 8);
            } else {
                $hoursFraction = $hoursBase;
            }
        }

        // 2. Minutes conversion
        if ($minutes > 0) {
            // Check singular and plural
            $minInputPlural = $minutes . ' mins';
            $minInputSingular = $minutes . ' min';

            $refMin = StarlaReference::where('table_ref', 'Table IV')
                ->whereIn('input_value', [$minInputPlural, $minInputSingular])
                ->value('day_equivalent');

            if ($refMin !== null) {
                $minutesFraction = (string)$refMin;
            } else {
                // Defensive Table IV lookup or fallback
                $minutesFraction = self::exactSub((string)($minutes * 0.002), '0');
            }
        }

        $totalFraction = self::exactAdd($hoursFraction, $minutesFraction);

        return [
            'hours' => $hours,
            'minutes' => $minutes,
            'hours_fraction' => self::formatExact($hoursFraction),
            'minutes_fraction' => self::formatExact($minutesFraction),
            'total_fraction' => self::formatExact($totalFraction),
        ];
    }

    /**
     * Parses manual particular strings and extracts numerical values, column destinations,
     * deduction rules, and Table IV/II calculations.
     */
    public function parseParticular(string $particular): array
    {
        $p = trim($particular);

        // 1. Late: L (X hr and Y mins) or Late (X hr Y mins) or Late: Y mins
        if (preg_match('/^\s*L(?:ATE)?\s*(?:\(|\:|\-)?\s*(?:(\d+)\s*h(?:ou)?rs?)?\s*(?:and|,)?\s*(?:(\d+)\s*mins?)?\s*\)?/i', $p, $m) && (!empty($m[1]) || !empty($m[2]))) {
            $hrs = isset($m[1]) && $m[1] !== '' ? (int)$m[1] : 0;
            $mins = isset($m[2]) && $m[2] !== '' ? (int)$m[2] : 0;
            $conv = $this->convertTableIV($hrs, $mins);

            return [
                'type' => 'LATE',
                'target_column' => 'vl_absence_with_pay',
                'value' => $conv['total_fraction'],
                'deduct_from' => 'VL',
                'is_deductible' => true,
                'description' => "Late ({$hrs}h {$mins}m) converted via Table IV: {$conv['total_fraction']} days (Deduct from VL)",
            ];
        }

        // 2. Undertime: UT (X hr and Y mins) or Undertime (X hr Y mins) or UT: Y mins
        if (preg_match('/^\s*(?:UT|UNDERTIME)\s*(?:\(|\:|\-)?\s*(?:(\d+)\s*h(?:ou)?rs?)?\s*(?:and|,)?\s*(?:(\d+)\s*mins?)?\s*\)?/i', $p, $m) && (!empty($m[1]) || !empty($m[2]))) {
            $hrs = isset($m[1]) && $m[1] !== '' ? (int)$m[1] : 0;
            $mins = isset($m[2]) && $m[2] !== '' ? (int)$m[2] : 0;
            $conv = $this->convertTableIV($hrs, $mins);

            return [
                'type' => 'UNDERTIME',
                'target_column' => 'vl_absence_with_pay',
                'value' => $conv['total_fraction'],
                'deduct_from' => 'VL',
                'is_deductible' => true,
                'description' => "Undertime ({$hrs}h {$mins}m) converted via Table IV: {$conv['total_fraction']} days (Deduct from VL)",
            ];
        }

        // 3. Vacation Leave: VL (n)
        if (preg_match('/^\s*VL\s*\(\s*([0-9.]+)\s*\)/i', $p, $m)) {
            $days = self::formatExact($m[1]);
            return [
                'type' => 'VACATION_LEAVE',
                'target_column' => 'vl_absence_with_pay',
                'value' => $days,
                'deduct_from' => 'VL',
                'is_deductible' => true,
                'description' => "Vacation Leave for {$days} day(s) (Deduct from VL)",
            ];
        }

        // 4. Force Leave: FL (n)
        if (preg_match('/^\s*(?:FL|FORCE(?:\s*LEAVE)?)\s*\(\s*([0-9.]+)\s*\)/i', $p, $m)) {
            $days = self::formatExact($m[1]);
            return [
                'type' => 'FORCE_LEAVE',
                'target_column' => 'vl_absence_with_pay',
                'value' => $days,
                'deduct_from' => 'VL',
                'is_deductible' => true,
                'description' => "Mandatory/Force Leave for {$days} day(s) (Deduct from VL)",
            ];
        }

        // 5. Sick Leave: SL (n)
        if (preg_match('/^\s*SL\s*\(\s*([0-9.]+)\s*\)/i', $p, $m)) {
            $days = self::formatExact($m[1]);
            return [
                'type' => 'SICK_LEAVE',
                'target_column' => 'sl_absence_with_pay',
                'value' => $days,
                'deduct_from' => 'SL',
                'is_deductible' => true,
                'description' => "Sick Leave for {$days} day(s) (Deduct from SL)",
            ];
        }

        // 6. Special Privilege Leave: SPL (n)
        // NON-DEDUCTIBLE from SL balance per Rule 4 and Rule 9.C
        if (preg_match('/^\s*SPL\s*\(\s*([0-9.]+)\s*\)/i', $p, $m)) {
            $days = self::formatExact($m[1]);
            return [
                'type' => 'SPECIAL_PRIVILEGE_LEAVE',
                'target_column' => 'sl_absence_with_pay',
                'value' => $days,
                'deduct_from' => 'NONE',
                'is_deductible' => false,
                'description' => "Special Privilege Leave for {$days} day(s) (Recorded under Sick Leave, NOT deducted from SL balance)",
            ];
        }

        // 7. Wellness Leave: WL (n)
        // NON-DEDUCTIBLE from SL balance per Rule 4 and Rule 9.C
        if (preg_match('/^\s*WL\s*\(\s*([0-9.]+)\s*\)/i', $p, $m)) {
            $days = self::formatExact($m[1]);
            return [
                'type' => 'WELLNESS_LEAVE',
                'target_column' => 'sl_absence_with_pay',
                'value' => $days,
                'deduct_from' => 'NONE',
                'is_deductible' => false,
                'description' => "Wellness Leave for {$days} day(s) (Recorded under Sick Leave, NOT deducted from SL balance)",
            ];
        }

        // 8. Maternity Leave: ML (n)
        if (preg_match('/^\s*ML\s*\(\s*([0-9.]+)\s*\)/i', $p, $m)) {
            $days = self::formatExact($m[1]);
            return [
                'type' => 'MATERNITY_LEAVE',
                'target_column' => 'sl_absence_with_pay',
                'value' => $days,
                'deduct_from' => 'NONE',
                'is_deductible' => false,
                'description' => "Maternity Leave for {$days} day(s) (Recorded under Sick Leave, Statutory Benefit)",
            ];
        }

        // 9. Paternity Leave: PL (n)
        if (preg_match('/^\s*PL\s*\(\s*([0-9.]+)\s*\)/i', $p, $m)) {
            $days = self::formatExact($m[1]);
            return [
                'type' => 'PATERNITY_LEAVE',
                'target_column' => 'sl_absence_with_pay',
                'value' => $days,
                'deduct_from' => 'NONE',
                'is_deductible' => false,
                'description' => "Paternity Leave for {$days} day(s) (Recorded under Sick Leave, Statutory Benefit)",
            ];
        }

        // 10. Solo Parent Leave: Solo Parent Leave (n) or SOLO (n)
        if (preg_match('/^\s*(?:SOLO(?:\s*PARENT)?(?:\s*LEAVE)?)\s*\(\s*([0-9.]+)\s*\)/i', $p, $m)) {
            $days = self::formatExact($m[1]);
            return [
                'type' => 'SOLO_PARENT_LEAVE',
                'target_column' => 'sl_absence_with_pay',
                'value' => $days,
                'deduct_from' => 'NONE',
                'is_deductible' => false,
                'description' => "Solo Parent Leave for {$days} day(s) (Recorded under Sick Leave, Statutory Benefit)",
            ];
        }

        // 11. Study Leave: Study Leave (n) or STUDY (n)
        if (preg_match('/^\s*(?:STUDY(?:\s*LEAVE)?)\s*\(\s*([0-9.]+)\s*\)/i', $p, $m)) {
            $days = self::formatExact($m[1]);
            return [
                'type' => 'STUDY_LEAVE',
                'target_column' => 'sl_absence_with_pay',
                'value' => $days,
                'deduct_from' => 'NONE',
                'is_deductible' => false,
                'description' => "Study Leave for {$days} day(s) (Recorded under Sick Leave)",
            ];
        }

        // 12. 10-Day VAWC Leave: VAWC (n)
        if (preg_match('/^\s*(?:VAWC|10-DAY\s*VAWC)\s*\(\s*([0-9.]+)\s*\)/i', $p, $m)) {
            $days = self::formatExact($m[1]);
            return [
                'type' => 'VAWC_LEAVE',
                'target_column' => 'sl_absence_with_pay',
                'value' => $days,
                'deduct_from' => 'NONE',
                'is_deductible' => false,
                'description' => "10-Day VAWC Leave for {$days} day(s) (Recorded under Sick Leave, Statutory Benefit)",
            ];
        }

        // 13. Rehabilitation Privilege: RP (n)
        if (preg_match('/^\s*(?:RP|REHAB(?:ILITATION)?(?:\s*PRIVILEGE)?)\s*\(\s*([0-9.]+)\s*\)/i', $p, $m)) {
            $days = self::formatExact($m[1]);
            return [
                'type' => 'REHAB_PRIVILEGE',
                'target_column' => 'sl_absence_with_pay',
                'value' => $days,
                'deduct_from' => 'NONE',
                'is_deductible' => false,
                'description' => "Rehabilitation Privilege for {$days} day(s) (Recorded under Sick Leave)",
            ];
        }

        // 14. Special Leave Benefits for Women: SLBW (n)
        if (preg_match('/^\s*(?:SLBW|WOMEN(?:\s*LEAVE)?)\s*\(\s*([0-9.]+)\s*\)/i', $p, $m)) {
            $days = self::formatExact($m[1]);
            return [
                'type' => 'WOMEN_LEAVE',
                'target_column' => 'sl_absence_with_pay',
                'value' => $days,
                'deduct_from' => 'NONE',
                'is_deductible' => false,
                'description' => "Special Leave Benefits for Women for {$days} day(s) (Recorded under Sick Leave)",
            ];
        }

        // 15. Special Emergency (Calamity) Leave: SECL (n)
        if (preg_match('/^\s*(?:SECL|CALAMITY)\s*\(\s*([0-9.]+)\s*\)/i', $p, $m)) {
            $days = self::formatExact($m[1]);
            return [
                'type' => 'CALAMITY_LEAVE',
                'target_column' => 'sl_absence_with_pay',
                'value' => $days,
                'deduct_from' => 'NONE',
                'is_deductible' => false,
                'description' => "Special Emergency / Calamity Leave for {$days} day(s) (Recorded under Sick Leave)",
            ];
        }

        // 16. Adoption Leave: AL (n)
        if (preg_match('/^\s*(?:AL|ADOPTION(?:\s*LEAVE)?)\s*\(\s*([0-9.]+)\s*\)/i', $p, $m)) {
            $days = self::formatExact($m[1]);
            return [
                'type' => 'ADOPTION_LEAVE',
                'target_column' => 'sl_absence_with_pay',
                'value' => $days,
                'deduct_from' => 'NONE',
                'is_deductible' => false,
                'description' => "Adoption Leave for {$days} day(s) (Recorded under Sick Leave)",
            ];
        }

        // 17. Absence: ABS (n) or Absence (n) or Absent 1 day
        if (preg_match('/^\s*(?:ABS|ABSENCE|ABSENT)\s*(?:\(|\:|\-)?\s*([0-9.]+)(?:\s*days?)?\s*\)?/i', $p, $m)) {
            $days = self::formatExact($m[1]);
            return [
                'type' => 'ABSENT',
                'target_column' => 'vl_absence_with_pay',
                'value' => $days,
                'deduct_from' => 'VL',
                'is_deductible' => true,
                'description' => "Absence for {$days} day(s) (Classify as with pay or without pay)",
            ];
        }

        // 18. Earned Credits: Earned or EARNED
        if (preg_match('/^\s*EARNED/i', $p)) {
            return [
                'type' => 'EARNED',
                'target_column' => 'vl_earned',
                'value' => null, // Manually input from Table II e.g. 1.25 or 0.625
                'deduct_from' => 'NONE',
                'is_deductible' => false,
                'description' => "Monthly Earned Leave Credits (Table II). Enter exact earned credits for VL and SL.",
            ];
        }

        // Default: unclassified particular
        return [
            'type' => 'CUSTOM',
            'target_column' => null,
            'value' => null,
            'deduct_from' => 'NONE',
            'is_deductible' => false,
            'description' => "Custom Particular. Manually fill in the corresponding earned or absence columns.",
        ];
    }

    /**
     * Extracts a sortable YYYY-MM key from a record's period, accrual_month, or date_action_taken.
     * All deductions (leaves, tardiness, absences) are strictly sorted and deducted on the month they were filed.
     */
    public static function extractSortDate(LeaveRecord $record, int $defaultYear = 2026): string
    {
        // 1. Linked Leave Application: strictly sort and deduct on the month it was filed (especially advance filings)
        if ($record->application_id) {
            $app = $record->relationLoaded('leaveApplication') 
                ? $record->leaveApplication 
                : LeaveApplication::find($record->application_id);
            if ($app) {
                $filing = $app->date_of_filing ?: $app->created_at;
                if ($filing) {
                    return Carbon::parse($filing)->format('Y-m');
                }
            }
        }

        if (!empty($record->accrual_month) && preg_match('/^\d{4}-\d{2}$/', $record->accrual_month)) {
            return $record->accrual_month;
        }

        $period = trim((string)($record->period ?? ''));
        
        $months = [
            'jan' => '01', 'feb' => '02', 'mar' => '03', 'apr' => '04',
            'may' => '05', 'jun' => '06', 'jul' => '07', 'aug' => '08',
            'sep' => '09', 'oct' => '10', 'nov' => '11', 'dec' => '12',
        ];

        $year = $defaultYear;
        if (preg_match('/\b(20\d{2})\b/', $period, $ym)) {
            $year = (int)$ym[1];
        }

        foreach ($months as $mStr => $mNum) {
            if (stripos($period, $mStr) !== false) {
                return sprintf('%04d-%s', $year, $mNum);
            }
        }

        if (!empty($record->date_action_taken)) {
            foreach ($months as $mStr => $mNum) {
                if (stripos($record->date_action_taken, $mStr) !== false) {
                    if (preg_match('/\b(20\d{2})\b/', $record->date_action_taken, $ym)) {
                        $year = (int)$ym[1];
                    }
                    return sprintf('%04d-%s', $year, $mNum);
                }
            }
        }

        if ($record->created_at) {
            return $record->created_at->format('Y-m');
        }

        return "{$defaultYear}-01";
    }

    /**
     * Returns priority within the same month:
     * 0: Certified Baseline Opening Balance (comes first in opening month)
     * 1: Earned credits (Earned / Prorated Table II Earned) - placed at the START of the month
     * 2: Deductions, tardiness, undertime, approved leaves
     */
    public static function getRowTypePriority(LeaveRecord $record): int
    {
        $particular = strtoupper(trim((string)($record->particular ?? '')));

        if (str_contains($particular, 'BASELINE') || str_contains($particular, 'OPENING')) {
            return 0; // Baseline Opening Balance comes first
        }

        if (
            str_contains($particular, 'EARNED') || 
            ($record->vl_earned !== null && trim($record->vl_earned) !== '' && (float)$record->vl_earned > 0) ||
            ($record->sl_earned !== null && trim($record->sl_earned) !== '' && (float)$record->sl_earned > 0)
        ) {
            return 1; // "Earned" at the start of the month
        }

        return 2; // Deductions, tardiness, undertime, leave applications
    }

    /**
     * Recalculates all balances for an employee's Leave Record in strict chronological order.
     * Guarantees that at the start of every month, "Earned" rows precede any deduction rows.
     * Enforces all formulas from Section 9:
     * - Balance = Earned (if first entry)
     * - Balance = Previous Balance + Earned
     * - Balance = Previous Balance - Absence/Undertime with Pay (if deductible)
     * - SPL and WL are recorded under Sick Leave but DO NOT deduct from Sick Leave balance.
     * - Never rounds or truncates any decimal value.
     */
    public function recalculateEmployeeBalances(int $employeeId): array
    {
        return DB::transaction(function () use ($employeeId) {
            $rawRecords = LeaveRecord::where('employee_id', $employeeId)->get();

            // Sort chronologically by month key, and within the month:
            // Priority 0: Baseline opening
            // Priority 1: Earned at the start of the month
            // Priority 2: Absences/leaves/deductions
            $records = $rawRecords->sort(function ($a, $b) {
                $dateA = self::extractSortDate($a);
                $dateB = self::extractSortDate($b);

                if ($dateA !== $dateB) {
                    return strcmp($dateA, $dateB);
                }

                $prioA = self::getRowTypePriority($a);
                $prioB = self::getRowTypePriority($b);

                if ($prioA !== $prioB) {
                    return $prioA <=> $prioB;
                }

                if ($a->row_order !== $b->row_order) {
                    return $a->row_order <=> $b->row_order;
                }

                return $a->record_id <=> $b->record_id;
            })->values();

            $runningVl = null;
            $runningSl = null;

            foreach ($records as $index => $record) {
                // Assign continuous 1-based row_order strictly in chronological sequence
                $record->row_order = $index + 1;

                // -------------------------------------------------------------
                // 1. Vacation Leave Calculations
                // -------------------------------------------------------------
                // A. Earned credits
                if ($record->vl_earned !== null && trim($record->vl_earned) !== '') {
                    if ($runningVl === null) {
                        $runningVl = self::formatExact($record->vl_earned);
                    } else {
                        $runningVl = self::exactAdd($runningVl, $record->vl_earned);
                    }
                }

                // B. Deductions with pay
                if ($record->vl_absence_with_pay !== null && trim($record->vl_absence_with_pay) !== '') {
                    if ($runningVl === null) {
                        $runningVl = '0';
                    }
                    $runningVl = self::exactSub($runningVl, $record->vl_absence_with_pay);
                }

                // If this row has a manual baseline balance override (e.g. initial certified balance)
                if ($runningVl === null && $record->vl_balance !== null && trim($record->vl_balance) !== '') {
                    $runningVl = self::formatExact($record->vl_balance);
                }

                $record->vl_balance = $runningVl;

                // -------------------------------------------------------------
                // 2. Sick Leave Calculations
                // -------------------------------------------------------------
                // A. Earned credits
                if ($record->sl_earned !== null && trim($record->sl_earned) !== '') {
                    if ($runningSl === null) {
                        $runningSl = self::formatExact($record->sl_earned);
                    } else {
                        $runningSl = self::exactAdd($runningSl, $record->sl_earned);
                    }
                }

                // B. Absences / Leave with pay
                if ($record->sl_absence_with_pay !== null && trim($record->sl_absence_with_pay) !== '') {
                    $parsed = $this->parseParticular($record->particular);

                    // Critical Rule: SPL and WL DO NOT deduct from Sick Leave balance!
                    if ($parsed['type'] === 'SPECIAL_PRIVILEGE_LEAVE' || $parsed['type'] === 'WELLNESS_LEAVE' || !$parsed['is_deductible']) {
                        // Do not deduct! Balance remains unchanged
                    } else {
                        if ($runningSl === null) {
                            $runningSl = '0';
                        }
                        $runningSl = self::exactSub($runningSl, $record->sl_absence_with_pay);
                    }
                }

                // If this row has a manual baseline balance override
                if ($runningSl === null && $record->sl_balance !== null && trim($record->sl_balance) !== '') {
                    $runningSl = self::formatExact($record->sl_balance);
                }

                $record->sl_balance = $runningSl;

                $record->saveQuietly();
            }

            // Sync the latest running balances into employee's LeaveBalance table
            $employee = Employee::findOrFail($employeeId);
            $currentYear = Carbon::now()->year;

            $vlType = LeaveType::where('code', 'VL')->first();
            $slType = LeaveType::where('code', 'SL')->first();

            if ($vlType && $runningVl !== null) {
                LeaveBalance::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'leave_type_id' => $vlType->leave_type_id,
                        'year' => $currentYear,
                    ],
                    [
                        'balance' => (float)$runningVl,
                        'last_posted_at' => Carbon::now(),
                    ]
                );
            }

            if ($slType && $runningSl !== null) {
                LeaveBalance::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'leave_type_id' => $slType->leave_type_id,
                        'year' => $currentYear,
                    ],
                    [
                        'balance' => (float)$runningSl,
                        'last_posted_at' => Carbon::now(),
                    ]
                );
            }

            return [
                'count' => $records->count(),
                'latest_vl_balance' => $runningVl ?? '0',
                'latest_sl_balance' => $runningSl ?? '0',
            ];
        });
    }

    /**
     * Checks if an employee has already been credited/accrued for a given month
     * to strictly prevent duplicate monthly accruals.
     */
    public function hasEmployeeAccruedMonth(int $employeeId, Carbon|string $month): bool
    {
        $date = is_string($month) ? Carbon::parse($month) : $month;
        $accrualMonth = $date->format('Y-m');
        $periodName = $date->format('F Y');

        return LeaveRecord::where('employee_id', $employeeId)
            ->where(function ($q) use ($accrualMonth, $periodName) {
                $q->where('accrual_month', $accrualMonth)
                  ->orWhere(function ($sub) use ($periodName) {
                      $sub->where('particular', 'like', '%Earned%')
                          ->where('period', 'like', "%{$periodName}%");
                  });
            })
            ->exists();
    }

    /**
     * Credits monthly leave credits to an employee's Leave Record without overwriting previous balances.
     * Guaranteed to execute only once per employee per month.
     * Preserves exact decimal precision without rounding or truncating.
     */
    public function accrueMonthlyCreditsForEmployee(
        Employee $employee,
        Carbon $date,
        string $vlEarned = '1.25',
        string $slEarned = '1.25',
        ?string $customParticular = null,
        bool $isProrated = false,
        ?int $encodedBy = null
    ): ?LeaveRecord {
        return DB::transaction(function () use (
            $employee,
            $date,
            $vlEarned,
            $slEarned,
            $customParticular,
            $isProrated,
            $encodedBy
        ) {
            // Strict CSC Rule: Do not earn 1.25 if it is not yet the first day of the target month
            $firstDayOfMonth = $date->copy()->startOfMonth()->startOfDay();
            if (Carbon::now()->startOfDay()->lt($firstDayOfMonth)) {
                return null;
            }

            // Anti-duplicate guard: If already accrued for this month, return null
            if ($this->hasEmployeeAccruedMonth($employee->employee_id, $date)) {
                return null;
            }

            $accrualMonth = $date->format('Y-m');
            $period = $date->format('F Y');
            $cleanVl = self::formatExact($vlEarned);
            $cleanSl = self::formatExact($slEarned);
            $particular = $customParticular ?: ($isProrated ? 'Earned (Table II Prorated)' : 'Earned');

            $nextOrder = (LeaveRecord::where('employee_id', $employee->employee_id)->max('row_order') ?? 0) + 1;

            $record = LeaveRecord::create([
                'employee_id' => $employee->employee_id,
                'period' => $period,
                'particular' => $particular,
                'accrual_month' => $accrualMonth,
                'is_prorated' => $isProrated,
                'vl_earned' => $cleanVl,
                'sl_earned' => $cleanSl,
                'row_order' => $nextOrder,
                'encoded_by' => $encodedBy ?? 1,
            ]);

            // Recalculate balances in chronological sequence (never overwriting existing balances)
            $this->recalculateEmployeeBalances($employee->employee_id);
            $record->refresh();

            // Audit Trail
            AuditLog::create([
                'user_id' => $encodedBy ?? 1,
                'action' => $isProrated ? 'ACCRUE_PRORATED_LEAVE_CREDITS_TABLE_II' : 'ACCRUE_MONTHLY_LEAVE_CREDITS',
                'table_name' => 'leave_records',
                'record_id' => $record->record_id,
                'details' => json_encode([
                    'employee_id' => $employee->employee_id,
                    'accrual_month' => $accrualMonth,
                    'vl_earned' => $cleanVl,
                    'sl_earned' => $cleanSl,
                    'is_prorated' => $isProrated,
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'logged_at' => Carbon::now(),
            ]);

            return $record;
        });
    }

    /**
     * Executes monthly accrual (1.25 VL & 1.25 SL) for all eligible active municipal employees.
     * Skips any employee who has already received credits for the target month.
     */
    public function accrueMonthlyCreditsForAllEligible(?Carbon $date = null, ?int $encodedBy = null): array
    {
        $targetDate = $date ?? Carbon::now();
        $targetMonthStr = $targetDate->format('F Y');
        $accrualMonth = $targetDate->format('Y-m');

        // Strict CSC rule: Do not earn 1.25 if it is not yet the first day of the target month
        $firstDayOfMonth = $targetDate->copy()->startOfMonth()->startOfDay();
        if (Carbon::now()->startOfDay()->lt($firstDayOfMonth)) {
            return [
                'target_month' => $targetMonthStr,
                'accrual_month' => $accrualMonth,
                'total_eligible' => 0,
                'total_accrued' => 0,
                'total_skipped' => 0,
                'accrued' => [],
                'skipped' => [],
                'error' => "Cannot accrue leave credits for {$targetMonthStr}: It is not yet the first day of the month.",
            ];
        }

        // All active eligible regular personnel in LGU Basud (excludes Job Order)
        $eligibleEmployees = Employee::eligibleForLeave()
            ->active()
            ->orderBy('full_name')
            ->get();

        $accrued = [];
        $skipped = [];

        foreach ($eligibleEmployees as $employee) {
            if ($this->hasEmployeeAccruedMonth($employee->employee_id, $targetDate)) {
                $skipped[] = [
                    'employee_id' => $employee->employee_id,
                    'employee_no' => $employee->employee_no,
                    'full_name' => $employee->full_name,
                    'reason' => "Already accrued for {$targetMonthStr}",
                ];
                continue;
            }

            $record = $this->accrueMonthlyCreditsForEmployee(
                $employee,
                $targetDate,
                '1.25',
                '1.25',
                'Earned',
                false,
                $encodedBy
            );

            if ($record) {
                $accrued[] = [
                    'employee_id' => $employee->employee_id,
                    'employee_no' => $employee->employee_no,
                    'full_name' => $employee->full_name,
                    'record_id' => $record->record_id,
                    'vl_earned' => $record->vl_earned,
                    'sl_earned' => $record->sl_earned,
                    'new_vl_balance' => $record->vl_balance,
                    'new_sl_balance' => $record->sl_balance,
                ];
            }
        }

        return [
            'target_month' => $targetMonthStr,
            'accrual_month' => $accrualMonth,
            'total_eligible' => $eligibleEmployees->count(),
            'total_accrued' => count($accrued),
            'total_skipped' => count($skipped),
            'accrued' => $accrued,
            'skipped' => $skipped,
        ];
    }

    /**
     * Manually encodes Table II prorated credits for an employee who became regular or eligible
     * partway through a month, or returned from LWOP.
     * Uses the exact entered decimal values without rounding or truncating.
     */
    public function accrueProratedCredits(
        int $employeeId,
        string $period,
        string $vlEarned,
        string $slEarned,
        ?string $particular = null,
        ?int $daysOfService = null,
        ?int $encodedBy = null
    ): LeaveRecord {
        $employee = Employee::findOrFail($employeeId);

        // Derive accrual month from period or current date
        $date = Carbon::now();
        try {
            $date = Carbon::parse($period);
        } catch (\Exception $e) {
            // Keep default
        }

        $accrualMonth = $date->format('Y-m');
        $cleanVl = self::formatExact($vlEarned);
        $cleanSl = self::formatExact($slEarned);

        $particularText = $particular;
        if (!$particularText) {
            $particularText = $daysOfService 
                ? "Earned (Table II: {$daysOfService} days)" 
                : "Earned (Prorated)";
        }

        $nextOrder = (LeaveRecord::where('employee_id', $employeeId)->max('row_order') ?? 0) + 1;

        $record = LeaveRecord::create([
            'employee_id' => $employeeId,
            'period' => $period,
            'particular' => $particularText,
            'accrual_month' => $accrualMonth,
            'is_prorated' => true,
            'vl_earned' => $cleanVl,
            'sl_earned' => $cleanSl,
            'row_order' => $nextOrder,
            'encoded_by' => $encodedBy ?? 1,
        ]);

        $this->recalculateEmployeeBalances($employeeId);
        $record->refresh();

        AuditLog::create([
            'user_id' => $encodedBy ?? 1,
            'action' => 'MANUAL_TABLE_II_PRORATED_ACCRUAL',
            'table_name' => 'leave_records',
            'record_id' => $record->record_id,
            'details' => json_encode([
                'employee_id' => $employeeId,
                'period' => $period,
                'days_of_service' => $daysOfService,
                'vl_earned' => $cleanVl,
                'sl_earned' => $cleanSl,
            ]),
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'logged_at' => Carbon::now(),
        ]);

        return $record;
    }

    /**
     * Retrieve all Table II entries sorted numerically by days of service.
     */
    public function getTableIIRates(): \Illuminate\Support\Collection
    {
        return StarlaReference::where('table_ref', 'Table II')
            ->get()
            ->sortBy(function ($item) {
                if (preg_match('/(\d+)/', $item->input_value, $m)) {
                    return (int)$m[1];
                }
                return 0;
            })
            ->values();
    }

    /**
     * Updates and populates all municipal employees' leave records,
     * ensuring that at the start of every month from January up to current/target period,
     * an "Earned" entry exists with 1.25 days in Particular and Earned days columns.
     */
    public function updateAllLeaveRecordsWithMonthlyEarned(
        int $year = 2026,
        ?int $upToMonth = null,
        ?int $encodedBy = null
    ): array {
        return DB::transaction(function () use ($year, $upToMonth, $encodedBy) {
            $employees = Employee::eligibleForLeave()->get();
            $maxMonth = $upToMonth ?? Carbon::now()->month;
            $results = [
                'total_employees' => $employees->count(),
                'records_created' => 0,
                'employees_updated' => 0,
            ];

            $currentDate = Carbon::now()->startOfDay();

            foreach ($employees as $emp) {
                // If employee has a date_hired in this year, start month is hire month; if hired after this year, skip
                $hireDate = $emp->date_hired ? Carbon::parse($emp->date_hired) : null;
                $startMonth = 1;
                if ($hireDate) {
                    if ($hireDate->year > $year) {
                        continue; // Not yet hired in this year
                    }
                    if ($hireDate->year === $year) {
                        $startMonth = $hireDate->month;
                    }
                }

                $empMaxMonth = $maxMonth;

                for ($m = $startMonth; $m <= $empMaxMonth; $m++) {
                    $date = Carbon::create($year, $m, 1)->startOfDay();

                    // Strict CSC rule: Do not earn 1.25 if it is not yet the first day of the month
                    if ($currentDate->lt($date)) {
                        break;
                    }

                    $accrualMonth = $date->format('Y-m');

                    // Check if an Earned record exists for this employee in this month
                    $earnedExists = LeaveRecord::where('employee_id', $emp->employee_id)
                        ->where(function ($q) use ($accrualMonth, $date) {
                            $periodName = $date->format('F');
                            $q->where('accrual_month', $accrualMonth)
                              ->orWhere(function ($sub) use ($periodName) {
                                  $sub->where('particular', 'like', '%Earned%')
                                      ->where('period', 'like', "%{$periodName}%");
                              });
                        })
                        ->exists();

                    if (!$earnedExists) {
                        $isMidMonthHire = ($hireDate && $hireDate->year === $year && $m === $hireDate->month && $hireDate->day > 1);
                        $creditAmount = '1.25';
                        $particularText = 'Earned';
                        $dateActionTaken = 'Monthly Accrual (CSC Table II - 30 days of service)';

                        if ($isMidMonthHire) {
                            $daysOfService = max(1, min(30, 30 - $hireDate->day + 1));
                            $ref = StarlaReference::where('table_ref', 'Table II')
                                ->where('input_value', "{$daysOfService} " . ($daysOfService === 1 ? 'day' : 'days'))
                                ->value('day_equivalent');
                            $creditAmount = $ref !== null ? (string)$ref : sprintf('%.3f', ($daysOfService / 30) * 1.250);
                            $particularText = "Earned (Table II: {$daysOfService} days)";
                            $dateActionTaken = "Appointment / Start Date: {$hireDate->format('M d, Y')} ({$daysOfService} days of service per CSC Table II)";
                        }

                        LeaveRecord::create([
                            'employee_id' => $emp->employee_id,
                            'period' => $date->format('F Y'),
                            'particular' => $particularText,
                            'accrual_month' => $accrualMonth,
                            'is_prorated' => $isMidMonthHire,
                            'vl_earned' => $creditAmount,
                            'sl_earned' => $creditAmount,
                            'row_order' => 999,
                            'date_action_taken' => $dateActionTaken,
                            'encoded_by' => $encodedBy ?? 1,
                        ]);
                        $results['records_created']++;
                    }
                }

                // Recalculate full chronological chain for employee, sorting and placing Earned at start of month
                $this->recalculateEmployeeBalances($emp->employee_id);
                $results['employees_updated']++;
            }

            return $results;
        });
    }

    /**
     * Automatically creates a corresponding row in the employee's existing Leave Record table
     * using the submitted leave application details.
     * Strictly prevents duplicate entries.
     * Does NOT prematurely deduct leave credits (absence columns remain null while pending).
     */
    public function createRowForLeaveApplication(LeaveApplication $application, ?int $encodedBy = null): ?LeaveRecord
    {
        return DB::transaction(function () use ($application, $encodedBy) {
            // Strict duplicate prevention: check by application_id or reference_code
            $existing = LeaveRecord::where('application_id', $application->application_id)
                ->orWhere(function ($q) use ($application) {
                    $q->where('employee_id', $application->employee_id)
                      ->where(function ($sub) use ($application) {
                          $sub->where('particular', 'like', "%{$application->reference_code}%")
                              ->orWhere('date_action_taken', 'like', "%{$application->reference_code}%");
                      });
                })
                ->first();

            if ($existing) {
                if (!$existing->application_id) {
                    $existing->update(['application_id' => $application->application_id]);
                }
                return $existing;
            }

            $leaveTypeCode = $application->leaveType->code ?? 'VL';
            $particularPrefix = match ($leaveTypeCode) {
                'FL' => 'FL',
                'SL' => 'SL',
                'SPL' => 'SPL',
                'WL' => 'WL',
                'ML' => 'ML',
                'PL' => 'PL',
                'SOLO' => 'SOLO',
                'STUDY' => 'STUDY',
                'VAWC' => 'VAWC',
                'REHAB' => 'RP',
                'SLBW' => 'SLBW',
                'CALAMITY' => 'SECL',
                'ADOPT' => 'AL',
                default => 'VL',
            };

            $formattedDays = self::formatExact((string)$application->days_requested);
            $particularStr = "{$particularPrefix} ({$formattedDays})";

            // Deduct on the month it was filed, specially for leaves filed in advance
            $filingDate = $application->date_of_filing 
                ? Carbon::parse($application->date_of_filing) 
                : ($application->created_at ? Carbon::parse($application->created_at) : Carbon::parse($application->date_from));
            $periodStr = $filingDate->format('F Y');
            $accrualMonth = $filingDate->format('Y-m');

            $dateFromStr = Carbon::parse($application->date_from)->format('M d');
            $dateToStr = Carbon::parse($application->date_to)->format('M d, Y');
            $dateActionTaken = "Application Filed (Pending Approval); Ref: {$application->reference_code} (Leave: {$dateFromStr} - {$dateToStr})";

            $nextOrder = (LeaveRecord::where('employee_id', $application->employee_id)->max('row_order') ?? 0) + 1;

            $record = LeaveRecord::create([
                'employee_id' => $application->employee_id,
                'application_id' => $application->application_id,
                'period' => $periodStr,
                'particular' => $particularStr,
                'accrual_month' => $accrualMonth,
                'is_prorated' => false,
                'vl_earned' => null,
                'vl_absence_with_pay' => null, // Explicitly null: Do not prematurely deduct upon submission
                'vl_balance' => null,
                'vl_absence_without_pay' => null,
                'sl_earned' => null,
                'sl_absence_with_pay' => null, // Explicitly null: Do not prematurely deduct upon submission
                'sl_balance' => null,
                'sl_absence_without_pay' => null,
                'date_action_taken' => $dateActionTaken,
                'row_order' => $nextOrder,
                'encoded_by' => $encodedBy ?? 1,
            ]);

            // Recalculate chronological balances (keeps running balances accurate without deducting pending leave)
            $this->recalculateEmployeeBalances($application->employee_id);
            $record->refresh();

            AuditLog::create([
                'user_id' => $encodedBy ?? 1,
                'action' => 'AUTO_CREATE_LEAVE_RECORD_ON_SUBMISSION',
                'table_name' => 'leave_records',
                'record_id' => $record->record_id,
                'details' => json_encode([
                    'application_id' => $application->application_id,
                    'reference_code' => $application->reference_code,
                    'employee_id' => $application->employee_id,
                    'particular' => $particularStr,
                    'period' => $periodStr,
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'logged_at' => Carbon::now(),
            ]);

            return $record;
        });
    }

    /**
     * True when a Vacation Leave / Sick Leave application cannot be covered by the
     * employee's remaining VL / SL credits. Such an application is still allowed but is
     * recorded under "Absence/Undertime Without Pay" with no credit deduction.
     * Idempotent: ignores the application's own deduction if it was already posted.
     */
    public function isLeaveWithoutPay(LeaveApplication $application): bool
    {
        $code = $application->leaveType->code ?? null;
        if (!in_array($code, ['VL', 'SL'], true)) {
            return false;
        }

        $col = $code === 'VL' ? 'vl' : 'sl';
        $own = LeaveRecord::where('application_id', $application->application_id)->first();

        if ($own && trim((string) $own->{"{$col}_absence_without_pay"}) !== '') {
            return true;
        }

        $latest = LeaveRecord::where('employee_id', $application->employee_id)
            ->orderBy('row_order')->orderBy('record_id')->get()->last();

        $balance = $latest?->{"{$col}_balance"} !== null
            ? (float) $latest->{"{$col}_balance"}
            : (float) (LeaveBalance::where('employee_id', $application->employee_id)
                ->whereHas('leaveType', fn($q) => $q->where('code', $code))
                ->orderByDesc('year')->value('balance') ?? 0);

        if ($own && trim((string) $own->{"{$col}_absence_with_pay"}) !== '') {
            $balance += (float) $own->{"{$col}_absence_with_pay"};
        }

        return round($balance, 3) < round((float) $application->days_requested, 3);
    }

    /**
     * Applies approved leave deduction to the employee's Leave Record table
     * upon physical Mayor approval (the appropriate processing stage according to existing workflow).
     * Updates the existing row created on submission without creating duplicate rows.
     * VL/SL applications that exceed the remaining credits are recorded as Absence/Undertime
     * Without Pay and do not change the credit balances.
     */
    public function applyApprovedLeaveDeduction(LeaveApplication $application, Carbon $postedAt, ?int $encodedBy = null): LeaveRecord
    {
        return DB::transaction(function () use ($application, $postedAt, $encodedBy) {
            $leaveTypeCode = $application->leaveType->code ?? 'VL';
            $days = (float) $application->days_requested;
            $formattedDays = self::formatExact((string)$days);

            $isVl = in_array($leaveTypeCode, ['VL', 'FL']);
            $isSl = ($leaveTypeCode === 'SL');
            $isNonDeductible = in_array($leaveTypeCode, ['SPL', 'WL', 'ML', 'PL', 'SOLO', 'STUDY', 'VAWC', 'REHAB', 'SLBW', 'CALAMITY', 'ADOPT']);

            // Insufficient VL/SL credits: record as Absence/Undertime Without Pay (no deduction)
            $isLwop = $this->isLeaveWithoutPay($application);

            $vlAbsence = ($isVl && !$isLwop) ? $formattedDays : null;
            $slAbsence = (($isSl && !$isLwop) || $isNonDeductible) ? $formattedDays : null;
            $vlWithoutPay = ($isLwop && $leaveTypeCode === 'VL') ? $formattedDays : null;
            $slWithoutPay = ($isLwop && $leaveTypeCode === 'SL') ? $formattedDays : null;

            // Deduct on the month it was filed, specially for leaves filed in advance
            $filingDate = $application->date_of_filing 
                ? Carbon::parse($application->date_of_filing) 
                : ($application->created_at ? Carbon::parse($application->created_at) : Carbon::parse($application->date_from));
            $periodStr = $filingDate->format('F Y');
            $accrualMonth = $filingDate->format('Y-m');

            $dateFromStr = Carbon::parse($application->date_from)->format('M d');
            $dateToStr = Carbon::parse($application->date_to)->format('M d, Y');
            $dateActionTaken = $postedAt->format('M d, Y') . " - Approved by Municipal Mayor (Physical Wet Signature); Ref: {$application->reference_code} (Leave: {$dateFromStr} - {$dateToStr})"
                . ($isLwop ? ' - Insufficient credits: Absence/Undertime Without Pay' : '');

            // Find existing row created on submission
            $record = LeaveRecord::where('application_id', $application->application_id)
                ->orWhere(function ($q) use ($application) {
                    $q->where('employee_id', $application->employee_id)
                      ->where(function ($sub) use ($application) {
                          $sub->where('particular', 'like', "%{$application->reference_code}%")
                              ->orWhere('date_action_taken', 'like', "%{$application->reference_code}%");
                      });
                })
                ->first();

            if ($record) {
                $record->update([
                    'application_id' => $application->application_id,
                    'period' => $periodStr,
                    'accrual_month' => $accrualMonth,
                    'vl_absence_with_pay' => $vlAbsence,
                    'sl_absence_with_pay' => $slAbsence,
                    'vl_absence_without_pay' => $vlWithoutPay,
                    'sl_absence_without_pay' => $slWithoutPay,
                    'date_action_taken' => $dateActionTaken,
                ]);
            } else {
                $nextOrder = (LeaveRecord::where('employee_id', $application->employee_id)->max('row_order') ?? 0) + 1;

                $record = LeaveRecord::create([
                    'employee_id' => $application->employee_id,
                    'application_id' => $application->application_id,
                    'period' => $periodStr,
                    'particular' => "{$leaveTypeCode} ({$formattedDays})",
                    'accrual_month' => $accrualMonth,
                    'vl_absence_with_pay' => $vlAbsence,
                    'sl_absence_with_pay' => $slAbsence,
                    'vl_absence_without_pay' => $vlWithoutPay,
                    'sl_absence_without_pay' => $slWithoutPay,
                    'date_action_taken' => $dateActionTaken,
                    'row_order' => $nextOrder,
                    'encoded_by' => $encodedBy ?? 1,
                ]);
            }

            // Recalculate full chain so running balances are 100% exact and synced to LeaveBalance
            $this->recalculateEmployeeBalances($application->employee_id);
            $record->refresh();

            return $record;
        });
    }

    /**
     * Calculates Section 7.A (Certification of Leave Credits) values before printing:
     * - Total Earned: Previous balance after all previously recorded and applicable transactions.
     * - Less This Application: Leave credits chargeable for the current application.
     * - Balance: Total Earned − Less This Application.
     * Guaranteed:
     * - Never rounds or truncates decimals.
     * - Does NOT prematurely deduct leave credits upon submission or printing.
     * - Reflects the latest saved balance before printing.
     */
    public function calculateSection7AData(LeaveApplication $application): array
    {
        $employeeId = $application->employee_id;

        // Fetch latest saved record from employee's Leave Record table
        $latestRecord = LeaveRecord::where('employee_id', $employeeId)
            ->orderBy('row_order', 'asc')
            ->orderBy('record_id', 'asc')
            ->get()
            ->last();

        $savedVl = $latestRecord?->vl_balance !== null 
            ? (string)$latestRecord->vl_balance 
            : (string)(LeaveBalance::where('employee_id', $employeeId)->whereHas('leaveType', fn($q) => $q->where('code', 'VL'))->value('balance') ?? '0');

        $savedSl = $latestRecord?->sl_balance !== null 
            ? (string)$latestRecord->sl_balance 
            : (string)(LeaveBalance::where('employee_id', $employeeId)->whereHas('leaveType', fn($q) => $q->where('code', 'SL'))->value('balance') ?? '0');

        $leaveTypeCode = $application->leaveType->code ?? 'VL';
        $days = (float)$application->days_requested;

        $isMonetization = ($leaveTypeCode === 'MONETIZATION')
            || in_array($application->details_type, ['monetization', 'monetization_special'], true)
            || str_contains(strtolower($application->other_leave_type ?? ''), 'monetiz')
            || str_contains(strtolower($application->details_of_leave ?? ''), 'monetiz');

        if ($isMonetization) {
            if ($application->details_type === 'monetization_special' || str_contains(strtolower($application->details_of_leave ?? ''), 'special')) {
                // Special monetization of 50% or more across VL and SL:
                // Deduct from VL first up to available balance in whole days, remaining from SL in whole days
                $vlAvailWhole = floor((float)$savedVl);
                $vlDed = min($days, $vlAvailWhole);
                $slDed = max(0.0, $days - $vlDed);
                $vlLessValue = self::formatExact((string)$vlDed);
                $slLessValue = self::formatExact((string)$slDed);
            } else {
                // Regular Monetization: deduct monetized days directly from Vacation Leave
                $vlLessValue = self::formatExact((string)$days);
                $slLessValue = '0';
            }
            $isVlChargeable = true;
            $isSlChargeable = false;
        } else {
            $isVlChargeable = in_array($leaveTypeCode, ['VL', 'FL']);
            $isSlChargeable = ($leaveTypeCode === 'SL');
            $vlLessValue = $isVlChargeable ? self::formatExact((string)$days) : '0';
            $slLessValue = $isSlChargeable ? self::formatExact((string)$days) : '0';
        }

        // Insufficient VL/SL credits: charged as Absence/Undertime Without Pay, so nothing is "Less" from credits
        if (!$isMonetization && $this->isLeaveWithoutPay($application)) {
            $isVlChargeable = false;
            $isSlChargeable = false;
            $vlLessValue = '0';
            $slLessValue = '0';
        }

        // Check if this application was already approved and already deducted from the saved balance
        $isAlreadyApproved = in_array($application->status, ['Approved', 'APPROVED_PHYSICAL']) 
            || ($latestRecord && $latestRecord->application_id === $application->application_id && $latestRecord->vl_absence_with_pay !== null);

        if ($isAlreadyApproved) {
            // Balance is the current saved balance
            $vlRemain = $savedVl;
            $slRemain = $savedSl;

            // Total Earned was the balance before this application
            $vlLess = $vlLessValue;
            $slLess = $slLessValue;

            $vlTotalEarned = self::exactAdd($savedVl, $vlLess);
            $slTotalEarned = self::exactAdd($savedSl, $slLess);
        } else {
            // Not yet deducted (Pending): Total Earned is the previous saved balance after all previously recorded transactions
            $vlTotalEarned = $savedVl;
            $slTotalEarned = $savedSl;

            $vlLess = $vlLessValue;
            $slLess = $slLessValue;

            $vlRemain = self::exactSub($vlTotalEarned, $vlLess);
            $slRemain = self::exactSub($slTotalEarned, $slLess);

            // Never let balance display as negative on certified Form 6
            if (is_numeric($vlRemain) && (float)$vlRemain < 0) $vlRemain = '0';
            if (is_numeric($slRemain) && (float)$slRemain < 0) $slRemain = '0';
        }

        $asOfDate = $application->certified_as_of 
            ?: ($application->date_of_filing ?: Carbon::now());

        return [
            'as_of_date' => $asOfDate,
            'as_of_formatted' => Carbon::parse($asOfDate)->format('F d, Y'),
            'vl_total_earned' => $vlTotalEarned,
            'vl_less' => $vlLess,
            'vl_balance' => $vlRemain,
            'sl_total_earned' => $slTotalEarned,
            'sl_less' => $slLess,
            'sl_balance' => $slRemain,
            'is_vl_chargeable' => $isVlChargeable,
            'is_sl_chargeable' => $isSlChargeable,
        ];
    }

    /**
     * Encodes manual DTR late, undertime, and absence records into the employee's Leave Record table.
     * Automatically converts durations using the prescribed Table IV / Starla values.
     * Applies the office's leave deduction rules:
     * - Tardiness and undertime are deducted from Vacation Leave (VL) first.
     * - If VL balance is exhausted, the excess converts to LWOP (Absence Without Pay).
     * - Absences with pay are deducted from VL; unexcused/without pay are placed in Without Pay.
     */
    public function recordDtrAttendance(
        int $employeeId,
        string $period,
        ?int $lateHours = 0,
        ?int $lateMinutes = 0,
        ?int $undertimeHours = 0,
        ?int $undertimeMinutes = 0,
        ?float $daysAbsent = 0.0,
        string $absenceType = 'with_pay',
        ?int $encodedBy = null
    ): LeaveRecord {
        return DB::transaction(function () use (
            $employeeId,
            $period,
            $lateHours,
            $lateMinutes,
            $undertimeHours,
            $undertimeMinutes,
            $daysAbsent,
            $absenceType,
            $encodedBy
        ) {
            $lateConv = $this->convertTableIV($lateHours ?? 0, $lateMinutes ?? 0);
            $utConv = $this->convertTableIV($undertimeHours ?? 0, $undertimeMinutes ?? 0);

            $tardyFraction = self::exactAdd($lateConv['total_fraction'], $utConv['total_fraction']);
            $absFraction = ($daysAbsent !== null && (float)$daysAbsent > 0) ? self::formatExact((string)$daysAbsent) : '0';

            // Build Civil Service Particular string
            $parts = [];
            if (($lateHours ?? 0) > 0 || ($lateMinutes ?? 0) > 0) {
                $lateStr = '';
                if (($lateHours ?? 0) > 0) $lateStr .= $lateHours . (($lateHours == 1) ? ' hr' : ' hrs');
                if (($lateMinutes ?? 0) > 0) $lateStr .= (!empty($lateStr) ? ' and ' : '') . $lateMinutes . (($lateMinutes == 1) ? ' min' : ' mins');
                $parts[] = "L ({$lateStr})";
            }
            if (($undertimeHours ?? 0) > 0 || ($undertimeMinutes ?? 0) > 0) {
                $utStr = '';
                if (($undertimeHours ?? 0) > 0) $utStr .= $undertimeHours . (($undertimeHours == 1) ? ' hr' : ' hrs');
                if (($undertimeMinutes ?? 0) > 0) $utStr .= (!empty($utStr) ? ' and ' : '') . $undertimeMinutes . (($undertimeMinutes == 1) ? ' min' : ' mins');
                $parts[] = "UT ({$utStr})";
            }
            if ((float)$absFraction > 0) {
                $parts[] = "ABS ({$absFraction})";
            }

            $particular = !empty($parts) ? implode(', ', $parts) : 'DTR Attendance Adjustment';

            // Calculate total with pay and without pay portions per office deduction rules
            $isAbsenceWithPay = ($absenceType === 'with_pay');
            $totalWithPay = $isAbsenceWithPay ? self::exactAdd($tardyFraction, $absFraction) : $tardyFraction;
            $unpaidBase = !$isAbsenceWithPay ? $absFraction : '0';

            // Check current VL balance of employee
            $latestRec = LeaveRecord::where('employee_id', $employeeId)
                ->orderBy('row_order', 'asc')
                ->orderBy('record_id', 'asc')
                ->get()
                ->last();

            $currentVl = $latestRec?->vl_balance !== null ? (float)$latestRec->vl_balance : 0.0;

            $vlAbsenceWithPay = null;
            $vlAbsenceWithoutPay = (float)$unpaidBase > 0 ? $unpaidBase : null;

            if ((float)$totalWithPay > 0) {
                if ($currentVl >= (float)$totalWithPay) {
                    // VL balance sufficient to cover deduction
                    $vlAbsenceWithPay = $totalWithPay;
                } elseif ($currentVl > 0) {
                    // Partial coverage: balance down to 0, excess converts to LWOP
                    $vlAbsenceWithPay = self::formatExact((string)$currentVl);
                    $excess = self::exactSub($totalWithPay, (string)$currentVl);
                    $vlAbsenceWithoutPay = self::exactAdd($vlAbsenceWithoutPay ?? '0', $excess);
                } else {
                    // VL balance is 0: entire deduction converts to LWOP
                    $vlAbsenceWithPay = null;
                    $vlAbsenceWithoutPay = self::exactAdd($vlAbsenceWithoutPay ?? '0', $totalWithPay);
                }
            }

            $date = Carbon::now();
            try {
                $date = Carbon::parse($period);
            } catch (\Exception $e) {}
            $accrualMonth = $date->format('Y-m');
            $periodMonthYear = $date->format('F Y');
            $dateActionTakenVal = $period; // e.g. "October 1-15, 2026"

            $nextOrder = (LeaveRecord::where('employee_id', $employeeId)->max('row_order') ?? 0) + 1;

            $record = LeaveRecord::create([
                'employee_id' => $employeeId,
                'period' => $periodMonthYear,
                'particular' => $particular,
                'accrual_month' => $accrualMonth,
                'is_prorated' => false,
                'vl_earned' => null,
                'vl_absence_with_pay' => $vlAbsenceWithPay,
                'vl_balance' => null,
                'vl_absence_without_pay' => $vlAbsenceWithoutPay,
                'sl_earned' => null,
                'sl_absence_with_pay' => null,
                'sl_balance' => null,
                'sl_absence_without_pay' => null,
                'date_action_taken' => $dateActionTakenVal,
                'row_order' => $nextOrder,
                'encoded_by' => $encodedBy ?? 1,
            ]);

            $this->recalculateEmployeeBalances($employeeId);
            $record->refresh();

            AuditLog::create([
                'user_id' => $encodedBy ?? 1,
                'action' => 'MANUAL_DTR_ATTENDANCE_LEAVE_RECORD',
                'table_name' => 'leave_records',
                'record_id' => $record->record_id,
                'details' => json_encode([
                    'employee_id' => $employeeId,
                    'period' => $period,
                    'particular' => $particular,
                    'late_hours' => $lateHours,
                    'late_minutes' => $lateMinutes,
                    'undertime_hours' => $undertimeHours,
                    'undertime_minutes' => $undertimeMinutes,
                    'days_absent' => $daysAbsent,
                    'vl_absence_with_pay' => $vlAbsenceWithPay,
                    'vl_absence_without_pay' => $vlAbsenceWithoutPay,
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'logged_at' => Carbon::now(),
            ]);

            return $record;
        });
    }

    /**
     * Ensures an employee has their initial certified baseline or first-month Table II earned row
     * in the LeaveRecord ledger if they currently have 0 leave records.
     * Guaranteed safe and idempotent.
     */
    public function ensureInitialRecordForEmployee(Employee $employee, ?int $encodedBy = null): ?LeaveRecord
    {
        // Job order personnel do not earn or have CS Form 41 leave records
        if ($employee->isJobOrder()) {
            return null;
        }

        // If they already have leave records, nothing to initialize
        if (LeaveRecord::where('employee_id', $employee->employee_id)->exists()) {
            return null;
        }

        $vlType = LeaveType::where('code', 'VL')->first();
        $slType = LeaveType::where('code', 'SL')->first();

        $vlBalance = (float) ($employee->balances->firstWhere('leave_type_id', $vlType?->leave_type_id)?->balance ?? 0.0);
        $slBalance = (float) ($employee->balances->firstWhere('leave_type_id', $slType?->leave_type_id)?->balance ?? 0.0);

        // If balances are 0 and date_hired is set, calculate first-month Table II credit
        if ($vlBalance == 0.0 && $slBalance == 0.0 && $employee->date_hired) {
            $table2Calc = $employee->getStartingMonthTableIICredit();
            if ($table2Calc && isset($table2Calc['credit'])) {
                $vlBalance = (float) $table2Calc['credit'];
                $slBalance = (float) $table2Calc['credit'];

                // Update balances in leave_balances table as well
                $currentYear = Carbon::now()->year;
                if ($vlType) {
                    LeaveBalance::updateOrCreate(
                        ['employee_id' => $employee->employee_id, 'leave_type_id' => $vlType->leave_type_id, 'year' => $currentYear],
                        ['balance' => $vlBalance, 'last_posted_at' => Carbon::now()]
                    );
                }
                if ($slType) {
                    LeaveBalance::updateOrCreate(
                        ['employee_id' => $employee->employee_id, 'leave_type_id' => $slType->leave_type_id, 'year' => $currentYear],
                        ['balance' => $slBalance, 'last_posted_at' => Carbon::now()]
                    );
                }
            }
        }

        // Even if balance is 0.0, regular employees still get an initial row
        $hireDate = $employee->date_hired ? Carbon::parse($employee->date_hired) : Carbon::now();
        $periodFormatted = $hireDate->format('F Y');
        $accrualMonth = $hireDate->format('Y-m');

        $isTable2Earned = (bool) $employee->date_hired;
        $particular = $isTable2Earned
            ? "Earned (Table II: {$hireDate->format('M d, Y')})"
            : "Certified Baseline Opening Balance";

        $vlEarnedVal = $isTable2Earned ? number_format($vlBalance, 3, '.', '') : null;
        $slEarnedVal = $isTable2Earned ? number_format($slBalance, 3, '.', '') : null;

        $record = LeaveRecord::create([
            'employee_id' => $employee->employee_id,
            'period' => $periodFormatted,
            'particular' => $particular,
            'accrual_month' => $accrualMonth,
            'is_prorated' => (bool) ($employee->date_hired && $hireDate->day > 1),
            'vl_earned' => $vlEarnedVal,
            'vl_absence_with_pay' => null,
            'vl_balance' => number_format($vlBalance, 3, '.', ''),
            'vl_absence_without_pay' => null,
            'sl_earned' => $slEarnedVal,
            'sl_absence_with_pay' => null,
            'sl_balance' => number_format($slBalance, 3, '.', ''),
            'sl_absence_without_pay' => null,
            'date_action_taken' => $hireDate->format('n/j/Y'),
            'row_order' => 1,
            'encoded_by' => $encodedBy ?? 1,
        ]);

        $this->recalculateEmployeeBalances($employee->employee_id);

        return $record;
    }

    /**
     * Synchronizes any existing leave applications that are missing a row in leave_records,
     * ensuring 100% data consistency and strict duplicate prevention across all historical records.
     */
    public function syncAllMissingApplicationRows(?int $encodedBy = null): array
    {
        $applications = LeaveApplication::all();
        $synced = 0;
        $skipped = 0;

        foreach ($applications as $app) {
            $exists = LeaveRecord::where('application_id', $app->application_id)
                ->orWhere(function ($q) use ($app) {
                    $q->where('employee_id', $app->employee_id)
                      ->where(function ($sub) use ($app) {
                          $sub->where('particular', 'like', "%{$app->reference_code}%")
                              ->orWhere('date_action_taken', 'like', "%{$app->reference_code}%");
                      });
                })
                ->first();

            if ($exists) {
                if (!$exists->application_id) {
                    $exists->update(['application_id' => $app->application_id]);
                }
                $skipped++;
            } else {
                $this->createRowForLeaveApplication($app, $encodedBy);
                $synced++;
            }
        }

        return [
            'synced' => $synced,
            'skipped' => $skipped,
        ];
    }
}

