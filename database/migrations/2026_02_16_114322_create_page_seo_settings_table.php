<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_seo_settings', function (Blueprint $table) {
            $table->id();
            $table->string('page_slug')->unique(); // e.g. "home", "about", "career", "contact", "faq", "quote", "service"
            $table->string('tab_name')->nullable();  // custom display name for the admin tab
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->text('seo_keywords')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_seo_settings');
    }
};
