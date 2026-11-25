<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StarlaReferenceSeeder extends Seeder
{
    /**
     * Run the database seeds for digitized CSC Starla conversion tables.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $records = [];

        // ---------------------------------------------------------------------
        // 1. Table IV - Working Hours (1 to 8 Hours)
        // ---------------------------------------------------------------------
        $hoursTable = [
            1 => 0.125,
            2 => 0.250,
            3 => 0.375,
            4 => 0.500,
            5 => 0.625,
            6 => 0.750,
            7 => 0.875,
            8 => 1.000,
        ];

        foreach ($hoursTable as $hour => $equiv) {
            $records[] = [
                'table_ref' => 'Table IV',
                'input_value' => $hour . ($hour === 1 ? ' hour' : ' hours'),
                'day_equivalent' => $equiv,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // ---------------------------------------------------------------------
        // 2. Table IV - Working Minutes (1 to 60 Minutes)
        // Exact discrete values matching HRMU Basud's printed Starla binder scan
        // ---------------------------------------------------------------------
        $minutesTable = [
            1 => 0.002, 2 => 0.004, 3 => 0.0006, 4 => 0.008, 5 => 0.010,
            6 => 0.012, 7 => 0.015, 8 => 0.017, 9 => 0.019, 10 => 0.021,
            11 => 0.023, 12 => 0.025, 13 => 0.027, 14 => 0.029, 15 => 0.031,
            16 => 0.033, 17 => 0.035, 18 => 0.037, 19 => 0.040, 20 => 0.042,
            21 => 0.044, 22 => 0.046, 23 => 0.048, 24 => 0.050, 25 => 0.052,
            26 => 0.054, 27 => 0.056, 28 => 0.058, 29 => 0.060, 30 => 0.062,
            31 => 0.065, 32 => 0.067, 33 => 0.069, 34 => 0.071, 35 => 0.073,
            36 => 0.075, 37 => 0.077, 38 => 0.079, 39 => 0.081, 40 => 0.083,
            41 => 0.085, 42 => 0.087, 43 => 0.090, 44 => 0.092, 45 => 0.094,
            46 => 0.096, 47 => 0.098, 48 => 0.100, 49 => 0.102, 50 => 0.104,
            51 => 0.106, 52 => 0.108, 53 => 0.110, 54 => 0.112, 55 => 0.115,
            56 => 0.117, 57 => 0.119, 58 => 0.121, 59 => 0.123, 60 => 0.125,
        ];

        foreach ($minutesTable as $min => $equiv) {
            $records[] = [
                'table_ref' => 'Table IV',
                'input_value' => $min . ($min === 1 ? ' min' : ' mins'),
                'day_equivalent' => $equiv,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // ---------------------------------------------------------------------
        // 3. Table II - Daily Leave Credits Earned (1 to 30 Days of Service)
        // ---------------------------------------------------------------------
        $dailyCreditsTable = [
            1 => 0.042, 2 => 0.083, 3 => 0.125, 4 => 0.167, 5 => 0.208,
            6 => 0.250, 7 => 0.292, 8 => 0.333, 9 => 0.375, 10 => 0.417,
            11 => 0.458, 12 => 0.500, 13 => 0.542, 14 => 0.583, 15 => 0.625,
            16 => 0.667, 17 => 0.708, 18 => 0.750, 19 => 0.792, 20 => 0.833,
            21 => 0.875, 22 => 0.917, 23 => 0.958, 24 => 1.000, 25 => 1.042,
            26 => 1.083, 27 => 1.125, 28 => 1.167, 29 => 1.208, 30 => 1.250,
        ];

        foreach ($dailyCreditsTable as $day => $equiv) {
            $records[] = [
                'table_ref' => 'Table II',
                'input_value' => $day . ($day === 1 ? ' day' : ' days'),
                'day_equivalent' => $equiv,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Note: Table III (LWOP) has been removed per HRMU Basud operational policy.
        // Absences and leave earnings are calculated using Table II (Daily Accruals) and Table IV (Hours/Minutes Tardiness).
        DB::table('starla_reference')->where('table_ref', 'Table III')->delete();

        // Idempotent bulk insertion in chunks
        foreach (array_chunk($records, 50) as $chunk) {
            foreach ($chunk as $row) {
                DB::table('starla_reference')->updateOrInsert(
                    [
                        'table_ref' => $row['table_ref'],
                        'input_value' => $row['input_value'],
                    ],
                    [
                        'day_equivalent' => $row['day_equivalent'],
                        'updated_at' => $row['updated_at'],
                        'created_at' => $now,
                    ]
                );
            }
        }

        $this->command->info('✔ Seeded ' . count($records) . ' Starla conversion records (Tables II & IV).');
    }
}
