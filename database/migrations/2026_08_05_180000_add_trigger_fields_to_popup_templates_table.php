<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('popup_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('popup_templates', 'trigger_type')) {
                $table->string('trigger_type')->default('event')->after('event_key');
            }
            if (!Schema::hasColumn('popup_templates', 'trigger_pages')) {
                $table->json('trigger_pages')->nullable()->after('trigger_type');
            }
            if (!Schema::hasColumn('popup_templates', 'display_frequency')) {
                $table->string('display_frequency')->default('every_time')->after('trigger_pages');
            }
        });
    }

    public function down(): void
    {
        Schema::table('popup_templates', function (Blueprint $table) {
            if (Schema::hasColumn('popup_templates', 'trigger_type')) {
                $table->dropColumn('trigger_type');
            }
            if (Schema::hasColumn('popup_templates', 'trigger_pages')) {
                $table->dropColumn('trigger_pages');
            }
            if (Schema::hasColumn('popup_templates', 'display_frequency')) {
                $table->dropColumn('display_frequency');
            }
        });
    }
};
