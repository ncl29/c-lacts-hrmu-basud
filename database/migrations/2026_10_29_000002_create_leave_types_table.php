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
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id('leave_type_id');
            $table->string('code', 20)->unique(); // e.g. 'VL', 'SL', 'FL', 'SPL', 'WL'
            $table->string('type_name')->unique();
            $table->boolean('is_cumulative')->default(false);
            $table->boolean('is_commutable')->default(false);
            $table->decimal('annual_limit', 6, 3)->nullable(); // Up to 3 decimal places e.g. 15.000, 5.000, 3.000
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
