<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('what_we_create_sections', function (Blueprint $table) {
            $table->string('heading_title')->nullable();
            $table->text('description')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('what_we_create_sections', function (Blueprint $table) {
            $table->dropColumn([
                'heading_title',
                'description',
                'button_text',
                'button_url'
            ]);
        });
    }
};
