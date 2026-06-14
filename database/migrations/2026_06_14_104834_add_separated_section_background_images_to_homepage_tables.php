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
        if (Schema::hasTable('service_sections')) {
            Schema::table('service_sections', function (Blueprint $table) {
                if (!Schema::hasColumn('service_sections', 'process_background_image')) {
                    $table->string('process_background_image', 255)->nullable();
                }
            });
        }

        if (Schema::hasTable('what_we_create_sections')) {
            Schema::table('what_we_create_sections', function (Blueprint $table) {
                if (!Schema::hasColumn('what_we_create_sections', 'grid_background_image')) {
                    $table->string('grid_background_image', 255)->nullable();
                }
            });
        }

        if (Schema::hasTable('our_facts_sections')) {
            Schema::table('our_facts_sections', function (Blueprint $table) {
                if (!Schema::hasColumn('our_facts_sections', 'timeline_background_image')) {
                    $table->string('timeline_background_image', 255)->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('service_sections')) {
            Schema::table('service_sections', function (Blueprint $table) {
                $table->dropColumn(['process_background_image']);
            });
        }

        if (Schema::hasTable('what_we_create_sections')) {
            Schema::table('what_we_create_sections', function (Blueprint $table) {
                $table->dropColumn(['grid_background_image']);
            });
        }

        if (Schema::hasTable('our_facts_sections')) {
            Schema::table('our_facts_sections', function (Blueprint $table) {
                $table->dropColumn(['timeline_background_image']);
            });
        }
    }
};
