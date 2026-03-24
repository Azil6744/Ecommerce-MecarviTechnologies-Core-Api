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
        Schema::table('quote_form_submissions', function (Blueprint $table) {
            $table->longText('corporate_intake_payload')->nullable()->after('required_skills');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_form_submissions', function (Blueprint $table) {
            $table->dropColumn('corporate_intake_payload');
        });
    }
};
