<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds for Municipality of Basud offices.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $offices = [
            [
                'office_name' => 'Human Resource Management Unit (HRMU)',
                'is_routing_point' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'office_name' => 'Office of the Municipal Mayor',
                'is_routing_point' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'office_name' => 'Office of the Municipal Vice Mayor / Sangguniang Bayan',
                'is_routing_point' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'office_name' => 'Municipal Planning and Development Office (MPDO)',
                'is_routing_point' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'office_name' => 'Municipal Engineering Office (MEO)',
                'is_routing_point' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'office_name' => 'Municipal Health Office (MHO)',
                'is_routing_point' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'office_name' => 'Municipal Social Welfare and Development Office (MSWDO)',
                'is_routing_point' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'office_name' => 'Municipal Agriculture Office (MAO)',
                'is_routing_point' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'office_name' => 'Municipal Accounting Office',
                'is_routing_point' => false, // Receives hard copies outside system
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'office_name' => 'Municipal Budget Office (MBO)',
                'is_routing_point' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'office_name' => 'General Services Office (GSO)',
                'is_routing_point' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'office_name' => 'Municipal Treasury Office (MTO)',
                'is_routing_point' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'office_name' => 'Municipal Assessor\'s Office (MASSO)',
                'is_routing_point' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'office_name' => 'Municipal Civil Registrar (MCR)',
                'is_routing_point' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($offices as $office) {
            DB::table('offices')->updateOrInsert(
                ['office_name' => $office['office_name']],
                [
                    'is_routing_point' => $office['is_routing_point'],
                    'updated_at' => $office['updated_at'],
                    'created_at' => $now,
                ]
            );
        }

        $this->command->info('✔ Seeded ' . count($offices) . ' Municipality of Basud offices.');
    }
}
