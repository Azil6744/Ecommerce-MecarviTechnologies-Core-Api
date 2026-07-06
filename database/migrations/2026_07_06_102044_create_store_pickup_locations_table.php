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
        Schema::create('store_pickup_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('store_type')->default('Company Store');
            $table->string('timezone')->default('Eastern Standard Time (EST)');
            $table->string('address');
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            $table->text('short_description')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('is_pickup_enabled')->default(true);
            $table->integer('pickup_preparation_time')->default(2);
            $table->string('pickup_preparation_unit')->default('hours');
            $table->double('max_pickup_radius')->default(10.0);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->json('weekly_schedule')->nullable();
            $table->json('special_hours')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_pickup_locations');
    }
};
