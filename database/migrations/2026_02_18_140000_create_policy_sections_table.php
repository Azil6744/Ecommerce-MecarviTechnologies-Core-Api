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
        Schema::create('policy_sections', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('terms'); // terms, privacy, refund, etc.
            $table->string('hero_title')->nullable();
            $table->string('hero_subtitle')->nullable();
            $table->string('hero_background_image')->nullable();
            $table->json('sections')->nullable(); // [{ "title": "...", "content": "..." }, ...]
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_sections');
    }
};
