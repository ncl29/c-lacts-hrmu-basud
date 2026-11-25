<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CSC entitlement period of each leave type:
     * 'annual'    = limit resets every calendar year
     * 'per_event' = limit applies per event (delivery, adoption, surgery, VAWC, disaster, injury, exam/thesis)
     */
    private array $perEvent = ['ML', 'PL', 'ADOPT', 'SLBW', 'VAWC', 'CALAMITY', 'REHAB', 'STUDY'];

    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->string('limit_period', 20)->default('annual')->after('annual_limit');
        });

        DB::table('leave_types')->whereIn('code', $this->perEvent)->update(['limit_period' => 'per_event']);
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn('limit_period');
        });
    }
};

