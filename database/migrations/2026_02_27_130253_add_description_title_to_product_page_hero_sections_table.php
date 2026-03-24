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
            $table->string('description_title', 255)->nullable()->after('hero_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_page_hero_sections', function (Blueprint $table) {
            $table->dropColumn('description_title');
        });
    }
};
