<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Ensure all 15 Civil Service leave options exist in leave_types table.
     */
    public function up(): void
    {
        $now = Carbon::now();

        $types = [
            [
                'code' => 'MONETIZATION',
                'type_name' => 'Monetization of Leave Credits',
                'is_cumulative' => false,
                'is_commutable' => true,
                'annual_limit' => null,
                'limit_period' => 'per_event',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'TERMINAL',
                'type_name' => 'Terminal Leave',
                'is_cumulative' => false,
                'is_commutable' => true,
                'annual_limit' => null,
                'limit_period' => 'per_event',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($types as $type) {
            DB::table('leave_types')->updateOrInsert(
                ['code' => $type['code']],
                $type
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('leave_types')->whereIn('code', ['MONETIZATION', 'TERMINAL'])->delete();
    }
};

