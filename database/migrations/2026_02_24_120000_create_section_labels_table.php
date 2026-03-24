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
        Schema::create('section_labels', function (Blueprint $table) {
            $table->id();
            $table->string('page_slug');          // e.g. "home", "services", "products", "careers"
            $table->string('section_key');         // e.g. "hero", "about", "services" (the default/internal key)
            $table->string('custom_label');         // The admin-editable display name
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['page_slug', 'section_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_labels');
    }
};
