<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Salary as declared by the employee at filing time (CS Form 6, item 5).
     * Informational snapshot only; never used in any computation.
     */
    public function up(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->decimal('declared_salary', 12, 2)->nullable()->after('date_of_filing');
        });
    }

    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropColumn('declared_salary');
        });
    }
};

