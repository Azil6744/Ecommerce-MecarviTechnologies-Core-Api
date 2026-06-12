<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecommerce_gift_cards', function (Blueprint $table) {
            if (! Schema::hasColumn('ecommerce_gift_cards', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->index()->after('id');
            }

            if (! Schema::hasColumn('ecommerce_gift_cards', 'order_id')) {
                $table->unsignedBigInteger('order_id')->nullable()->index()->after('user_id');
            }

            if (! Schema::hasColumn('ecommerce_gift_cards', 'delivery_type')) {
                $table->string('delivery_type', 50)->nullable()->after('expires_at');
            }

            if (! Schema::hasColumn('ecommerce_gift_cards', 'message')) {
                $table->text('message')->nullable()->after('delivery_type');
            }

            if (! Schema::hasColumn('ecommerce_gift_cards', 'scheduled_for')) {
                $table->dateTime('scheduled_for')->nullable()->after('message');
            }

            if (! Schema::hasColumn('ecommerce_gift_cards', 'purchased_at')) {
                $table->dateTime('purchased_at')->nullable()->after('scheduled_for');
            }

            if (! Schema::hasColumn('ecommerce_gift_cards', 'redeemed_at')) {
                $table->dateTime('redeemed_at')->nullable()->after('purchased_at');
            }

            if (! Schema::hasColumn('ecommerce_gift_cards', 'currency')) {
                $table->string('currency', 10)->nullable()->default('USD')->after('redeemed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ecommerce_gift_cards', function (Blueprint $table) {
            foreach (['currency', 'redeemed_at', 'purchased_at', 'scheduled_for', 'message', 'delivery_type', 'order_id', 'user_id'] as $column) {
                if (Schema::hasColumn('ecommerce_gift_cards', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
