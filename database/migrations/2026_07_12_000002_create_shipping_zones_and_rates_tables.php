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
        // Shipping Zones
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('regions')->nullable(); // list of countries/states (e.g. ['US', 'CA'])
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Shipping Rates
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained('shipping_zones')->onDelete('cascade');
            $table->string('name'); // e.g. "Standard Weight Rate", "Flat Rate"
            $table->string('rate_type')->default('flat'); // flat, weight_based, price_based
            $table->decimal('min_value', 10, 2)->default(0.00); // min weight or min price
            $table->decimal('max_value', 10, 2)->nullable(); // max weight or max price
            $table->decimal('rate_amount', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('shipping_zones');
    }
};
