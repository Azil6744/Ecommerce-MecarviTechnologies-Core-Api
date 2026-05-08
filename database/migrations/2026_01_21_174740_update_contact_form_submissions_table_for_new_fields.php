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
        $columns = Schema::getColumnListing('contact_form_submissions');
        
        // Drop each column separately for SQLite compatibility
        if (in_array('job_location', $columns)) {
            Schema::table('contact_form_submissions', function (Blueprint $table) {
                $table->dropColumn('job_location');
            });
        }
        
        if (in_array('preferred_contact_method', $columns)) {
            Schema::table('contact_form_submissions', function (Blueprint $table) {
                $table->dropColumn('preferred_contact_method');
            });
        }
        
        if (in_array('best_time_to_contact', $columns)) {
            Schema::table('contact_form_submissions', function (Blueprint $table) {
                $table->dropColumn('best_time_to_contact');
            });
        }
        
        if (in_array('name', $columns)) {
            Schema::table('contact_form_submissions', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
        
        if (in_array('subject', $columns)) {
            Schema::table('contact_form_submissions', function (Blueprint $table) {
                $table->dropColumn('subject');
            });
        }
        
        // Refresh column list after drops
        $columns = Schema::getColumnListing('contact_form_submissions');
        
        // Add/modify columns
        Schema::table('contact_form_submissions', function (Blueprint $table) use ($columns) {
            // Ensure first_name and last_name exist
            if (!in_array('first_name', $columns)) {
                $table->string('first_name')->after('id');
            }
            
            if (!in_array('last_name', $columns)) {
                $table->string('last_name')->after('first_name');
            }
            
            // Ensure company exists (nullable)
            if (!in_array('company', $columns)) {
                $table->string('company')->nullable()->after('email');
            }
            
            // Ensure phone exists (nullable)
            if (!in_array('phone', $columns)) {
                $table->string('phone')->nullable()->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_form_submissions', function (Blueprint $table) {
            // Add back the dropped fields if needed
            $table->string('job_location')->nullable();
            $table->string('preferred_contact_method')->nullable();
            $table->string('best_time_to_contact')->nullable();
            $table->string('name')->nullable();
            $table->string('subject')->nullable();
        });
    }
};
