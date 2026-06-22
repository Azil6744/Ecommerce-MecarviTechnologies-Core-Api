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
            if (!Schema::hasColumn('site_settings', 'referral_commission_percentage')) {
                $table->decimal('referral_commission_percentage', 5, 2)->default(0.00);
            }
        });

        if (!Schema::hasTable('ecommerce_referral_commissions')) {
            Schema::create('ecommerce_referral_commissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('referrer_id');
                $table->unsignedBigInteger('referred_id');
                $table->unsignedBigInteger('order_id');
                $table->decimal('order_amount', 10, 2);
                $table->decimal('commission_percentage', 5, 2);
                $table->decimal('commission_amount', 10, 2);
                $table->string('status')->default('pending'); // pending, completed, cancelled
                $table->timestamp('payout_at')->nullable();
                $table->timestamps();

                $table->foreign('referrer_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('referred_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('order_id')->references('id')->on('ecommerce_orders')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_referral_commissions');

        Schema::table('site_settings', function (Blueprint $table) {
            if (Schema::hasColumn('site_settings', 'referral_commission_percentage')) {
                $table->dropColumn('referral_commission_percentage');
            }
        });
    }
};
