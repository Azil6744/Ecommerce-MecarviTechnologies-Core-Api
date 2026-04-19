<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecommerce_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('ecommerce_orders', 'company_name')) {
                $table->string('company_name')->nullable()->after('customer_name');
            }

            if (!Schema::hasColumn('ecommerce_orders', 'customer_phone')) {
                $table->string('customer_phone')->nullable()->after('customer_email');
            }

            if (!Schema::hasColumn('ecommerce_orders', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }

            if (!Schema::hasColumn('ecommerce_orders', 'metadata')) {
                $table->json('metadata')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ecommerce_orders', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('ecommerce_orders', 'company_name') ? 'company_name' : null,
                Schema::hasColumn('ecommerce_orders', 'customer_phone') ? 'customer_phone' : null,
                Schema::hasColumn('ecommerce_orders', 'notes') ? 'notes' : null,
                Schema::hasColumn('ecommerce_orders', 'metadata') ? 'metadata' : null,
            ]);

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
