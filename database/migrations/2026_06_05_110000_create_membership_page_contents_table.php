<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_page_contents', function (Blueprint $table) {
            $table->id();
            $table->json('hero')->nullable();
            $table->json('stats')->nullable();
            $table->json('plan_section')->nullable();
            $table->json('plans')->nullable();
            $table->json('benefits_section')->nullable();
            $table->json('benefits')->nullable();
            $table->json('bottom_cta')->nullable();
            $table->json('faq_section')->nullable();
            $table->json('faqs')->nullable();
            $table->json('support_section')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_page_contents');
    }
};
