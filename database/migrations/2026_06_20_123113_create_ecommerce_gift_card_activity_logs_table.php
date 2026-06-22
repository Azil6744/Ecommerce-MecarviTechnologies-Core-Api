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
        Schema::create('ecommerce_gift_card_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('giftcard_id')->index();
            $table->string('action');
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->foreign('giftcard_id')->references('id')->on('ecommerce_gift_cards')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_gift_card_activity_logs');
    }
};
