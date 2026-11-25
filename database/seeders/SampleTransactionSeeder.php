<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SampleTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds for realistic LGU operational transactions.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Retrieve foreign key references
        $seniorEmp = DB::table('employees')->where('employee_no', '101')->orWhere('employee_no', 'BASUD-EMP-101')->first()
            ?? DB::table('employees')->first();
        $midEmp = DB::table('employees')->where('employee_no', '102')->orWhere('employee_no', 'BASUD-EMP-102')->first()
            ?? $seniorEmp;
        $criticalEmp = DB::table('employees')->where('employee_no', '103')->orWhere('employee_no', 'BASUD-EMP-103')->first()
            ?? $seniorEmp;

        $seniorEmpId = $seniorEmp->employee_id ?? $seniorEmp->id;
        $midEmpId = $midEmp->employee_id ?? $midEmp->id;
        $criticalEmpId = $criticalEmp->employee_id ?? $criticalEmp->id;

        $hrUser = DB::table('users')->where('username', 'hr_staff01')->first();
        $mayorClerkUser = DB::table('users')->where('username', 'clerk_mayor')->first();
        $mpdoClerkUser = DB::table('users')->where('username', 'clerk_mpdo')->first();

        $hrmuOffice = DB::table('offices')->where('office_name', 'like', '%Human Resource%')->first();
        $mayorOffice = DB::table('offices')->where('office_name', 'like', '%Mayor%')->first();
        $mpdoOffice = DB::table('offices')->where('office_name', 'like', '%Planning%')->first();

        $vlType = DB::table('leave_types')->where('code', 'VL')->first();
        $slType = DB::table('leave_types')->where('code', 'SL')->first();

        $firstOctPeriod = DB::table('pay_periods')->where('month', 10)->where('half', 1)->first();

        // =====================================================================
        // SCENARIO A: Completed & Approved 3-Day Vacation Leave (Full Routing Trail)
        // =====================================================================
        $appARef = 'LV-2026-0001';
        $appAId = DB::table('leave_applications')->updateOrInsert(
            ['reference_code' => $appARef],
            [
                'employee_id' => $seniorEmpId,
                'leave_type_id' => $vlType->leave_type_id ?? $vlType->id ?? 1,
                'date_from' => '2026-10-12',
                'date_to' => '2026-10-14',
                'date_of_filing' => '2026-10-05',
                'days_requested' => 3.000,
                'status' => 'Approved',
                'details_type' => 'within_ph',
                'details_specify' => 'Basud, Camarines Norte',
                'details_of_leave' => 'Within the Philippines (Basud, Camarines Norte)',
                'created_at' => Carbon::parse('2026-10-05 08:30:00'),
                'updated_at' => Carbon::parse('2026-10-09 16:45:00'),
            ]
        );

        $appARecord = DB::table('leave_applications')->where('reference_code', $appARef)->first();
        $applicationAId = $appARecord->application_id ?? $appARecord->id;

        // Routing logs simulating the physical hand-offs
        $routingAEvents = [
            [
                'application_id' => $applicationAId,
                'office_id' => $mpdoOffice->office_id ?? 4,
                'scanned_by' => $mpdoClerkUser->user_id ?? $mpdoClerkUser->id ?? 1,
                'action' => 'DISPATCHED',
                'method' => 'QR_SCAN',
                'scanned_at' => Carbon::parse('2026-10-05 09:15:00'),
                'created_at' => Carbon::parse('2026-10-05 09:15:00'),
                'updated_at' => Carbon::parse('2026-10-05 09:15:00'),
            ],
            [
                'application_id' => $applicationAId,
                'office_id' => $hrmuOffice->office_id ?? 1,
                'scanned_by' => $hrUser->user_id ?? $hrUser->id ?? 2,
                'action' => 'RECEIVED',
                'method' => 'QR_SCAN',
                'scanned_at' => Carbon::parse('2026-10-05 10:20:00'),
                'created_at' => Carbon::parse('2026-10-05 10:20:00'),
                'updated_at' => Carbon::parse('2026-10-05 10:20:00'),
            ],
            [
                'application_id' => $applicationAId,
                'office_id' => $hrmuOffice->office_id ?? 1,
                'scanned_by' => $hrUser->user_id ?? $hrUser->id ?? 2,
                'action' => 'DISPATCHED', // Batch forward with transmittal
                'method' => 'QR_SCAN',
                'scanned_at' => Carbon::parse('2026-10-06 14:00:00'),
                'created_at' => Carbon::parse('2026-10-06 14:00:00'),
                'updated_at' => Carbon::parse('2026-10-06 14:00:00'),
            ],
            [
                'application_id' => $applicationAId,
                'office_id' => $mayorOffice->office_id ?? 2,
                'scanned_by' => $mayorClerkUser->user_id ?? $mayorClerkUser->id ?? 3,
                'action' => 'RECEIVED',
                'method' => 'QR_SCAN',
                'scanned_at' => Carbon::parse('2026-10-07 08:45:00'),
                'created_at' => Carbon::parse('2026-10-07 08:45:00'),
                'updated_at' => Carbon::parse('2026-10-07 08:45:00'),
            ],
            [
                'application_id' => $applicationAId,
                'office_id' => $mayorOffice->office_id ?? 2,
                'scanned_by' => $mayorClerkUser->user_id ?? $mayorClerkUser->id ?? 3,
                'action' => 'APPROVED_PHYSICAL', // Mayor Davoco physically signed Box 7.C
                'method' => 'MANUAL_ENTRY',
                'scanned_at' => Carbon::parse('2026-10-08 11:30:00'),
                'created_at' => Carbon::parse('2026-10-08 11:30:00'),
                'updated_at' => Carbon::parse('2026-10-08 11:30:00'),
            ],
            [
                'application_id' => $applicationAId,
                'office_id' => $hrmuOffice->office_id ?? 1,
                'scanned_by' => $hrUser->user_id ?? $hrUser->id ?? 2,
                'action' => 'RECEIVED', // Returned to HRMU & debited in digital ledger
                'method' => 'QR_SCAN',
                'scanned_at' => Carbon::parse('2026-10-09 16:45:00'),
                'created_at' => Carbon::parse('2026-10-09 16:45:00'),
                'updated_at' => Carbon::parse('2026-10-09 16:45:00'),
            ],
        ];

        foreach ($routingAEvents as $evt) {
            DB::table('routing_logs')->insertOrIgnore($evt);
        }

        // Ledger Entry for Approved 3-Day Leave
        DB::table('leave_ledger_entries')->insertOrIgnore([
            'employee_id' => $seniorEmpId,
            'leave_type_id' => $vlType->leave_type_id ?? $vlType->id ?? 1,
            'application_id' => $applicationAId,
            'deduction_id' => null,
            'entry_type' => 'LEAVE_AVAILED',
            'days' => 3.000,
            'balance_after' => 21.500, // 24.500 - 3.000
            'posted_at' => Carbon::parse('2026-10-09 16:45:00'),
            'created_at' => Carbon::parse('2026-10-09 16:45:00'),
            'updated_at' => Carbon::parse('2026-10-09 16:45:00'),
        ]);

        // =====================================================================
        // SCENARIO B: Active In-Flight Leave Application Waiting at Mayor's Office
        // =====================================================================
        $appBRef = 'LV-2026-0002';
        DB::table('leave_applications')->updateOrInsert(
            ['reference_code' => $appBRef],
            [
                'employee_id' => $midEmpId,
                'leave_type_id' => $vlType->leave_type_id ?? $vlType->id ?? 1,
                'date_from' => '2026-11-03',
                'date_to' => '2026-11-05',
                'date_of_filing' => '2026-10-25',
                'days_requested' => 3.000,
                'status' => 'Pending Mayor Approval',
                'details_type' => 'within_ph',
                'details_specify' => 'Daet, Camarines Norte',
                'details_of_leave' => 'Within the Philippines (Daet, Camarines Norte)',
                'created_at' => Carbon::parse('2026-10-25 10:00:00'),
                'updated_at' => Carbon::parse('2026-10-27 09:30:00'),
            ]
        );

        $appBRecord = DB::table('leave_applications')->where('reference_code', $appBRef)->first();
        $applicationBId = $appBRecord->application_id ?? $appBRecord->id;

        // Routing logs for Scenario B
        $routingBEvents = [
            [
                'application_id' => $applicationBId,
                'office_id' => $hrmuOffice->office_id ?? 1,
                'scanned_by' => $hrUser->user_id ?? $hrUser->id ?? 2,
                'action' => 'RECEIVED',
                'method' => 'QR_SCAN',
                'scanned_at' => Carbon::parse('2026-10-25 11:00:00'),
                'created_at' => Carbon::parse('2026-10-25 11:00:00'),
                'updated_at' => Carbon::parse('2026-10-25 11:00:00'),
            ],
            [
                'application_id' => $applicationBId,
                'office_id' => $mayorOffice->office_id ?? 2,
                'scanned_by' => $mayorClerkUser->user_id ?? $mayorClerkUser->id ?? 3,
                'action' => 'RECEIVED',
                'method' => 'QR_SCAN',
                'scanned_at' => Carbon::parse('2026-10-27 09:30:00'),
                'created_at' => Carbon::parse('2026-10-27 09:30:00'),
                'updated_at' => Carbon::parse('2026-10-27 09:30:00'),
            ],
        ];

        foreach ($routingBEvents as $evt) {
            DB::table('routing_logs')->insertOrIgnore($evt);
        }

        // Seed Batch Transmittal Forwarding Scenario B
        $transmittalNo = 'TR-2026-10-0042';
        DB::table('transmittals')->updateOrInsert(
            ['transmittal_no' => $transmittalNo],
            [
                'office_id' => $mayorOffice->office_id ?? 2,
                'prepared_by' => $hrUser->user_id ?? $hrUser->id ?? 2,
                'generated_at' => Carbon::parse('2026-10-26 15:00:00'),
                'created_at' => Carbon::parse('2026-10-26 15:00:00'),
                'updated_at' => Carbon::parse('2026-10-26 15:00:00'),
            ]
        );

        $transRecord = DB::table('transmittals')->where('transmittal_no', $transmittalNo)->first();
        $transId = $transRecord->transmittal_id ?? $transRecord->id;

        DB::table('transmittal_items')->insertOrIgnore([
            'transmittal_id' => $transId,
            'application_id' => $applicationBId,
            'created_at' => Carbon::parse('2026-10-26 15:00:00'),
            'updated_at' => Carbon::parse('2026-10-26 15:00:00'),
        ]);

        // =====================================================================
        // SCENARIO C: Semi-Monthly Attendance Tardiness Deduction (Starla Table IV)
        // =====================================================================
        // Mid-level Employee incurred 45 minutes of aggregate tardiness in Oct 1-15
        // Starla Table IV: 45 minutes = 0.094 day
        $periodId = $firstOctPeriod->pay_period_id ?? $firstOctPeriod->id ?? 19;
        $tardyEmpId = $midEmpId;

        DB::table('attendance_deductions')->updateOrInsert(
            [
                'employee_id' => $tardyEmpId,
                'pay_period_id' => $periodId,
            ],
            [
                'encoded_by' => $hrUser->user_id ?? $hrUser->id ?? 2,
                'days_absent' => 0.00,
                'late_hours' => 0,
                'late_minutes' => 45,
                'days_deducted' => 0.094, // From Starla Table IV
                'encoded_at' => Carbon::parse('2026-10-18 10:30:00'),
                'created_at' => Carbon::parse('2026-10-18 10:30:00'),
                'updated_at' => Carbon::parse('2026-10-18 10:30:00'),
            ]
        );

        $deductionRecord = DB::table('attendance_deductions')
            ->where('employee_id', $tardyEmpId)
            ->where('pay_period_id', $periodId)
            ->first();
        $deductionId = $deductionRecord->deduction_id ?? $deductionRecord->id;

        // Ledger Entry: Debit 0.094 days from Vacation Leave
        DB::table('leave_ledger_entries')->insertOrIgnore([
            'employee_id' => $tardyEmpId,
            'leave_type_id' => $vlType->leave_type_id ?? $vlType->id ?? 1,
            'application_id' => null,
            'deduction_id' => $deductionId,
            'entry_type' => 'TARDINESS_DEDUCTION',
            'days' => 0.094,
            'balance_after' => 8.031, // 8.125 - 0.094
            'posted_at' => Carbon::parse('2026-10-18 10:30:00'),
            'created_at' => Carbon::parse('2026-10-18 10:30:00'),
            'updated_at' => Carbon::parse('2026-10-18 10:30:00'),
        ]);

        // Audit Trail Record
        DB::table('audit_logs')->insertOrIgnore([
            'user_id' => $hrUser->user_id ?? $hrUser->id ?? 2,
            'action' => 'POST_TARDINESS_DEDUCTION',
            'table_name' => 'attendance_deductions',
            'record_id' => $deductionId,
            'logged_at' => Carbon::parse('2026-10-18 10:30:00'),
            'created_at' => Carbon::parse('2026-10-18 10:30:00'),
            'updated_at' => Carbon::parse('2026-10-18 10:30:00'),
        ]);

        $this->command->info('✔ Seeded sample transactions: Scenario A (Approved Leave & Routing Logs), Scenario B (Pending with Batch Transmittal), and Scenario C (Starla Tardiness Deduction).');
    }
}
