<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecommerce_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('ecommerce_orders', 'membership_id')) {
                $table->unsignedBigInteger('membership_id')->nullable()->after('user_id')->index();
            }

            if (! Schema::hasColumn('ecommerce_orders', 'membership_plan_name')) {
                $table->string('membership_plan_name')->nullable()->after('membership_id');
            }

            if (! Schema::hasColumn('ecommerce_orders', 'membership_discount_amount')) {
                $table->decimal('membership_discount_amount', 10, 2)->default(0)->after('discount_amount');
            }

            if (! Schema::hasColumn('ecommerce_orders', 'membership_benefits_snapshot')) {
                $table->json('membership_benefits_snapshot')->nullable()->after('membership_discount_amount');
            }

            if (! Schema::hasColumn('ecommerce_orders', 'membership_benefit_usage')) {
                $table->json('membership_benefit_usage')->nullable()->after('membership_benefits_snapshot');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ecommerce_orders', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('ecommerce_orders', 'membership_benefit_usage') ? 'membership_benefit_usage' : null,
                Schema::hasColumn('ecommerce_orders', 'membership_benefits_snapshot') ? 'membership_benefits_snapshot' : null,
                Schema::hasColumn('ecommerce_orders', 'membership_discount_amount') ? 'membership_discount_amount' : null,
                Schema::hasColumn('ecommerce_orders', 'membership_plan_name') ? 'membership_plan_name' : null,
                Schema::hasColumn('ecommerce_orders', 'membership_id') ? 'membership_id' : null,
            ]);

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
