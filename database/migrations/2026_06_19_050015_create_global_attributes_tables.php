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
        Schema::create('global_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('dropdown');
            $table->text('description')->nullable();
            $table->string('pricing_mode')->default('per_item');
            $table->string('status')->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('global_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('global_attributes')->cascadeOnDelete();
            $table->string('name');
            $table->string('image_path')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->string('pricing_mode')->default('per_item');
            $table->string('status')->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_attribute_values');
        Schema::dropIfExists('global_attributes');
    }
};
