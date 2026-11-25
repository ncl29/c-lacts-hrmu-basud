<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Leave Record Releases / Control Number Sequence.
     * Control numbers for official Leave Records (CS Form 41) are strictly numeric
     * and increment sequentially based on how many records have been released.
     */
    public function up(): void
    {
        Schema::create('leave_record_releases', function (Blueprint $table) {
            $table->id('release_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedInteger('control_no')->unique(); // Sequential release counter: 1, 2, 3...
            $table->string('release_type', 50)->default('official_cardstock');
            $table->unsignedBigInteger('released_by')->nullable();
            $table->timestamp('released_at')->useCurrent();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')
                ->references('employee_id')
                ->on('employees')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('released_by')
                ->references('user_id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index(['employee_id', 'control_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_record_releases');
    }
};

