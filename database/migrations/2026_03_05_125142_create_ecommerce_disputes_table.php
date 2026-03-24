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
        Schema::create('ecommerce_disputes', function (Blueprint $table) {
            $table->id();
            $table->string('dispute_number')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('order_number')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('type'); // E.g., Missing Items, Quality Issue, Delayed
            $table->string('status')->default('Open');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_disputes');
    }
};
