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
        Schema::table('mission_vision_sections', function (Blueprint $table) {
            $table->string('mission_background_color')->nullable()->after('vision_description');
            $table->string('vision_background_color')->nullable()->after('mission_background_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mission_vision_sections', function (Blueprint $table) {
            $table->dropColumn(['mission_background_color', 'vision_background_color']);
        });
    }
};

