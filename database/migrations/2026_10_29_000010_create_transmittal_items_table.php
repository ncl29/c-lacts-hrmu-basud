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
        Schema::create('transmittal_items', function (Blueprint $table) {
            $table->id('item_id');
            $table->unsignedBigInteger('transmittal_id');
            $table->unsignedBigInteger('application_id');
            $table->timestamps();

            $table->foreign('transmittal_id')
                ->references('transmittal_id')
                ->on('transmittals')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('application_id')
                ->references('application_id')
                ->on('leave_applications')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unique(['transmittal_id', 'application_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transmittal_items');
    }
};
