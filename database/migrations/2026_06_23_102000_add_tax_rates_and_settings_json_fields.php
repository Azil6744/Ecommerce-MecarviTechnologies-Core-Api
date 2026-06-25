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
        // 1. Create ecommerce_tax_rates table
        if (!Schema::hasTable('ecommerce_tax_rates')) {
            Schema::create('ecommerce_tax_rates', function (Blueprint $table) {
                $table->id();
                $table->string('state')->nullable();
                $table->string('country')->nullable();
                $table->decimal('rate', 10, 2)->default(0.00);
                $table->string('label')->default('Sales Tax');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Add JSON settings fields to site_settings table
        Schema::table('site_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('site_settings', 'loyalty_settings')) {
                $table->text('loyalty_settings')->nullable();
            }
            if (!Schema::hasColumn('site_settings', 'charity_settings')) {
                $table->text('charity_settings')->nullable();
            }
            if (!Schema::hasColumn('site_settings', 'tips_settings')) {
                $table->text('tips_settings')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_tax_rates');

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['loyalty_settings', 'charity_settings', 'tips_settings']);
        });
    }
};
