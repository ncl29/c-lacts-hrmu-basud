<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds accrual tracking columns to leave_records to strictly prevent duplicate monthly accruals
     * and support Table II manual prorated accruals for employees eligible partway through a month.
     */
    public function up(): void
    {
        Schema::table('leave_records', function (Blueprint $table) {
            $table->string('accrual_month', 7)->nullable()->after('particular')->index(); // e.g. '2026-11'
            $table->boolean('is_prorated')->default(false)->after('accrual_month'); // True if prorated via Table II
        });

        // Backfill existing 'Earned' rows if any
        $earnedRecords = DB::table('leave_records')
            ->where('particular', 'like', '%Earned%')
            ->get();

        foreach ($earnedRecords as $rec) {
            $month = null;
            try {
                // Try parsing period e.g. 'January 2026' or 'July 2026'
                $parsedDate = Carbon::parse($rec->period);
                $month = $parsedDate->format('Y-m');
            } catch (\Exception $e) {
                // Try from created_at
                if ($rec->created_at) {
                    $month = Carbon::parse($rec->created_at)->format('Y-m');
                }
            }

            if ($month) {
                DB::table('leave_records')
                    ->where('record_id', $rec->record_id)
                    ->update(['accrual_month' => $month]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_records', function (Blueprint $table) {
            $table->dropColumn(['accrual_month', 'is_prorated']);
        });
    }
};
