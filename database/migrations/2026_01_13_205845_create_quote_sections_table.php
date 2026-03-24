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
        Schema::create('quote_sections', function (Blueprint $table) {
            $table->id();
            $table->string('request_quote_title')->nullable();
            $table->string('request_quote_subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('button_text')->nullable();
            $table->string('title_1')->nullable();
            $table->text('paragraph_1')->nullable();
            $table->string('title_2')->nullable();
            $table->text('paragraph_2')->nullable();
            $table->string('image_1')->nullable();
            $table->string('image_2')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_sections');
    }
};
