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
        Schema::table('support_sections', function (Blueprint $table) {
            if (Schema::hasColumn('support_sections', 'inquiry_form_title')) {
                $table->dropColumn('inquiry_form_title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_sections', function (Blueprint $table) {
            if (!Schema::hasColumn('support_sections', 'inquiry_form_title')) {
                $table->string('inquiry_form_title')->nullable()->after('description');
            }
        });
    }
};
