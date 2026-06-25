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
        // 1. Add loyalty points to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'loyalty_points')) {
                $table->integer('loyalty_points')->default(0)->after('wallet_balance');
            }
        });

        // 2. Add loyalty points price to products table
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'loyalty_points_price')) {
                $table->integer('loyalty_points_price')->nullable()->after('price');
            }
        });

        // 3. Add fields to ecommerce_orders table
        Schema::table('ecommerce_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('ecommerce_orders', 'tip_amount')) {
                $table->decimal('tip_amount', 10, 2)->default(0.00)->after('tax_amount');
            }
            if (!Schema::hasColumn('ecommerce_orders', 'donation_amount')) {
                $table->decimal('donation_amount', 10, 2)->default(0.00)->after('tip_amount');
            }
            if (!Schema::hasColumn('ecommerce_orders', 'loyalty_points_earned')) {
                $table->integer('loyalty_points_earned')->default(0)->after('donation_amount');
            }
            if (!Schema::hasColumn('ecommerce_orders', 'loyalty_points_redeemed')) {
                $table->integer('loyalty_points_redeemed')->default(0)->after('loyalty_points_earned');
            }
        });

        // 4. Add fields to site_settings table
        Schema::table('site_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('site_settings', 'tax_rate')) {
                $table->decimal('tax_rate', 10, 2)->default(10.00)->after('theme_secondary_color');
            }
            if (!Schema::hasColumn('site_settings', 'tax_enabled')) {
                $table->boolean('tax_enabled')->default(true)->after('tax_rate');
            }
            if (!Schema::hasColumn('site_settings', 'loyalty_points_earned_per_unit_price')) {
                $table->decimal('loyalty_points_earned_per_unit_price', 10, 2)->default(50.00)->after('tax_enabled');
            }
            if (!Schema::hasColumn('site_settings', 'loyalty_points_earned_points')) {
                $table->integer('loyalty_points_earned_points')->default(2)->after('loyalty_points_earned_per_unit_price');
            }
            if (!Schema::hasColumn('site_settings', 'charity_name')) {
                $table->string('charity_name')->default('Red Cross')->after('loyalty_points_earned_points');
            }
            if (!Schema::hasColumn('site_settings', 'charity_donation_enabled')) {
                $table->boolean('charity_donation_enabled')->default(true)->after('charity_name');
            }
            if (!Schema::hasColumn('site_settings', 'charity_default_amount')) {
                $table->decimal('charity_default_amount', 10, 2)->default(1.00)->after('charity_donation_enabled');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'tax_rate', 'tax_enabled',
                'loyalty_points_earned_per_unit_price', 'loyalty_points_earned_points',
                'charity_name', 'charity_donation_enabled', 'charity_default_amount'
            ]);
        });

        Schema::table('ecommerce_orders', function (Blueprint $table) {
            $table->dropColumn([
                'tip_amount', 'donation_amount', 'loyalty_points_earned', 'loyalty_points_redeemed'
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['loyalty_points_price']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['loyalty_points']);
        });
    }
};
