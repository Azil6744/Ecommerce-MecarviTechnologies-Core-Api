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
        Schema::create('mission_vision_sections', function (Blueprint $table) {
            $table->id();
            $table->string('mission_title')->nullable();
            $table->string('vision_title')->nullable();
            $table->text('mission_description')->nullable();
            $table->text('vision_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mission_vision_sections');
    }
};

