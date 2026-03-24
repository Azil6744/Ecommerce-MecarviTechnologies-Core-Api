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
            $table->string('tab_bar_color')->nullable()->after('section_bg_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('what_we_create_sections', function (Blueprint $table) {
            $table->dropColumn('tab_bar_color');
        });
    }
};
