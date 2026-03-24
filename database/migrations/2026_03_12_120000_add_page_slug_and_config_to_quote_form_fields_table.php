<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_form_fields', function (Blueprint $table) {
            $table->string('page_slug')->default('quote')->after('id');
            $table->json('config')->nullable()->after('options');
        });
    }

    public function down(): void
    {
        Schema::table('quote_form_fields', function (Blueprint $table) {
            $table->dropColumn(['page_slug', 'config']);
        });
    }
};
