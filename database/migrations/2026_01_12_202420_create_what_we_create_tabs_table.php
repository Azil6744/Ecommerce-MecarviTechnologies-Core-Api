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
        Schema::create('what_we_create_tabs', function (Blueprint $table) {
            $table->id();
            $table->string('tag_label')->nullable();
            $table->string('main_heading')->nullable();
            $table->text('description')->nullable();
            $table->json('features')->nullable(); // Array of feature strings
            $table->string('button_text')->nullable();
            $table->string('image_1')->nullable();
            $table->string('image_2')->nullable();
            $table->string('image_3')->nullable();
            $table->integer('order')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('what_we_create_tabs');
    }
};
