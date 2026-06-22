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
        Schema::create('ecommerce_gift_card_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('giftcard_id')->index();
            $table->unsignedBigInteger('old_owner_id')->nullable()->index();
            $table->unsignedBigInteger('new_owner_id')->nullable()->index();
            $table->string('old_owner_email');
            $table->string('new_owner_email');
            $table->text('transfer_reason')->nullable();
            $table->dateTime('transferred_at');
            $table->timestamps();

            $table->foreign('giftcard_id')->references('id')->on('ecommerce_gift_cards')->onDelete('cascade');
            $table->foreign('old_owner_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('new_owner_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_gift_card_transfers');
    }
};
