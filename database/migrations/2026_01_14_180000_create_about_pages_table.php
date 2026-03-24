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
        Schema::create('about_pages', function (Blueprint $table) {
            $table->id();
            
            // Hero Section
            $table->string('hero_background_image')->nullable();
            $table->string('title_part_1')->nullable();
            $table->string('title_part_2')->nullable();
            $table->text('description_1')->nullable();
            $table->text('description_2')->nullable();
            $table->string('hero_image')->nullable();
            
            // About the Founder Section
            $table->string('founder_title')->nullable();
            $table->text('founder_description')->nullable();
            
            // About our Company Section
            $table->string('company_title')->nullable();
            $table->text('company_description')->nullable();
            $table->string('company_image')->nullable();
            
            // Mission and Vision Section
            $table->string('mission_title')->nullable();
            $table->string('vision_title')->nullable();
            $table->text('mission_description')->nullable();
            $table->text('vision_description')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_pages');
    }
};

