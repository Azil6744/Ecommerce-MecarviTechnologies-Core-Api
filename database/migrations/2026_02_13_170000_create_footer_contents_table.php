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
        Schema::create('footer_contents', function (Blueprint $table) {
            $table->id();
            // Contact Info section
            $table->string('contact_section_heading')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('hours_mon_fri')->nullable();
            $table->string('hours_sat')->nullable();
            $table->string('hours_sun_holidays')->nullable();
            $table->string('chat_title')->nullable();
            $table->string('chat_subtitle')->nullable();
            // Section headings for link groups
            $table->string('company_section_heading')->nullable();
            $table->string('policy_center_section_heading')->nullable();
            $table->string('our_brands_section_heading')->nullable();
            $table->string('social_links_section_heading')->nullable();
            // Social URLs
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('tiktok_url')->nullable();
            // Payment methods section
            $table->string('payment_methods_section_heading')->nullable();
            $table->text('copyright_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('footer_contents');
    }
};
