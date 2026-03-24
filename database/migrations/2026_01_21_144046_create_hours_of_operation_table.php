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
        Schema::create('hours_of_operation', function (Blueprint $table) {
            $table->id();
            $table->string('section_title')->nullable();
            $table->string('category_title');
            $table->string('monday_friday_hours')->nullable();
            $table->string('saturday_hours')->nullable();
            $table->string('sunday_hours')->nullable();
            $table->string('sunday_status')->nullable(); // For some categories that use "status" instead of "hours"
            $table->string('public_holidays_hours')->nullable();
            $table->string('public_holidays_status')->nullable(); // For some categories that use "status" instead of "hours"
            $table->text('description_1')->nullable(); // Additional field
            $table->text('description_2')->nullable(); // Additional field
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
        Schema::dropIfExists('hours_of_operation');
    }
};
