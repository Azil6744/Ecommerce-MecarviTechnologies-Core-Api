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
        Schema::table('features_sections', function (Blueprint $table) {
            $table->string('background_color')->nullable();
        });
        Schema::table('analytics_sections', function (Blueprint $table) {
            $table->string('background_color')->nullable();
        });
        Schema::table('chart_sections', function (Blueprint $table) {
            $table->string('background_color')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('features_sections', function (Blueprint $table) {
            $table->dropColumn('background_color');
        });
        Schema::table('analytics_sections', function (Blueprint $table) {
            $table->dropColumn('background_color');
        });
        Schema::table('chart_sections', function (Blueprint $table) {
            $table->dropColumn('background_color');
        });
    }
};
