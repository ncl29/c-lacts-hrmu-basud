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
        Schema::create('routing_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('office_id');
            $table->unsignedBigInteger('scanned_by'); // user_id
            $table->string('action', 50); // 'RECEIVED', 'DISPATCHED', 'RECOMMENDED', 'APPROVED_PHYSICAL', 'DISAPPROVED_PHYSICAL'
            $table->string('method', 50)->default('QR_SCAN'); // 'QR_SCAN', 'MANUAL_ENTRY', 'SYSTEM'
            $table->timestamp('scanned_at');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('application_id')
                ->references('application_id')
                ->on('leave_applications')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('office_id')
                ->references('office_id')
                ->on('offices')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('scanned_by')
                ->references('user_id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index(['application_id', 'scanned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routing_logs');
    }
};
