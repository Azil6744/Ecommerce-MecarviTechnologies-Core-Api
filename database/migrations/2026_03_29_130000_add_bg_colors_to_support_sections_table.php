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
        Schema::table('support_sections', function (Blueprint $table) {
            $table->string('quick_support_bg_color')->nullable()->after('description');
            $table->string('inquiry_form_bg_color')->nullable()->after('quick_support_bg_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_sections', function (Blueprint $table) {
            $table->dropColumn(['quick_support_bg_color', 'inquiry_form_bg_color']);
        });
    }
};
