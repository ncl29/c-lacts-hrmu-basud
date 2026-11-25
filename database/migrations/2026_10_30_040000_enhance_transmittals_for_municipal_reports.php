<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transmittals', function (Blueprint $table) {
            if (!Schema::hasColumn('transmittals', 'recipient_name')) {
                $table->string('recipient_name', 150)->nullable()->default('Mr. Jan Mark De Vera');
            }
            if (!Schema::hasColumn('transmittals', 'period_covered')) {
                $table->string('period_covered', 150)->nullable()->default('September 1-15, 2026');
            }
            if (!Schema::hasColumn('transmittals', 'signatory_name')) {
                $table->string('signatory_name', 150)->nullable()->default('VICKY C. ALVARO');
            }
            if (!Schema::hasColumn('transmittals', 'signatory_title')) {
                $table->string('signatory_title', 150)->nullable()->default('Administrative Officer IV-HRMO II');
            }
            if (!Schema::hasColumn('transmittals', 'notes')) {
                $table->text('notes')->nullable();
            }
        });

        Schema::table('transmittal_items', function (Blueprint $table) {
            if (!Schema::hasColumn('transmittal_items', 'employee_id')) {
                $table->unsignedBigInteger('employee_id')->nullable()->after('transmittal_id');
            }
            if (!Schema::hasColumn('transmittal_items', 'employee_name')) {
                $table->string('employee_name', 150)->nullable()->after('employee_id');
            }
            if (!Schema::hasColumn('transmittal_items', 'office_name')) {
                $table->string('office_name', 150)->nullable()->after('employee_name');
            }
            if (!Schema::hasColumn('transmittal_items', 'remarks')) {
                $table->string('remarks', 255)->nullable()->after('application_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transmittals', function (Blueprint $table) {
            $table->dropColumn(['recipient_name', 'period_covered', 'signatory_name', 'signatory_title', 'notes']);
        });

        Schema::table('transmittal_items', function (Blueprint $table) {
            $table->dropColumn(['employee_id', 'employee_name', 'office_name', 'remarks']);
        });
    }
};
