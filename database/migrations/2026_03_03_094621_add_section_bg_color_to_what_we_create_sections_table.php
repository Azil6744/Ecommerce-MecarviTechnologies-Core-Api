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
        Schema::table('what_we_create_sections', function (Blueprint $table) {
            $table->string('section_bg_color')->nullable()->after('background_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('what_we_create_sections', function (Blueprint $table) {
            $table->dropColumn('section_bg_color');
        });
    }
};
