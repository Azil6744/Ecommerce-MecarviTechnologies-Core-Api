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
        Schema::table('career_cta_sections', function (Blueprint $table) {
            $table->string('background_color')->nullable()->after('button_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('career_cta_sections', function (Blueprint $table) {
            $table->dropColumn('background_color');
        });
    }
};
