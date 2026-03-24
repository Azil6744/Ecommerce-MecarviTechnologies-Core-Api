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
        Schema::create('tab_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_title')->nullable();
            $table->text('section_description')->nullable();
            
            // Tab 1 fields
            $table->string('tab1_title')->nullable();
            $table->string('tab1_icon')->nullable();
            $table->text('tab1_content')->nullable();
            $table->json('tab1_features')->nullable();
            $table->string('tab1_image')->nullable();
            
            // Tab 2 fields
            $table->string('tab2_title')->nullable();
            $table->string('tab2_icon')->nullable();
            $table->text('tab2_content')->nullable();
            $table->json('tab2_features')->nullable();
            $table->string('tab2_image')->nullable();
            
            // Tab 3 fields
            $table->string('tab3_title')->nullable();
            $table->string('tab3_icon')->nullable();
            $table->text('tab3_content')->nullable();
            $table->json('tab3_features')->nullable();
            $table->string('tab3_image')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tab_sections');
    }
};
