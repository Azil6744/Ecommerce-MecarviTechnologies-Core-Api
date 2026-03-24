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
        Schema::table('product_items', function (Blueprint $table) {
            $table->string('card_title_one')->nullable()->after('video_url');
            $table->text('card_text_one')->nullable()->after('card_title_one');
            $table->string('card_title_two')->nullable()->after('card_text_one');
            $table->text('card_text_two')->nullable()->after('card_title_two');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_items', function (Blueprint $table) {
            $table->dropColumn(['card_title_one', 'card_text_one', 'card_title_two', 'card_text_two']);
        });
    }
};
