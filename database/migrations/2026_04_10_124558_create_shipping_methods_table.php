<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->decimal('base_rate', 10, 2)->default(0);
            $table->string('estimated_days')->nullable(); // e.g. "3-5 days"
            $table->string('coverage')->nullable(); // e.g. "USA", "International"
            $table->boolean('is_active')->default(true);
            $table->decimal('free_shipping_threshold', 10, 2)->nullable();
            $table->json('settings')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};
