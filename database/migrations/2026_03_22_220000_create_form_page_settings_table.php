<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_page_settings', function (Blueprint $table) {
            $table->id();
            $table->string('page_slug')->unique();
            $table->string('contact_email')->nullable()->default('info@mecarvi.com');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_page_settings');
    }
};
