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
        Schema::create('support_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_title')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('call_icon')->nullable();
            $table->string('call_title')->nullable();
            $table->text('call_description')->nullable();
            $table->string('call_phone')->nullable();
            $table->string('email_icon')->nullable();
            $table->string('email_title')->nullable();
            $table->text('email_description')->nullable();
            $table->string('email_address')->nullable();
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
        Schema::dropIfExists('support_sections');
    }
};
