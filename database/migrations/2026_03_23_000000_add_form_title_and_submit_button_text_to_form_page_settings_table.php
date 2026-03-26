<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_page_settings', function (Blueprint $table) {
            $table->string('form_title')->nullable()->after('contact_email');
            $table->text('form_description')->nullable()->after('form_title');
            $table->string('submit_button_text')->nullable()->after('form_description');
        });
    }

    public function down(): void
    {
        Schema::table('form_page_settings', function (Blueprint $table) {
            $table->dropColumn(['form_title', 'form_description', 'submit_button_text']);
        });
    }
};
