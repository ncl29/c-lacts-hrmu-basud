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
        Schema::create('starla_reference', function (Blueprint $table) {
            $table->id('starla_id');
            $table->string('table_ref', 50); // e.g. 'Table II', 'Table III', 'Table IV'
            $table->string('input_value', 100); // e.g. '15 mins', '1 hour', '15 days'
            $table->decimal('day_equivalent', 6, 3); // Discrete fraction to 3 decimals e.g. 0.031, 0.125, 0.625
            $table->unsignedBigInteger('validated_by')->nullable(); // user_id
            $table->date('validated_on')->nullable();
            $table->timestamps();

            $table->foreign('validated_by')
                ->references('user_id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->unique(['table_ref', 'input_value']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('starla_reference');
    }
};
