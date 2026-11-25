<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Links leave_records directly to leave_applications to ensure 100% duplicate prevention
     * and seamless status synchronizations across the application routing lifecycle.
     */
    public function up(): void
    {
        Schema::table('leave_records', function (Blueprint $table) {
            $table->unsignedBigInteger('application_id')->nullable()->after('employee_id');

            $table->foreign('application_id')
                ->references('application_id')
                ->on('leave_applications')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index('application_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_records', function (Blueprint $table) {
            $table->dropForeign(['application_id']);
            $table->dropColumn('application_id');
        });
    }
};
