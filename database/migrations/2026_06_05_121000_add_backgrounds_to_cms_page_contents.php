<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_page_contents', function (Blueprint $table) {
            $table->json('backgrounds')->nullable()->after('id');
        });

        Schema::table('gift_card_page_contents', function (Blueprint $table) {
            $table->json('backgrounds')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('membership_page_contents', function (Blueprint $table) {
            $table->dropColumn('backgrounds');
        });

        Schema::table('gift_card_page_contents', function (Blueprint $table) {
            $table->dropColumn('backgrounds');
        });
    }
};
