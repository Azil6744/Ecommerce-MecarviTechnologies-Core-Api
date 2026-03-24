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
        Schema::table('social_links', function (Blueprint $table) {
            if (!Schema::hasColumn('social_links', 'platform_name')) {
                $table->string('platform_name');
            }
            if (!Schema::hasColumn('social_links', 'platform_url')) {
                $table->string('platform_url');
            }
            if (!Schema::hasColumn('social_links', 'icon')) {
                $table->string('icon')->nullable();
            }
            if (!Schema::hasColumn('social_links', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (!Schema::hasColumn('social_links', 'sort_order')) {
                $table->integer('sort_order')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('social_links', function (Blueprint $table) {
            $table->dropColumn(['platform_name', 'platform_url', 'icon', 'is_active', 'sort_order']);
        });
    }
};
