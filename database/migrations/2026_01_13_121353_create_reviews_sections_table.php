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
        Schema::create('reviews_sections', function (Blueprint $table) {
            $table->id();
            $table->string('main_heading')->nullable();
            $table->string('average_rating')->nullable();
            $table->text('call_to_action_text')->nullable();
            $table->string('client_label')->nullable();
            $table->string('review_count')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->string('avatar_1')->nullable();
            $table->string('avatar_2')->nullable();
            $table->string('avatar_3')->nullable();
            $table->string('avatar_4')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews_sections');
    }
};
