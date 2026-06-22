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
        Schema::table('site_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('site_settings', 'referral_reward_referrer')) {
                $table->decimal('referral_reward_referrer', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('site_settings', 'referral_reward_referee')) {
                $table->decimal('referral_reward_referee', 10, 2)->default(0.00);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('site_settings', 'referral_reward_referrer')) {
                $columns[] = 'referral_reward_referrer';
            }
            if (Schema::hasColumn('site_settings', 'referral_reward_referee')) {
                $columns[] = 'referral_reward_referee';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
