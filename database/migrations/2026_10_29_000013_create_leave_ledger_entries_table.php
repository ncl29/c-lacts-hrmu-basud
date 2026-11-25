<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Enforces Guardrail #5: Immutability and day-based fractional balances (3 decimal places).
     */
    public function up(): void
    {
        Schema::create('leave_ledger_entries', function (Blueprint $table) {
            $table->id('entry_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('leave_type_id');
            $table->unsignedBigInteger('deduction_id')->nullable();
            $table->unsignedBigInteger('application_id')->nullable();
            $table->string('entry_type', 50); // 'OPENING_BALANCE', 'MONTHLY_ACCRUAL', 'TARDINESS_DEDUCTION', 'ABSENCE_DEDUCTION', 'LEAVE_AVAILED', 'ADJUSTMENT'
            $table->decimal('days', 6, 3); // Credits or debits in days (3 decimal places)
            $table->decimal('balance_after', 7, 3); // Running balance in days
            $table->string('particular')->nullable(); // Description matching physical Leave Record Col 2
            $table->timestamp('posted_at');
            $table->timestamps();

            $table->foreign('employee_id')
                ->references('employee_id')
                ->on('employees')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('leave_type_id')
                ->references('leave_type_id')
                ->on('leave_types')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('deduction_id')
                ->references('deduction_id')
                ->on('attendance_deductions')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('application_id')
                ->references('application_id')
                ->on('leave_applications')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index(['employee_id', 'posted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_ledger_entries');
    }
};
