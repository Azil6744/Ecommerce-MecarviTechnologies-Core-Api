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
        if (!Schema::hasTable('ecommerce_referrals')) {
            Schema::create('ecommerce_referrals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('referrer_id');
                $table->unsignedBigInteger('referred_id');
                $table->decimal('reward_amount_referrer', 10, 2)->default(0.00);
                $table->decimal('reward_amount_referee', 10, 2)->default(0.00);
                $table->timestamps();

                $table->foreign('referrer_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('referred_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_referrals');
    }
};
