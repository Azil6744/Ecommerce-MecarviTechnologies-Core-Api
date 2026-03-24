<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hours_of_operation', function (Blueprint $table) {
            $table->dropColumn(['sunday_status', 'public_holidays_status']);
        });
    }

    public function down(): void
    {
        Schema::table('hours_of_operation', function (Blueprint $table) {
            $table->string('sunday_status')->nullable()->after('sunday_hours');
            $table->string('public_holidays_status')->nullable()->after('public_holidays_hours');
        });
    }
};
