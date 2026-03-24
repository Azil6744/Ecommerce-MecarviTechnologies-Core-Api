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
        Schema::table('contact_form_submissions', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->string('job_location')->nullable()->after('phone');
            $table->string('preferred_contact_method')->nullable()->after('job_location');
            $table->string('best_time_to_contact')->nullable()->after('preferred_contact_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_form_submissions', function (Blueprint $table) {
            $table->dropColumn(['name', 'job_location', 'preferred_contact_method', 'best_time_to_contact']);
        });
    }
};
