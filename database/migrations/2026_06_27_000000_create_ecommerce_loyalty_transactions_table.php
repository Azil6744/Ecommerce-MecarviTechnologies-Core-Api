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
        if (!Schema::hasTable('ecommerce_loyalty_transactions')) {
            Schema::create('ecommerce_loyalty_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('order_id')->nullable();
                $table->string('transaction_type'); // earned, redeemed, reversed, expired, manual_added, manual_removed, bonus
                $table->integer('points'); // positive or negative points
                $table->decimal('dollar_value', 10, 2)->default(0.00);
                $table->string('status')->default('available'); // pending, available, redeemed, reversed, expired
                $table->text('reason')->nullable();
                $table->unsignedBigInteger('admin_id')->nullable();
                $table->timestamp('expiration_date')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('order_id')->references('id')->on('ecommerce_orders')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_loyalty_transactions');
    }
};
