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
        Schema::table('footer_contents', function (Blueprint $table) {
            $table->string('policy_section_heading')->nullable()->after('support_section_heading');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('footer_contents', function (Blueprint $table) {
            $table->dropColumn('policy_section_heading');
        });
    }
};
