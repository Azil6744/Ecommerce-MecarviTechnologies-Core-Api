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
        Schema::create('job_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_title')->nullable();
            $table->text('section_description')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('employment_type')->nullable();
            $table->string('experience_required')->nullable();
            $table->string('company_name')->nullable();
            $table->string('image')->nullable();
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
        Schema::dropIfExists('job_sections');
    }
};
