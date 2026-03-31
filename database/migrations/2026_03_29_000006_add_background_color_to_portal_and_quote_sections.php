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
        Schema::table('portfolio_sections', function (Blueprint $table) {
            $table->string('background_color', 20)->nullable()->after('description');
        });

        Schema::table('quote_sections', function (Blueprint $table) {
            $table->string('background_color', 20)->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portfolio_sections', function (Blueprint $table) {
            $table->dropColumn('background_color');
        });

        Schema::table('quote_sections', function (Blueprint $table) {
            $table->dropColumn('background_color');
        });
    }
};
