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
        Schema::table('why_choose_us_sections', function (Blueprint $table) {
            $table->json('bad_points')->nullable();
            $table->string('bottom_text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('why_choose_us_sections', function (Blueprint $table) {
            $table->dropColumn(['bad_points', 'bottom_text']);
        });
    }
};
