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
        Schema::create('pay_periods', function (Blueprint $table) {
            $table->id('pay_period_id');
            $table->unsignedTinyInteger('month'); // 1 to 12
            $table->unsignedTinyInteger('half'); // 1 (1st-15th) or 2 (16th-end)
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->unique(['month', 'half', 'start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_periods');
    }
};
