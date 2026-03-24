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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->text('review_quote')->nullable();
            $table->string('avatar')->nullable();
            $table->string('name')->nullable();
            $table->string('designation')->nullable();
            $table->decimal('rating', 2, 1)->nullable(); // e.g., 4.0, 4.5, 5.0
            $table->integer('order')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
