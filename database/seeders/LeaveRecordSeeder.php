<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\LeaveRecord;
use App\Services\LeaveRecordService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveRecordSeeder extends Seeder
{
    /**
     * Run the database seeds for official Civil Service Leave Records.
     * Seeds exact data matching the physical scanned card for Camille Faurillo
     * and baseline records for municipal personnel.
     */
    public function run(): void
    {
        $service = new LeaveRecordService();
        $adminUser = DB::table('users')->where('username', 'admin_hrmu')->first();
        $adminId = $adminUser->user_id ?? 1;

        // ---------------------------------------------------------------------
        // 1. Seed Camille Faurillo (Primary Scanned Card Artifact Reference)
        // ---------------------------------------------------------------------
        $camille = Employee::where('full_name', 'like', '%Camille Faurillo%')
            ->orWhere('employee_no', '002')
            ->orWhere('employee_no', 'BASUD-HR-002')
            ->orWhere('employee_no', 'BASUD-EMP-104')
            ->first();

        if ($camille) {
            LeaveRecord::where('employee_id', $camille->employee_id)->delete();

            $camilleRows = [];
            if (!$camille->isJobOrder()) {
                $camilleRows = [
                [
                    'period' => 'July',
                    'particular' => 'Earned',
                    'vl_earned' => '1.25',
                    'vl_absence_with_pay' => null,
                    'vl_balance' => '1.25',
                    'vl_absence_without_pay' => null,
                    'sl_earned' => '1.25',
                    'sl_absence_with_pay' => null,
                    'sl_balance' => '1.25',
                    'sl_absence_without_pay' => null,
                    'date_action_taken' => 'Monthly Accrual (CSC Table II - 30 days of service)',
                    'row_order' => 1,
                ],
                [
                    'period' => 'July',
                    'particular' => 'L (1 hr and 3 mins)',
                    'vl_earned' => null,
                    'vl_absence_with_pay' => '0.1256',
                    'vl_balance' => '1.1244',
                    'vl_absence_without_pay' => null,
                    'sl_earned' => null,
                    'sl_absence_with_pay' => null,
                    'sl_balance' => '1.25',
                    'sl_absence_without_pay' => null,
                    'date_action_taken' => 'DTR July 1-15: 1h late (0.125) + 3m late (0.0006) = 0.1256 days (Table IV)',
                    'row_order' => 2,
                ],
                [
                    'period' => 'August',
                    'particular' => 'Earned',
                    'vl_earned' => '1.25',
                    'vl_absence_with_pay' => null,
                    'vl_balance' => '2.3744',
                    'vl_absence_without_pay' => null,
                    'sl_earned' => '1.25',
                    'sl_absence_with_pay' => null,
                    'sl_balance' => '2.50',
                    'sl_absence_without_pay' => null,
                    'date_action_taken' => 'Monthly Accrual (CSC Table II - 30 days of service)',
                    'row_order' => 3,
                ],
                [
                    'period' => 'August',
                    'particular' => 'FL (1)',
                    'vl_earned' => null,
                    'vl_absence_with_pay' => '1',
                    'vl_balance' => '1.3744',
                    'vl_absence_without_pay' => null,
                    'sl_earned' => null,
                    'sl_absence_with_pay' => null,
                    'sl_balance' => '2.50',
                    'sl_absence_without_pay' => null,
                    'date_action_taken' => 'Aug 18, 2026 - Approved by Dept Head & Mayor; Ref: LV-2026-0012',
                    'row_order' => 4,
                ],
                [
                    'period' => 'September',
                    'particular' => 'Earned',
                    'vl_earned' => '1.25',
                    'vl_absence_with_pay' => null,
                    'vl_balance' => '2.6244',
                    'vl_absence_without_pay' => null,
                    'sl_earned' => '1.25',
                    'sl_absence_with_pay' => null,
                    'sl_balance' => '3.75',
                    'sl_absence_without_pay' => null,
                    'date_action_taken' => 'Monthly Accrual (CSC Table II - 30 days of service)',
                    'row_order' => 5,
                    'accrual_month' => '2026-09',
                ],
                [
                    'period' => 'October',
                    'particular' => 'Earned',
                    'vl_earned' => '1.25',
                    'vl_absence_with_pay' => null,
                    'vl_balance' => '3.8744',
                    'vl_absence_without_pay' => null,
                    'sl_earned' => '1.25',
                    'sl_absence_with_pay' => null,
                    'sl_balance' => '5.00',
                    'sl_absence_without_pay' => null,
                    'date_action_taken' => 'Monthly Accrual (CSC Table II - 30 days of service)',
                    'row_order' => 6,
                    'accrual_month' => '2026-10',
                ],
                [
                    'period' => 'October',
                    'particular' => 'SL (2)',
                    'vl_earned' => null,
                    'vl_absence_with_pay' => null,
                    'vl_balance' => '3.8744',
                    'vl_absence_without_pay' => null,
                    'sl_earned' => null,
                    'sl_absence_with_pay' => '2',
                    'sl_balance' => '3.00',
                    'sl_absence_without_pay' => null,
                    'date_action_taken' => 'Oct 14-15, 2026 - Medical Certificate verified; Approved; Ref: LV-2026-0045',
                    'row_order' => 7,
                ],
                [
                    'period' => 'October',
                    'particular' => 'SPL (3)',
                    'vl_earned' => null,
                    'vl_absence_with_pay' => null,
                    'vl_balance' => '3.8744',
                    'vl_absence_without_pay' => null,
                    'sl_earned' => null,
                    'sl_absence_with_pay' => '3',
                    'sl_balance' => '3.00', // DO NOT DEDUCT from Sick Leave balance!
                    'sl_absence_without_pay' => null,
                    'date_action_taken' => 'Oct 21-23, 2026 - Special Privilege Leave (Personal Milestone); Approved; Ref: LV-2026-0051',
                    'row_order' => 8,
                ],
                [
                    'period' => 'November',
                    'particular' => 'Earned',
                    'vl_earned' => '1.25',
                    'vl_absence_with_pay' => null,
                    'vl_balance' => '5.1244',
                    'vl_absence_without_pay' => null,
                    'sl_earned' => '1.25',
                    'sl_absence_with_pay' => null,
                    'sl_balance' => '4.25',
                    'sl_absence_without_pay' => null,
                    'date_action_taken' => 'Monthly Accrual (CSC Table II - 30 days of service)',
                    'row_order' => 9,
                    'accrual_month' => '2026-11',
                ],
                [
                    'period' => 'November',
                    'particular' => 'FL (1)',
                    'vl_earned' => null,
                    'vl_absence_with_pay' => '1',
                    'vl_balance' => '4.1244',
                    'vl_absence_without_pay' => null,
                    'sl_earned' => null,
                    'sl_absence_with_pay' => null,
                    'sl_balance' => '4.25',
                    'sl_absence_without_pay' => null,
                    'date_action_taken' => 'Nov 12, 2026 - Mandatory Forced Leave; Approved; Ref: LV-2026-0078',
                    'row_order' => 10,
                ],
            ];
            }

            foreach ($camilleRows as $row) {
                LeaveRecord::create(array_merge($row, [
                    'employee_id' => $camille->employee_id,
                    'encoded_by' => $adminId,
                ]));
            }

            // Recalculate to ensure 100% balance consistency
            // $service->recalculateEmployeeBalances($camille->employee_id);
        }

        // ---------------------------------------------------------------------
        // 2. Seed Baseline Records for other employees (regular personnel eligible for leave)
        // ---------------------------------------------------------------------
        $otherEmployees = Employee::eligibleForLeave()->get();

        foreach ($otherEmployees as $emp) {
            LeaveRecord::where('employee_id', $emp->employee_id)->delete();

            // Date hired determines real civil service timeline
            $hireDate = $emp->date_hired ? Carbon::parse($emp->date_hired) : Carbon::parse('2020-01-01');
            $hireYear = $hireDate->year;
            $hireMonth = $hireDate->month;
            $hireDay = $hireDate->day;

            $rowCounter = 1;

            if ($hireYear < 2026) {
                // Employees hired prior to baseline year 2026:
                // They carry over a certified opening balance as of January 1, 2026 from their 2025 physical cardstock ledger
                $vlOpening = DB::table('leave_balances')
                    ->where('employee_id', $emp->employee_id)
                    ->where('leave_type_id', 1)
                    ->value('balance') ?? 15.000;

                $slOpening = DB::table('leave_balances')
                    ->where('employee_id', $emp->employee_id)
                    ->where('leave_type_id', 2)
                    ->value('balance') ?? 12.000;

                LeaveRecord::create([
                    'employee_id' => $emp->employee_id,
                    'period' => 'January 2026',
                    'particular' => 'CERTIFIED BASELINE OPENING BALANCE',
                    'vl_earned' => null,
                    'vl_absence_with_pay' => null,
                    'vl_balance' => (string)$vlOpening,
                    'vl_absence_without_pay' => null,
                    'sl_earned' => null,
                    'sl_absence_with_pay' => null,
                    'sl_balance' => (string)$slOpening,
                    'sl_absence_without_pay' => null,
                    'date_action_taken' => 'Certified Opening Balance carried forward from 2025 Physical Ledger Card (CS Form 41)',
                    'row_order' => $rowCounter++,
                    'encoded_by' => $adminId,
                ]);

                // Standard monthly accruals for 2026 up to current month (e.g. up to November 2026)
                for ($m = 1; $m <= 11; $m++) {
                    $monthDate = Carbon::create(2026, $m, 1);
                    LeaveRecord::create([
                        'employee_id' => $emp->employee_id,
                        'period' => $monthDate->format('F Y'),
                        'particular' => 'Earned',
                        'accrual_month' => $monthDate->format('Y-m'),
                        'is_prorated' => false,
                        'vl_earned' => '1.25',
                        'sl_earned' => '1.25',
                        'row_order' => $rowCounter++,
                        'date_action_taken' => 'Monthly Accrual (CSC Table II - 30 days of service)',
                        'encoded_by' => $adminId,
                    ]);
                }
            } else {
                // Employee hired during 2026 (e.g. Johann Chavez hired March 16, 2026):
                // ACCRUAL ONLY STARTS ON OR AFTER THEIR HIRE DATE. Zero credits prior to hire date!
                // First month: Prorated Table II accrual if hired mid-month, or full 1.25 if day 1
                $startMonth = $hireMonth;

                for ($m = $startMonth; $m <= 11; $m++) {
                    $monthDate = Carbon::create(2026, $m, 1);

                    if ($m === $hireMonth && $hireDay > 1) {
                        // Mid-month hire: compute exact Table II days of service
                        // Civil Service standard 30-day baseline: days of service = 30 - day + 1
                        $daysOfService = max(1, min(30, 30 - $hireDay + 1));
                        $refCredit = DB::table('starla_reference')
                            ->where('table_ref', 'Table II')
                            ->where('input_value', "{$daysOfService} " . ($daysOfService === 1 ? 'day' : 'days'))
                            ->value('day_equivalent');
                        $creditEarned = $refCredit !== null ? (string)$refCredit : sprintf('%.3f', ($daysOfService / 30) * 1.250);

                        LeaveRecord::create([
                            'employee_id' => $emp->employee_id,
                            'period' => $monthDate->format('F Y'),
                            'particular' => "Earned (Table II: {$daysOfService} days)",
                            'accrual_month' => $monthDate->format('Y-m'),
                            'is_prorated' => true,
                            'vl_earned' => $creditEarned,
                            'sl_earned' => $creditEarned,
                            'row_order' => $rowCounter++,
                            'date_action_taken' => "Appointment / Start Date: {$hireDate->format('M d, Y')} ({$daysOfService} days of service per CSC Table II)",
                            'encoded_by' => $adminId,
                        ]);
                    } else {
                        // Full month service (30 days)
                        LeaveRecord::create([
                            'employee_id' => $emp->employee_id,
                            'period' => $monthDate->format('F Y'),
                            'particular' => 'Earned',
                            'accrual_month' => $monthDate->format('Y-m'),
                            'is_prorated' => false,
                            'vl_earned' => '1.25',
                            'sl_earned' => '1.25',
                            'row_order' => $rowCounter++,
                            'date_action_taken' => 'Monthly Accrual (CSC Table II - 30 days of service)',
                            'encoded_by' => $adminId,
                        ]);
                    }
                }
            }

            $service->recalculateEmployeeBalances($emp->employee_id);
        }

        $this->command->info("✔ Successfully seeded official Civil Service Leave Records for municipal personnel.");
    }
}

