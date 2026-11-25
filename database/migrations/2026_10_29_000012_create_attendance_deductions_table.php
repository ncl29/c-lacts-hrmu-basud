<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Enforces Guardrail #1: Only pre-verified aggregate summaries from HR Staff.
     */
    public function up(): void
    {
        Schema::create('attendance_deductions', function (Blueprint $table) {
            $table->id('deduction_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('pay_period_id');
            $table->unsignedBigInteger('encoded_by'); // user_id of HR Staff
            $table->decimal('days_absent', 5, 3)->default(0.000);
            $table->unsignedSmallInteger('late_hours')->default(0);
            $table->unsignedSmallInteger('late_minutes')->default(0);
            $table->decimal('days_deducted', 6, 3)->default(0.000); // Computed decimal days via Starla Table IV
            $table->timestamp('encoded_at');
            $table->timestamps();

            $table->foreign('employee_id')
                ->references('employee_id')
                ->on('employees')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('pay_period_id')
                ->references('pay_period_id')
                ->on('pay_periods')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('encoded_by')
                ->references('user_id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->unique(['employee_id', 'pay_period_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_deductions');
    }
};
