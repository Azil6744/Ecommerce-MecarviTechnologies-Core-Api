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
        Schema::table('quote_sections', function (Blueprint $table) {
            $table->string('background_image')->nullable()->after('image_2');
            $table->string('card_1_color', 20)->nullable()->after('background_image');
            $table->string('card_2_color', 20)->nullable()->after('card_1_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_sections', function (Blueprint $table) {
            $table->dropColumn(['background_image', 'card_1_color', 'card_2_color']);
        });
    }
};
