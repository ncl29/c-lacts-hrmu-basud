<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds for 2026 semi-monthly pay periods.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $year = 2026;
        $periods = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);

            // First half: Day 01 to Day 15
            $firstHalfStart = "{$year}-{$monthStr}-01";
            $firstHalfEnd = "{$year}-{$monthStr}-15";

            $periods[] = [
                'month' => $month,
                'half' => 1,
                'start_date' => $firstHalfStart,
                'end_date' => $firstHalfEnd,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Second half: Day 16 to Last Day of Month
            $lastDay = Carbon::create($year, $month, 1)->endOfMonth()->day;
            $secondHalfStart = "{$year}-{$monthStr}-16";
            $secondHalfEnd = "{$year}-{$monthStr}-{$lastDay}";

            $periods[] = [
                'month' => $month,
                'half' => 2,
                'start_date' => $secondHalfStart,
                'end_date' => $secondHalfEnd,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach ($periods as $period) {
            DB::table('pay_periods')->updateOrInsert(
                [
                    'month' => $period['month'],
                    'half' => $period['half'],
                    'start_date' => $period['start_date'],
                ],
                [
                    'end_date' => $period['end_date'],
                    'updated_at' => $period['updated_at'],
                    'created_at' => $now,
                ]
            );
        }

        $this->command->info('✔ Seeded ' . count($periods) . ' semi-monthly pay periods for year ' . $year . '.');
    }
}
