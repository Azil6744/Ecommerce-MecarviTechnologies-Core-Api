<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecommerce_membership_benefits', function (Blueprint $table) {
            $columns = Schema::getColumnListing('ecommerce_membership_benefits');
            $has = fn (string $column) => in_array($column, $columns, true);

            if (! $has('benefit_type')) {
                $table->string('benefit_type')->default('percentage_discount')->after('description');
            }
            if (! $has('benefit_value')) {
                $table->decimal('benefit_value', 10, 2)->default(0)->after('benefit_type');
            }
            if (! $has('restrictions')) {
                $table->json('restrictions')->nullable()->after('benefit_value');
            }
            if (! $has('usage_limit')) {
                $table->unsignedInteger('usage_limit')->nullable()->after('restrictions');
            }
            if (! $has('reset_frequency')) {
                $table->string('reset_frequency')->nullable()->after('usage_limit');
            }
            if (! $has('eligible_websites')) {
                $table->json('eligible_websites')->nullable()->after('reset_frequency');
            }
            if (! $has('eligible_products')) {
                $table->json('eligible_products')->nullable()->after('eligible_websites');
            }
            if (! $has('eligible_categories')) {
                $table->json('eligible_categories')->nullable()->after('eligible_products');
            }
            if (! $has('stacking_rules')) {
                $table->json('stacking_rules')->nullable()->after('eligible_categories');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ecommerce_membership_benefits', function (Blueprint $table) {
            foreach ([
                'benefit_type', 'benefit_value', 'restrictions', 'usage_limit',
                'reset_frequency', 'eligible_websites', 'eligible_products',
                'eligible_categories', 'stacking_rules',
            ] as $column) {
                if (Schema::hasColumn('ecommerce_membership_benefits', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
