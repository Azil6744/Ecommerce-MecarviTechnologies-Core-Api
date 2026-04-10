<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('subject')->nullable();
            $table->string('category')->default('system'); // system, onboarding, orders, sales, promotional
            $table->text('preview_text')->nullable();
            $table->longText('body_html')->nullable();
            $table->string('status')->default('draft'); // draft, published
            $table->json('variables')->nullable(); // available merge variables
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
