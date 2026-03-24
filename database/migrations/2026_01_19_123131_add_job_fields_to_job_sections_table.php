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
        Schema::table('job_sections', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('job_sections', 'employment_type')) {
                $table->string('employment_type')->nullable();
            }
            if (!Schema::hasColumn('job_sections', 'experience_required')) {
                $table->string('experience_required')->nullable();
            }
            if (!Schema::hasColumn('job_sections', 'company_name')) {
                $table->string('company_name')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_sections', function (Blueprint $table) {
            $table->dropColumn(['employment_type', 'experience_required', 'company_name']);
        });
    }
};
