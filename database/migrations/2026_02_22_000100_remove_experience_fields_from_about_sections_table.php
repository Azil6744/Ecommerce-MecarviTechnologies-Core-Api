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
        Schema::table('about_sections', function (Blueprint $table) {
            if (Schema::hasColumn('about_sections', 'experience_years')) {
                $table->dropColumn('experience_years');
            }

            if (Schema::hasColumn('about_sections', 'experience_description')) {
                $table->dropColumn('experience_description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('about_sections', function (Blueprint $table) {
            if (!Schema::hasColumn('about_sections', 'experience_years')) {
                $table->integer('experience_years')->nullable();
            }

            if (!Schema::hasColumn('about_sections', 'experience_description')) {
                $table->text('experience_description')->nullable();
            }
        });
    }
};
