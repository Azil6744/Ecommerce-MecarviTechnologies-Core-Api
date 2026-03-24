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
        Schema::create('product_page_hero_sections', function (Blueprint $table) {
            $table->id();
            $table->string('hero_title')->nullable();
            $table->text('hero_description')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();
        });

        Schema::create('product_tabs', function (Blueprint $table) {
            $table->id();
            $table->string('tab_name');
            $table->integer('order')->default(0);
            $table->string('layout_type')->default('standard'); // standard, image_left, image_right, etc.
            $table->timestamps();
        });

        Schema::create('product_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_tab_id')->constrained('product_tabs')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('video_url')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_items');
        Schema::dropIfExists('product_tabs');
        Schema::dropIfExists('product_page_hero_sections');
    }
};
