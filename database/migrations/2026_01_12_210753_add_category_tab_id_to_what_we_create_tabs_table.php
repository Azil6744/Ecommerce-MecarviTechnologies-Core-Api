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
        Schema::table('what_we_create_tabs', function (Blueprint $table) {
            $table->foreignId('category_tab_id')->nullable()->after('id')->constrained('category_tabs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('what_we_create_tabs', function (Blueprint $table) {
            $table->dropForeign(['category_tab_id']);
            $table->dropColumn('category_tab_id');
        });
    }
};
