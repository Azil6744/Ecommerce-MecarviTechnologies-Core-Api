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
        Schema::create('service_how_it_works_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('short_description')->nullable();
            $table->text('full_description')->nullable();
            $table->string('background_image_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_how_it_works_sections');
    }
};
