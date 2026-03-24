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
            // Drop fields that are not needed
            $columns = Schema::getColumnListing('contact_form_submissions');
            
            if (in_array('job_location', $columns)) {
                $table->dropColumn('job_location');
            }
            
            if (in_array('preferred_contact_method', $columns)) {
                $table->dropColumn('preferred_contact_method');
            }
            
            if (in_array('best_time_to_contact', $columns)) {
                $table->dropColumn('best_time_to_contact');
            }
            
            if (in_array('name', $columns)) {
                $table->dropColumn('name');
            }
            
            if (in_array('subject', $columns)) {
                $table->dropColumn('subject');
            }
            
            // Ensure first_name and last_name exist and are not nullable
            if (!in_array('first_name', $columns)) {
                $table->string('first_name')->after('id');
            } else {
                $table->string('first_name')->nullable(false)->change();
            }
            
            if (!in_array('last_name', $columns)) {
                $table->string('last_name')->after('first_name');
            } else {
                $table->string('last_name')->nullable(false)->change();
            }
            
            // Ensure company exists (nullable)
            if (!in_array('company', $columns)) {
                $table->string('company')->nullable()->after('email');
            }
            
            // Ensure email is not nullable
            $table->string('email')->nullable(false)->change();
            
            // Ensure phone is nullable
            if (in_array('phone', $columns)) {
                $table->string('phone')->nullable()->change();
            } else {
                $table->string('phone')->nullable()->after('email');
            }
            
            // Ensure message is not nullable
            $table->text('message')->nullable(false)->change();
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
