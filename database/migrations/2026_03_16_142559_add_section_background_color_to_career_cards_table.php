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
        Schema::table('career_cards', function (Blueprint $table) {
            $table->string('section_background_color', 50)->nullable()->after('section_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('career_cards', function (Blueprint $table) {
            $table->dropColumn('section_background_color');
        });
    }
};
