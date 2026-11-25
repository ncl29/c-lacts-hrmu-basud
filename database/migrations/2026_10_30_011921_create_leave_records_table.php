<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for official Civil Service Leave Record Table.
     * Matches the exact 11-column cardstock ledger used by HRMU Basud.
     * All numerical values are stored with full string precision to strictly avoid rounding errors.
     */
    public function up(): void
    {
        Schema::create('leave_records', function (Blueprint $table) {
            $table->id('record_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('period', 100); // Col 1: Month/Year covered (e.g., 'July 2026')
            $table->string('particular', 255); // Col 2: Particular (e.g. 'Earned', 'L (1 hr and 3 mins)', 'FL (1)')
            
            // Col 3-6: Vacation Leave Sub-columns
            $table->string('vl_earned', 50)->nullable(); // Col 3: VL Earned
            $table->string('vl_absence_with_pay', 50)->nullable(); // Col 4: VL Absence/Undertime with Pay
            $table->string('vl_balance', 50)->nullable(); // Col 5: VL Balance
            $table->string('vl_absence_without_pay', 50)->nullable(); // Col 6: VL Absence/Undertime without Pay
            
            // Col 7-10: Sick Leave Sub-columns
            $table->string('sl_earned', 50)->nullable(); // Col 7: SL Earned
            $table->string('sl_absence_with_pay', 50)->nullable(); // Col 8: SL Absence/Undertime with Pay
            $table->string('sl_balance', 50)->nullable(); // Col 9: SL Balance
            $table->string('sl_absence_without_pay', 50)->nullable(); // Col 10: SL Absence/Undertime without Pay
            
            // Col 11: Date & Action Taken on Application for Leave
            $table->text('date_action_taken')->nullable();
            
            $table->integer('row_order')->default(0);
            $table->unsignedBigInteger('encoded_by')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')
                ->references('employee_id')
                ->on('employees')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('encoded_by')
                ->references('user_id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index(['employee_id', 'row_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_records');
    }
};
