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
        Schema::table('product_page_hero_sections', function (Blueprint $table) {
            $table->string('section_bg_color')->nullable()->default('#ff6a00')->after('hero_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_page_hero_sections', function (Blueprint $table) {
            $table->dropColumn('section_bg_color');
        });
    }
};
