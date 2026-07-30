<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make referred_id nullable (new users registered via Central Auth may not exist
     * in the Ecommerce DB yet) and add referred_email for cross-system tracking.
     */
    public function up(): void
    {
        Schema::table('ecommerce_referrals', function (Blueprint $table) {
            // Make referred_id nullable so we can record referrals before the user
            // is synced into the Ecommerce database.
            $table->unsignedBigInteger('referred_id')->nullable()->change();

            // Store the referred user's email for cross-system lookups.
            if (!Schema::hasColumn('ecommerce_referrals', 'referred_email')) {
                $table->string('referred_email', 255)->nullable()->after('referred_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecommerce_referrals', function (Blueprint $table) {
            $table->unsignedBigInteger('referred_id')->nullable(false)->change();

            if (Schema::hasColumn('ecommerce_referrals', 'referred_email')) {
                $table->dropColumn('referred_email');
            }
        });
    }
};
