<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change the unique constraint on 'name' from global to scoped per page_slug.
     */
    public function up(): void
    {
        Schema::table('quote_form_fields', function (Blueprint $table) {
            // Drop the old global unique constraint on 'name'
            $table->dropUnique(['name']);

            // Add a composite unique constraint on 'name' + 'page_slug'
            $table->unique(['name', 'page_slug'], 'quote_form_fields_name_page_slug_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_form_fields', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('quote_form_fields_name_page_slug_unique');

            // Restore the old global unique constraint
            $table->unique('name');
        });
    }
};
