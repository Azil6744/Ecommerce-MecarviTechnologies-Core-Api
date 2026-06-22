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
        Schema::create('ecommerce_gift_card_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('buyer_name');
            $table->string('buyer_email');
            $table->string('recipient_name');
            $table->string('recipient_email');
            $table->text('personal_message')->nullable();
            $table->decimal('giftcard_amount', 10, 2);
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->enum('order_status', [
                'Payment Pending',
                'Payment Confirmed',
                'Pending Gift Card Issue',
                'Gift Card Issued',
                'Gift Card Delivered',
                'Delivery Failed',
                'Cancelled',
                'Refunded'
            ])->default('Payment Pending');
            $table->dateTime('delivery_date')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_gift_card_orders');
    }
};
