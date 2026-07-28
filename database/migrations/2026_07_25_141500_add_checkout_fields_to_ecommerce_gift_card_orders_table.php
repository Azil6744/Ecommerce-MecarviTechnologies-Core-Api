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
        Schema::table('ecommerce_gift_card_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('ecommerce_gift_card_orders', 'delivery_method')) {
                $table->string('delivery_method')->default('digital')->after('giftcard_amount');
            }
            if (!Schema::hasColumn('ecommerce_gift_card_orders', 'recipient_phone')) {
                $table->string('recipient_phone')->nullable()->after('delivery_method');
            }
            if (!Schema::hasColumn('ecommerce_gift_card_orders', 'address_line1')) {
                $table->string('address_line1')->nullable()->after('recipient_phone');
            }
            if (!Schema::hasColumn('ecommerce_gift_card_orders', 'address_line2')) {
                $table->string('address_line2')->nullable()->after('address_line1');
            }
            if (!Schema::hasColumn('ecommerce_gift_card_orders', 'city')) {
                $table->string('city')->nullable()->after('address_line2');
            }
            if (!Schema::hasColumn('ecommerce_gift_card_orders', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
            if (!Schema::hasColumn('ecommerce_gift_card_orders', 'zip_code')) {
                $table->string('zip_code')->nullable()->after('state');
            }
            if (!Schema::hasColumn('ecommerce_gift_card_orders', 'country')) {
                $table->string('country')->nullable()->after('zip_code');
            }
            if (!Schema::hasColumn('ecommerce_gift_card_orders', 'card_purpose')) {
                $table->string('card_purpose')->nullable()->after('country');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecommerce_gift_card_orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_method',
                'recipient_phone',
                'address_line1',
                'address_line2',
                'city',
                'state',
                'zip_code',
                'country',
                'card_purpose',
            ]);
        });
    }
};
