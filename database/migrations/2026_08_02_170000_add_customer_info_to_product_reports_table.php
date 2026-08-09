<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('product_reports', 'customer_name')) {
                $table->string('customer_name')->nullable();
            }
            if (!Schema::hasColumn('product_reports', 'customer_email')) {
                $table->string('customer_email')->nullable();
            }
            if (!Schema::hasColumn('product_reports', 'customer_phone')) {
                $table->string('customer_phone')->nullable();
            }
            if (!Schema::hasColumn('product_reports', 'purchase_location')) {
                $table->string('purchase_location')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_reports', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'customer_email', 'customer_phone', 'purchase_location']);
        });
    }
};
