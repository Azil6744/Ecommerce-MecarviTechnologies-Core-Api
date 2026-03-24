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
        Schema::create('about_sections', function (Blueprint $table) {
            $table->id();
            $table->string('main_title')->nullable();
            $table->text('main_description')->nullable();
            $table->string('background_image')->nullable();
            
            // Tab 1 fields
            $table->string('tab1_title')->nullable();
            $table->string('tab1_subtitle')->nullable();
            $table->text('tab1_description')->nullable();
            $table->string('tab1_image')->nullable();
            
            // Tab 2 fields
            $table->string('tab2_title')->nullable();
            $table->string('tab2_subtitle')->nullable();
            $table->text('tab2_description')->nullable();
            $table->string('tab2_image')->nullable();
            
            // Experience fields
            $table->integer('experience_years')->nullable();
            $table->text('experience_description')->nullable();
            
            // About images
            $table->string('about_image_1')->nullable();
            $table->string('about_image_2')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_sections');
    }
};

