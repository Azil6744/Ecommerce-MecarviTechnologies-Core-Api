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
        Schema::create('ecommerce_gift_cards', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('recipient_name');
            $table->string('recipient_email');
            $table->string('sender_name')->nullable();
            $table->decimal('initial_balance', 10, 2);
            $table->decimal('current_balance', 10, 2);
            $table->string('status')->default('Active'); // Active, Redeemed, Expired
            $table->date('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_gift_cards');
    }
};
