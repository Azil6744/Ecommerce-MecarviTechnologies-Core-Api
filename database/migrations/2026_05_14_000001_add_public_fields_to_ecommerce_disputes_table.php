<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecommerce_disputes', function (Blueprint $table) {
            if (!Schema::hasColumn('ecommerce_disputes', 'email')) {
                $table->string('email')->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('ecommerce_disputes', 'phone')) {
                $table->string('phone', 40)->nullable()->after('email');
            }
            if (!Schema::hasColumn('ecommerce_disputes', 'amount')) {
                $table->decimal('amount', 12, 2)->nullable()->after('description');
            }
            if (!Schema::hasColumn('ecommerce_disputes', 'evidence')) {
                $table->json('evidence')->nullable()->after('amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ecommerce_disputes', function (Blueprint $table) {
            $drops = [];
            foreach (['email', 'phone', 'amount', 'evidence'] as $column) {
                if (Schema::hasColumn('ecommerce_disputes', $column)) {
                    $drops[] = $column;
                }
            }
            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
