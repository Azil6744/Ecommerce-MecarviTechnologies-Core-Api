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
        Schema::table('ecommerce_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('ecommerce_orders', 'pickup_location_id')) {
                $table->foreignId('pickup_location_id')
                    ->nullable()
                    ->after('shipping_method')
                    ->constrained('store_pickup_locations')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecommerce_orders', function (Blueprint $table) {
            if (Schema::hasColumn('ecommerce_orders', 'pickup_location_id')) {
                $table->dropForeign(['pickup_location_id']);
                $table->dropColumn('pickup_location_id');
            }
        });
    }
};
