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
        Schema::create('service_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_heading')->nullable();
            $table->string('bg_image')->nullable();
            $table->string('small_text')->nullable();
            $table->string('main_heading')->nullable();
            $table->string('outlined_heading')->nullable();
            $table->text('description')->nullable();
            $table->string('background_text')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_pages');
    }
};
