<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserAndEmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds for Employees, Users, and Certified Leave Balances.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $defaultPassword = Hash::make('password123');

        // Retrieve required office IDs
        $hrmuOffice = DB::table('offices')->where('office_name', 'like', '%Human Resource%')->first();
        $mayorOffice = DB::table('offices')->where('office_name', 'like', '%Mayor%')->first();
        $mpdoOffice = DB::table('offices')->where('office_name', 'like', '%Planning%')->first();
        $gsoOffice = DB::table('offices')->where('office_name', 'like', '%General Services%')->first();
        $mtoOffice = DB::table('offices')->where('office_name', 'like', '%Treasury%')->first();

        // Retrieve leave type IDs
        $vlType = DB::table('leave_types')->where('code', 'VL')->first();
        $slType = DB::table('leave_types')->where('code', 'SL')->first();
        $flType = DB::table('leave_types')->where('code', 'FL')->first();
        $splType = DB::table('leave_types')->where('code', 'SPL')->first();
        $wlType = DB::table('leave_types')->where('code', 'WL')->first();

        // ---------------------------------------------------------------------
        // 1. Definition of Roles, Personnel & Test Cases
        // ---------------------------------------------------------------------
        $personnelData = [
            // Admin / HR Head
            [
                'employee_no' => 'HRMU-0001',
                'full_name' => 'Vicky C. Alvaro',
                'position' => 'Administrative Officer IV - HRMU II',
                'employment_type' => 'Permanent',
                'office_id' => $hrmuOffice->office_id ?? 1,
                'username' => 'admin_hrmu',
                'role' => 'admin',
                'balances' => ['VL' => 35.250, 'SL' => 28.125, 'FL' => 5.0, 'SPL' => 3.0, 'WL' => 5.0],
            ],
            // HR Staff / Record Keeper (Job Order personnel per HRMU operational policy)
            [
                'employee_no' => 'HRMU-0002',
                'full_name' => 'Camille Faurillo',
                'position' => 'Administrative Assistant II (Job Order)',
                'employment_type' => 'Job Order',
                'office_id' => $hrmuOffice->office_id ?? 1,
                'username' => 'hr_staff01',
                'role' => 'hrmu_staff',
                'balances' => [],
            ],
            // Routing Clerk - Mayor's Office
            [
                'employee_no' => 'MAYOR-0002',
                'full_name' => 'Mikylla Nanez',
                'position' => 'Administrative Aide VI - Mayor\'s Receiving',
                'employment_type' => 'Permanent',
                'office_id' => $mayorOffice->office_id ?? 2,
                'username' => 'clerk_mayor',
                'role' => 'routing_clerk',
                'balances' => ['VL' => 11.250, 'SL' => 9.375, 'FL' => 5.0, 'SPL' => 3.0, 'WL' => 5.0],
            ],
            // Routing Clerk - MPDO
            [
                'employee_no' => 'MPDO-0002',
                'full_name' => 'Divine Ponciano',
                'position' => 'Administrative Aide IV - MPDO Receiving',
                'employment_type' => 'Permanent',
                'office_id' => $mpdoOffice->office_id ?? 4,
                'username' => 'clerk_mpdo',
                'role' => 'routing_clerk',
                'balances' => ['VL' => 9.500, 'SL' => 7.000, 'FL' => 0.0, 'SPL' => 3.0, 'WL' => 5.0],
            ],
            // Edge Case 1: Senior Regular Employee (High balance >= 10 VL, FL Eligible)
            [
                'employee_no' => 'HRMU-0003',
                'full_name' => 'Grace Bolos',
                'position' => 'Senior Administrative Assistant I',
                'employment_type' => 'Permanent',
                'office_id' => $hrmuOffice->office_id ?? 1,
                'username' => 'emp_senior',
                'role' => 'employee',
                'balances' => ['VL' => 24.500, 'SL' => 18.250, 'FL' => 5.0, 'SPL' => 3.0, 'WL' => 5.0],
            ],
            // Edge Case 2: Mid-level Employee (< 10 VL, Ineligible for Forced Leave)
            [
                'employee_no' => 'GSO-0001',
                'full_name' => 'ShalieMar De Vera',
                'position' => 'General Services Assistant II',
                'employment_type' => 'Permanent',
                'office_id' => $gsoOffice->office_id ?? 11,
                'username' => 'emp_mid',
                'role' => 'employee',
                'balances' => ['VL' => 8.125, 'SL' => 5.000, 'FL' => 0.0, 'SPL' => 3.0, 'WL' => 5.0],
            ],
            // Edge Case 3: Low-balance / Critical Employee (Tests LWOP conversion)
            [
                'employee_no' => 'MTO-0001',
                'full_name' => 'Craton Z. Genorga',
                'position' => 'Administrative Aide I',
                'employment_type' => 'Casual',
                'office_id' => $mtoOffice->office_id ?? 12,
                'username' => 'emp_critical',
                'role' => 'employee',
                'balances' => ['VL' => 0.500, 'SL' => 0.000, 'FL' => 0.0, 'SPL' => 1.0, 'WL' => 2.0],
            ],
            // Edge Case 4: Municipal Mayor (Elected Approving Authority)
            [
                'employee_no' => 'MAYOR-0001',
                'full_name' => 'Hon. Shunn Frederick S. Davoco',
                'position' => 'Municipal Mayor',
                'employment_type' => 'Elected',
                'office_id' => $mayorOffice->office_id ?? 2,
                'username' => 'emp_mayor',
                'role' => 'employee', // Mayor files leaves and has employee record, approvals are tracked via clerks
                'balances' => ['VL' => 30.000, 'SL' => 30.000, 'FL' => 5.0, 'SPL' => 3.0, 'WL' => 5.0],
            ],
            // Edge Case 5: Department Head (Recommending Officer)
            [
                'employee_no' => 'MPDO-0001',
                'full_name' => 'Marjory A. Santillan',
                'position' => 'Municipal Planning & Development Coordinator',
                'employment_type' => 'Permanent',
                'office_id' => $mpdoOffice->office_id ?? 4,
                'username' => 'emp_depthead',
                'role' => 'employee',
                'balances' => ['VL' => 21.375, 'SL' => 16.500, 'FL' => 5.0, 'SPL' => 3.0, 'WL' => 5.0],
            ],
            // MPDO Regular Employee (View & Track Level)
            [
                'employee_no' => 'MPDO-0003',
                'full_name' => 'Johann Chavez',
                'position' => 'Planning Assistant',
                'employment_type' => 'Permanent',
                'office_id' => $mpdoOffice->office_id ?? 4,
                'username' => 'johann_chavez',
                'role' => 'employee',
                'balances' => ['VL' => 14.500, 'SL' => 11.250, 'FL' => 5.0, 'SPL' => 3.0, 'WL' => 5.0],
            ],
        ];

        // ---------------------------------------------------------------------
        // 2. Insert or Update Employees, Users, and Opening Balances
        // ---------------------------------------------------------------------
        foreach ($personnelData as $p) {
            $parsedName = \App\Models\Employee::parsePhilippineName($p['full_name']);

            // A. Seed Employee Record
            DB::table('employees')->updateOrInsert(
                ['employee_no' => $p['employee_no']],
                [
                    'full_name' => $p['full_name'],
                    'last_name' => $parsedName['last_name'],
                    'first_name' => $parsedName['first_name'],
                    'middle_name' => $parsedName['middle_name'],
                    'position' => $p['position'],
                    'employment_type' => $p['employment_type'],
                    'office_id' => $p['office_id'],
                    'status' => 'active',
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $empRecord = DB::table('employees')->where('employee_no', $p['employee_no'])->first();
            $empId = $empRecord->employee_id ?? $empRecord->id;

            // B. Seed User Account
            DB::table('users')->updateOrInsert(
                ['username' => $p['username']],
                [
                    'employee_id' => $empId,
                    'password_hash' => $defaultPassword,
                    'role' => $p['role'],
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            // C. Seed Certified Leave Balances (Year 2026)
            if (!empty($p['balances'])) {
                $balanceMap = [
                    $vlType->leave_type_id ?? $vlType->id ?? 1 => $p['balances']['VL'] ?? 0.0,
                    $slType->leave_type_id ?? $slType->id ?? 2 => $p['balances']['SL'] ?? 0.0,
                    $flType->leave_type_id ?? $flType->id ?? 3 => $p['balances']['FL'] ?? 0.0,
                    $splType->leave_type_id ?? $splType->id ?? 4 => $p['balances']['SPL'] ?? 0.0,
                    $wlType->leave_type_id ?? $wlType->id ?? 5 => $p['balances']['WL'] ?? 0.0,
                ];

                foreach ($balanceMap as $typeId => $balValue) {
                    DB::table('leave_balances')->updateOrInsert(
                        [
                            'employee_id' => $empId,
                            'leave_type_id' => $typeId,
                            'year' => 2026,
                        ],
                        [
                            'balance' => $balValue,
                            'last_posted_at' => $now,
                            'updated_at' => $now,
                            'created_at' => $now,
                        ]
                    );
                }
            }
        }

        // Link optional username alias camille_faurillo to HR Staff employee record
        $camilleEmp = DB::table('employees')->where('employee_no', 'HRMU-0002')->orWhere('employee_no', '002')->first();
        if ($camilleEmp) {
            DB::table('users')->updateOrInsert(
                ['username' => 'camille_faurillo'],
                [
                    'employee_id' => $camilleEmp->employee_id ?? $camilleEmp->id,
                    'password_hash' => $defaultPassword,
                    'role' => 'hrmu_staff',
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
        // Link optional username alias emp_mpdo to Johann Chavez
        $johannEmp = DB::table('employees')->where('employee_no', 'MPDO-0003')->orWhere('employee_no', '105')->first();
        if ($johannEmp) {
            DB::table('users')->updateOrInsert(
                ['username' => 'emp_mpdo'],
                [
                    'employee_id' => $johannEmp->employee_id ?? $johannEmp->id,
                    'password_hash' => $defaultPassword,
                    'role' => 'employee',
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $this->command->info('✔ Seeded ' . count($personnelData) . ' employees, linked user accounts, and 2026 certified leave balances.');
    }
}
