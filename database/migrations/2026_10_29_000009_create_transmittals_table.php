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
        Schema::create('transmittals', function (Blueprint $table) {
            $table->id('transmittal_id');
            $table->string('transmittal_no', 60)->unique(); // e.g. 'TR-2026-10-0042'
            $table->unsignedBigInteger('office_id'); // Destination Office
            $table->unsignedBigInteger('prepared_by'); // user_id
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->foreign('office_id')
                ->references('office_id')
                ->on('offices')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('prepared_by')
                ->references('user_id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transmittals');
    }
};
