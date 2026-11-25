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
        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id('application_id');
            $table->string('reference_code', 60)->unique(); // e.g. 'LV-2026-0001' or 'CLACTS-REF-2026-0001'
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('leave_type_id');
            $table->string('other_leave_type', 150)->nullable(); // User-specified other leave type
            $table->date('date_from');
            $table->date('date_to');
            $table->date('date_of_filing')->nullable();
            $table->decimal('days_requested', 5, 3); // 3 decimal places e.g. 3.000, 0.500
            $table->string('status', 50)->default('Pending'); // 'Pending', 'Routed', 'Approved', 'Disapproved', 'Cancelled'
            $table->boolean('commutation_requested')->default(false);
            $table->string('details_type', 50)->nullable(); // 'within_ph', 'abroad', 'in_hospital', 'out_patient', 'women_illness', etc.
            $table->text('details_specify')->nullable(); // Specified location or illness
            $table->text('details_of_leave')->nullable(); // Consolidated details summary
            $table->unsignedBigInteger('current_location_office_id')->nullable();
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

            $table->foreign('current_location_office_id')
                ->references('office_id')
                ->on('offices')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_applications');
    }
};
