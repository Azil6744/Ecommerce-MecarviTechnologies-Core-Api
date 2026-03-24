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
        Schema::create('service_how_it_works_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('section_id');
            $table->string('title')->nullable();
            $table->text('short_description')->nullable();
            $table->text('full_description')->nullable();
            $table->string('image_url')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->foreign('section_id')
                ->references('id')
                ->on('service_how_it_works_sections')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_how_it_works_items');
    }
};
