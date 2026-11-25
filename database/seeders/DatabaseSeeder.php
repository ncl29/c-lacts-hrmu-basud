<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database for C-LACTS (HRMU Basud).
     */
    public function run(): void
    {
        $this->command->newLine();
        $this->command->info('====================================================================');
        $this->command->info('   C-LACTS DATABASE SEEDER (HRMU MUNICIPALITY OF BASUD)             ');
        $this->command->info('====================================================================');

        // Execute all seeders in strict dependency order within a transaction
        DB::transaction(function () {
            $this->call([
                OfficeSeeder::class,
                LeaveTypeSeeder::class,
                StarlaReferenceSeeder::class,
                PayPeriodSeeder::class,
                UserAndEmployeeSeeder::class,
                SampleTransactionSeeder::class,
                LeaveRecordSeeder::class,
            ]);
        });

        // ---------------------------------------------------------------------
        // Output Credential & Test Data Summary Table
        // ---------------------------------------------------------------------
        $this->command->newLine();
        $this->command->info('====================================================================');
        $this->command->info('           C-LACTS TEST USERS & RBAC LOGIN CREDENTIALS              ');
        $this->command->info('====================================================================');

        $headers = ['Role', 'Username', 'Password', 'Employee Name', 'Assigned Office'];
        $rows = [
            ['System Admin', 'admin_hrmu', 'password123', 'Vicky C. Alvaro', 'Administrative Officer IV - HRMU II'],
            ['HR Records', 'hr_staff01', 'password123', 'Camille Faurillo', 'HRMU (Basud)'],
            ['Routing Clerk', 'clerk_mayor', 'password123', 'Mikylla Nanez', 'Office of the Mayor'],
            ['Routing Clerk', 'clerk_mpdo', 'password123', 'Divine Ponciano', 'MPDO Office'],
            ['HR Staff (Alias)', 'camille_faurillo', 'password123', 'Camille Faurillo', 'HRMU (Leave Record Card)'],
            ['Employee (Senior)', 'emp_senior', 'password123', 'Grace Bolos', 'HRMU (VL: 24.5, SL: 18.25)'],
            ['Employee (Mid)', 'emp_mid', 'password123', 'ShalieMar De Vera', 'GSO (VL: 8.031, SL: 5.0)'],
            ['Employee (Critical)', 'emp_critical', 'password123', 'Craton Z. Genorga', 'MTO (VL: 0.5, SL: 0.0)'],
            ['Municipal Mayor', 'emp_mayor', 'password123', 'Hon. Shunn Davoco', 'Office of the Mayor'],
            ['Department Head', 'emp_depthead', 'password123', 'Marjory Santillan', 'MPDO (Coordinator)'],
            ['MPDO Employee', 'johann_chavez', 'password123', 'Johann Chavez', 'MPDO (Planning Assistant)'],
        ];

        $this->command->table($headers, $rows);

        // Operational Counts Summary
        $officesCount = DB::table('offices')->count();
        $leaveTypesCount = DB::table('leave_types')->count();
        $starlaCount = DB::table('starla_reference')->count();
        $payPeriodsCount = DB::table('pay_periods')->count();
        $employeesCount = DB::table('employees')->count();
        $usersCount = DB::table('users')->count();
        $applicationsCount = DB::table('leave_applications')->count();
        $routingLogsCount = DB::table('routing_logs')->count();
        $ledgerEntriesCount = DB::table('leave_ledger_entries')->count();

        $this->command->newLine();
        $this->command->info('----------------- SEEDED DATABASE TOTALS -----------------');
        $this->command->line(" • Municipal Offices:         {$officesCount}");
        $this->command->line(" • CSC Leave Types:           {$leaveTypesCount}");
        $this->command->line(" • Starla Conversion Rules:   {$starlaCount} (Tables II, III, & IV)");
        $this->command->line(" • Semi-Monthly Pay Periods:  {$payPeriodsCount} (Year 2026)");
        $this->command->line(" • Personnel / Employees:     {$employeesCount}");
        $this->command->line(" • System User Accounts:      {$usersCount}");
        $this->command->line(" • Seeded Leave Applications: {$applicationsCount}");
        $this->command->line(" • Physical Routing Scans:    {$routingLogsCount}");
        $this->command->line(" • Digital Ledger Entries:    {$ledgerEntriesCount}");
        $this->command->info('====================================================================');
        $this->command->info('   ✔ C-LACTS DATABASE READY FOR SECONDARY DEFENSE DEMONSTRATION!    ');
        $this->command->info('====================================================================');
        $this->command->newLine();
    }
}
