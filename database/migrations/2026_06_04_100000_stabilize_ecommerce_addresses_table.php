<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecommerce_addresses', function (Blueprint $table) {
            if (! Schema::hasColumn('ecommerce_addresses', 'type')) {
                $table->string('type', 30)->default('shipping')->after('user_id');
            }

            if (! Schema::hasColumn('ecommerce_addresses', 'company')) {
                $table->string('company')->nullable()->after('last_name');
            }

            if (! Schema::hasColumn('ecommerce_addresses', 'address')) {
                $table->string('address')->nullable()->after('phone');
            }

            if (! Schema::hasColumn('ecommerce_addresses', 'zip_code')) {
                $table->string('zip_code', 20)->nullable()->after('state');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ecommerce_addresses', function (Blueprint $table) {
            foreach (['type', 'company', 'address', 'zip_code'] as $column) {
                if (Schema::hasColumn('ecommerce_addresses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
