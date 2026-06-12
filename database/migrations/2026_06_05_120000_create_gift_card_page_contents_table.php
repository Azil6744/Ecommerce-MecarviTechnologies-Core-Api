<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_card_page_contents', function (Blueprint $table) {
            $table->id();
            $table->json('header')->nullable();
            $table->json('hero')->nullable();
            $table->json('perks')->nullable();
            $table->json('card_types_section')->nullable();
            $table->json('card_types')->nullable();
            $table->json('design_showcase')->nullable();
            $table->json('how_it_works')->nullable();
            $table->json('redeem_band')->nullable();
            $table->json('faq_section')->nullable();
            $table->json('faqs')->nullable();
            $table->json('support_section')->nullable();
            $table->json('bottom_cta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_card_page_contents');
    }
};
