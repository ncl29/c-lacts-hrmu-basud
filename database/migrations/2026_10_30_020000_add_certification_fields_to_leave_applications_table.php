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
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->boolean('is_certified')->default(false)->after('details_of_leave');
            $table->date('certified_as_of')->nullable()->after('is_certified');
            $table->decimal('certified_vl_earned', 8, 3)->nullable()->after('certified_as_of');
            $table->decimal('certified_vl_less', 8, 3)->nullable()->after('certified_vl_earned');
            $table->decimal('certified_vl_balance', 8, 3)->nullable()->after('certified_vl_less');
            $table->decimal('certified_sl_earned', 8, 3)->nullable()->after('certified_vl_balance');
            $table->decimal('certified_sl_less', 8, 3)->nullable()->after('certified_sl_earned');
            $table->decimal('certified_sl_balance', 8, 3)->nullable()->after('certified_sl_less');
            $table->unsignedBigInteger('certified_by_user_id')->nullable()->after('certified_sl_balance');
            $table->timestamp('certified_at')->nullable()->after('certified_by_user_id');

            $table->foreign('certified_by_user_id')
                ->references('user_id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropForeign(['certified_by_user_id']);
            $table->dropColumn([
                'is_certified',
                'certified_as_of',
                'certified_vl_earned',
                'certified_vl_less',
                'certified_vl_balance',
                'certified_sl_earned',
                'certified_sl_less',
                'certified_sl_balance',
                'certified_by_user_id',
                'certified_at',
            ]);
        });
    }
};
