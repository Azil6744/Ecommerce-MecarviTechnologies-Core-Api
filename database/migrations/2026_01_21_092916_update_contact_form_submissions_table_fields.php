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
            // Add new fields as nullable first
            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('company')->nullable()->after('email');
        });
        
        // Update existing data - split name into first_name and last_name
        \DB::table('contact_form_submissions')->get()->each(function ($record) {
            $nameParts = explode(' ', $record->name ?? '', 2);
            $firstName = $nameParts[0] ?? 'Unknown';
            $lastName = $nameParts[1] ?? '';
            
            \DB::table('contact_form_submissions')
                ->where('id', $record->id)
                ->update([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ]);
        });
        
        // SQLite requires separate schema modifications for dropColumn
        Schema::table('contact_form_submissions', function (Blueprint $table) {
            $table->dropColumn('name');
        });
        
        Schema::table('contact_form_submissions', function (Blueprint $table) {
            $table->dropColumn('subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_form_submissions', function (Blueprint $table) {
            // Add back old fields
            $table->string('name')->after('id');
            $table->string('subject')->after('email');
            
            // Drop new fields
            $table->dropColumn(['first_name', 'last_name', 'company']);
        });
    }
};
