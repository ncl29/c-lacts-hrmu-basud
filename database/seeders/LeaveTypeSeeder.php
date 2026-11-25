<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds for CSC leave categories.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $leaveTypes = [
            [
                'code' => 'VL',
                'type_name' => 'Vacation Leave',
                'is_cumulative' => true,
                'is_commutable' => true,
                'annual_limit' => 15.000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SL',
                'type_name' => 'Sick Leave',
                'is_cumulative' => true,
                'is_commutable' => true,
                'annual_limit' => 15.000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'FL',
                'type_name' => 'Mandatory / Forced Leave',
                'is_cumulative' => false,
                'is_commutable' => false,
                'annual_limit' => 5.000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SPL',
                'type_name' => 'Special Privilege Leave',
                'is_cumulative' => false,
                'is_commutable' => false,
                'annual_limit' => 3.000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'WL',
                'type_name' => 'Wellness Leave',
                'is_cumulative' => false,
                'is_commutable' => false,
                'annual_limit' => 5.000, // CSC MC No. 01, s. 2026 / Res. No. 2501292
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'ML',
                'type_name' => 'Maternity Leave',
                'is_cumulative' => false,
                'is_commutable' => false,
                'annual_limit' => 105.000, // RA 11210
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'PL',
                'type_name' => 'Paternity Leave',
                'is_cumulative' => false,
                'is_commutable' => false,
                'annual_limit' => 7.000, // RA 8187
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SOLO',
                'type_name' => 'Solo Parent Leave',
                'is_cumulative' => false,
                'is_commutable' => false,
                'annual_limit' => 7.000, // RA 8972 / RA 11861
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'VAWC',
                'type_name' => '10-Day VAWC Leave',
                'is_cumulative' => false,
                'is_commutable' => false,
                'annual_limit' => 10.000, // RA 9262
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'STUDY',
                'type_name' => 'Study Leave',
                'is_cumulative' => false,
                'is_commutable' => false,
                'annual_limit' => 180.000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'REHAB',
                'type_name' => 'Rehabilitation Privilege',
                'is_cumulative' => false,
                'is_commutable' => false,
                'annual_limit' => 180.000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SLBW',
                'type_name' => 'Special Leave Benefits for Women',
                'is_cumulative' => false,
                'is_commutable' => false,
                'annual_limit' => 60.000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'ADOPT',
                'type_name' => 'Adoption Leave',
                'is_cumulative' => false,
                'is_commutable' => false,
                'annual_limit' => 60.000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CALAMITY',
                'type_name' => 'Special Emergency / Calamity Leave',
                'is_cumulative' => false,
                'is_commutable' => false,
                'annual_limit' => 5.000, // CSC MC No. 2, s. 2012
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'OTHER',
                'type_name' => 'Others (Specify)',
                'is_cumulative' => false,
                'is_commutable' => false,
                'annual_limit' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($leaveTypes as $type) {
            DB::table('leave_types')->updateOrInsert(
                ['type_name' => $type['type_name']],
                [
                    'code' => $type['code'],
                    'is_cumulative' => $type['is_cumulative'],
                    'is_commutable' => $type['is_commutable'],
                    'annual_limit' => $type['annual_limit'],
                    'limit_period' => in_array($type['code'], ['ML', 'PL', 'ADOPT', 'SLBW', 'VAWC', 'CALAMITY', 'REHAB', 'STUDY']) ? 'per_event' : 'annual',
                    'updated_at' => $type['updated_at'],
                    'created_at' => $now,
                ]
            );
        }

        $this->command->info('✔ Seeded ' . count($leaveTypes) . ' Civil Service leave types.');
    }
}
