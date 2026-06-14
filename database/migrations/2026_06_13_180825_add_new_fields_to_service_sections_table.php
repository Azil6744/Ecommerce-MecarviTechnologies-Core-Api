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
        Schema::table('service_sections', function (Blueprint $table) {
            $table->string('process_subtitle')->nullable();
            $table->string('process_title_1')->nullable();
            $table->string('process_title_2')->nullable();
            $table->text('process_description')->nullable();
            $table->json('process_checklist')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_sections', function (Blueprint $table) {
            $table->dropColumn([
                'process_subtitle',
                'process_title_1',
                'process_title_2',
                'process_description',
                'process_checklist'
            ]);
        });
    }
};
