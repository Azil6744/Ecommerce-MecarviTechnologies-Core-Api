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
        Schema::table('career_procedures', function (Blueprint $table) {
            $table->string('background_image_url')->nullable()->after('section_description');
            $table->string('background_color')->nullable()->after('background_image_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('career_procedures', function (Blueprint $table) {
            $table->dropColumn('background_image_url');
            $table->dropColumn('background_color');
        });
    }
};
