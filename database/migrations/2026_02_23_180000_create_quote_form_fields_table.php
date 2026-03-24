<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_form_fields', function (Blueprint $table) {
            $table->id();
            $table->string('section');          // e.g. "Company", "Scope", "Signage"
            $table->string('label');            // Display label
            $table->string('name')->unique();   // Unique field key
            $table->string('type');             // text, email, number, tel, select, radio, checkbox, textarea, file
            $table->json('options')->nullable(); // Array of options for select/radio/checkbox
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('placeholder')->nullable();
            $table->integer('grid_cols')->default(1); // 1 = full width, 2 = half width (in 2-col grid)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_form_fields');
    }
};
