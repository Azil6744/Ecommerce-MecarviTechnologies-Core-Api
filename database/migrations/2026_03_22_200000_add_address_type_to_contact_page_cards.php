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
        Schema::table('contact_page_cards', function (Blueprint $table) {
            $table->string('address_type')->nullable()->default('other')->after('country'); // 'us' or 'other'
            $table->string('us_state')->nullable()->after('address_type'); // US state name for US addresses
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_page_cards', function (Blueprint $table) {
            $table->dropColumn(['address_type', 'us_state']);
        });
    }
};
