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
        Schema::table('about_company_sections', function (Blueprint $table) {
            $table->string('left_background_color')->nullable()->after('company_image');
            $table->string('right_background_color')->nullable()->after('left_background_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('about_company_sections', function (Blueprint $table) {
            $table->dropColumn(['left_background_color', 'right_background_color']);
        });
    }
};

