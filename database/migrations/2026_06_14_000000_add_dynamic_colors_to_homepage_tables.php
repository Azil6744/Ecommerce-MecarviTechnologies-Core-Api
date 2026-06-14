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
        if (Schema::hasTable('home_pages')) {
            Schema::table('home_pages', function (Blueprint $table) {
                if (!Schema::hasColumn('home_pages', 'background_color')) {
                    $table->string('background_color', 50)->nullable();
                }
            });
        }

        if (Schema::hasTable('service_sections')) {
            Schema::table('service_sections', function (Blueprint $table) {
                if (!Schema::hasColumn('service_sections', 'card_background_color')) {
                    $table->string('card_background_color', 50)->nullable();
                }
            });
        }

        if (Schema::hasTable('what_we_create_sections')) {
            Schema::table('what_we_create_sections', function (Blueprint $table) {
                if (!Schema::hasColumn('what_we_create_sections', 'card_background_color')) {
                    $table->string('card_background_color', 50)->nullable();
                }
            });
        }

        if (Schema::hasTable('our_facts_sections')) {
            Schema::table('our_facts_sections', function (Blueprint $table) {
                if (!Schema::hasColumn('our_facts_sections', 'card_background_color')) {
                    $table->string('card_background_color', 50)->nullable();
                }
            });
        }

        if (Schema::hasTable('why_choose_us_sections')) {
            Schema::table('why_choose_us_sections', function (Blueprint $table) {
                if (!Schema::hasColumn('why_choose_us_sections', 'card_background_color')) {
                    $table->string('card_background_color', 50)->nullable();
                }
            });
        }

        if (Schema::hasTable('reviews_sections')) {
            Schema::table('reviews_sections', function (Blueprint $table) {
                if (!Schema::hasColumn('reviews_sections', 'background_color')) {
                    $table->string('background_color', 50)->nullable();
                }
                if (!Schema::hasColumn('reviews_sections', 'card_background_color')) {
                    $table->string('card_background_color', 50)->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('home_pages')) {
            Schema::table('home_pages', function (Blueprint $table) {
                $table->dropColumn(['background_color']);
            });
        }

        if (Schema::hasTable('service_sections')) {
            Schema::table('service_sections', function (Blueprint $table) {
                $table->dropColumn(['card_background_color']);
            });
        }

        if (Schema::hasTable('what_we_create_sections')) {
            Schema::table('what_we_create_sections', function (Blueprint $table) {
                $table->dropColumn(['card_background_color']);
            });
        }

        if (Schema::hasTable('our_facts_sections')) {
            Schema::table('our_facts_sections', function (Blueprint $table) {
                $table->dropColumn(['card_background_color']);
            });
        }

        if (Schema::hasTable('why_choose_us_sections')) {
            Schema::table('why_choose_us_sections', function (Blueprint $table) {
                $table->dropColumn(['card_background_color']);
            });
        }

        if (Schema::hasTable('reviews_sections')) {
            Schema::table('reviews_sections', function (Blueprint $table) {
                $table->dropColumn(['background_color', 'card_background_color']);
            });
        }
    }
};
