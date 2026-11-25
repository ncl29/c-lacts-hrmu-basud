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
        Schema::create('employees', function (Blueprint $table) {
            $table->id('employee_id');
            $table->string('employee_no', 50)->unique(); // e.g. 'BASUD-EMP-001'
            $table->string('full_name');
            $table->string('position');
            $table->string('employment_type'); // e.g. 'Permanent', 'Casual', 'Elected'
            $table->unsignedBigInteger('office_id');
            $table->string('status', 30)->default('active'); // e.g. 'active', 'separated', 'retired'
            $table->timestamps();

            $table->foreign('office_id')
                ->references('office_id')
                ->on('offices')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
