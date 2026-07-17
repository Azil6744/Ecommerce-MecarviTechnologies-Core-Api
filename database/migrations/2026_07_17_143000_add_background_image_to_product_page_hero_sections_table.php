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
            if (!Schema::hasColumn('product_page_hero_sections', 'background_image')) {
                $table->string('background_image')->nullable()->after('hero_description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_page_hero_sections', function (Blueprint $table) {
            if (Schema::hasColumn('product_page_hero_sections', 'background_image')) {
                $table->dropColumn('background_image');
            }
        });
    }
};
