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
        Schema::create('ecommerce_gift_card_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('giftcard_id')->index();
            $table->enum('transaction_type', [
                'Issue',
                'Redemption',
                'Refund',
                'Manual Adjustment',
                'Transfer',
                'Expiration',
                'Re-activation'
            ]);
            $table->decimal('amount', 10, 2);
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('giftcard_id')->references('id')->on('ecommerce_gift_cards')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('ecommerce_orders')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_gift_card_transactions');
    }
};
