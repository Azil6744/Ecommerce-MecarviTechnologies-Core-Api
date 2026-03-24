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
        if (!Schema::hasColumn('tab_sections', 'background_color')) {
            Schema::table('tab_sections', function (Blueprint $table) {
                $table->string('background_color')->nullable()->after('section_description');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tab_sections', 'background_color')) {
            Schema::table('tab_sections', function (Blueprint $table) {
                $table->dropColumn('background_color');
            });
        }
    }
};
