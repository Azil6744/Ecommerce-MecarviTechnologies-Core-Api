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
        Schema::create('career_procedures', function (Blueprint $table) {
            $table->id();
            $table->string('section_title')->nullable();
            $table->text('section_description')->nullable();
            $table->string('heading')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('step_number')->default(1);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_procedures');
    }
};

