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
        Schema::create('ecommerce_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('type'); // Deposit, Order Payment, Affiliate Earned, Refund
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_after', 10, 2)->nullable();
            $table->string('description')->nullable();
            $table->string('reference_id')->nullable();
            $table->string('status')->default('Completed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_wallet_transactions');
    }
};
