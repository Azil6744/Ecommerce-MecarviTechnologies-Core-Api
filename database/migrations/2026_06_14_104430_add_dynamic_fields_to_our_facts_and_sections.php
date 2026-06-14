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
        Schema::table('our_facts_sections', function (Blueprint $table) {
            $table->string('heading_main')->nullable();
            $table->string('heading_highlight')->nullable();
        });

        Schema::table('our_facts', function (Blueprint $table) {
            $table->string('signature')->nullable();
            $table->text('sublabel')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('our_facts_sections', function (Blueprint $table) {
            $table->dropColumn(['heading_main', 'heading_highlight']);
        });

        Schema::table('our_facts', function (Blueprint $table) {
            $table->dropColumn(['signature', 'sublabel']);
        });
    }
};
