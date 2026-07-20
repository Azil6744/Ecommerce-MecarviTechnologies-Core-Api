<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecommerce_subscription_plans', function (Blueprint $table) {
            if (! Schema::hasColumn('ecommerce_subscription_plans', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (! Schema::hasColumn('ecommerce_subscription_plans', 'features')) {
                $table->json('features')->nullable()->after('members_limit');
            }
            if (! Schema::hasColumn('ecommerce_subscription_plans', 'badge')) {
                $table->string('badge')->nullable()->after('features');
            }
            if (! Schema::hasColumn('ecommerce_subscription_plans', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('badge');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ecommerce_subscription_plans', function (Blueprint $table) {
            foreach (['description', 'features', 'badge', 'sort_order'] as $column) {
                if (Schema::hasColumn('ecommerce_subscription_plans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
