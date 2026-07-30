<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecommerce_addresses', function (Blueprint $table) {
            $table->string('first_name')->nullable()->change();
            $table->string('last_name')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('address_line_1')->nullable()->change();
            $table->string('postal_code')->nullable()->change();
            $table->string('country')->nullable()->change();
        });
    }

    public function down(): void
    {
        // No-op down migration for safety
    }
};
