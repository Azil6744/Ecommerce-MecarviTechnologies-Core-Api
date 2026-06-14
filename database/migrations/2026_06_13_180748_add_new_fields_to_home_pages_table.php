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
        Schema::table('home_pages', function (Blueprint $table) {
            $table->string('top_label')->nullable();
            $table->string('secondary_button_text')->nullable();
            $table->string('secondary_button_url')->nullable();
            $table->string('trust_badge_1')->nullable();
            $table->string('trust_badge_2')->nullable();
            $table->string('trust_badge_3')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_pages', function (Blueprint $table) {
            $table->dropColumn([
                'top_label',
                'secondary_button_text',
                'secondary_button_url',
                'trust_badge_1',
                'trust_badge_2',
                'trust_badge_3'
            ]);
        });
    }
};
