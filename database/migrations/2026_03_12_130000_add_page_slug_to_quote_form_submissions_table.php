<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_form_submissions', function (Blueprint $table) {
            $table->string('page_slug')->default('quote')->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('quote_form_submissions', function (Blueprint $table) {
            $table->dropColumn('page_slug');
        });
    }
};
