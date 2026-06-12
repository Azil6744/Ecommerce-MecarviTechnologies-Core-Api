<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecommerce_returns', function (Blueprint $table) {
            if (! Schema::hasColumn('ecommerce_returns', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('user_id')->constrained('ecommerce_orders')->nullOnDelete();
            }

            if (! Schema::hasColumn('ecommerce_returns', 'return_items')) {
                $table->json('return_items')->nullable()->after('reason');
            }

            if (! Schema::hasColumn('ecommerce_returns', 'refund_method')) {
                $table->string('refund_method')->nullable()->after('refund_amount');
            }

            if (! Schema::hasColumn('ecommerce_returns', 'currency')) {
                $table->string('currency', 10)->default('USD')->after('refund_method');
            }

            if (! Schema::hasColumn('ecommerce_returns', 'return_address')) {
                $table->text('return_address')->nullable()->after('currency');
            }

            if (! Schema::hasColumn('ecommerce_returns', 'requested_at')) {
                $table->timestamp('requested_at')->nullable()->after('return_address');
            }

            if (! Schema::hasColumn('ecommerce_returns', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('requested_at');
            }

            if (! Schema::hasColumn('ecommerce_returns', 'refunded_at')) {
                $table->timestamp('refunded_at')->nullable()->after('approved_at');
            }

            if (! Schema::hasColumn('ecommerce_returns', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('refunded_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ecommerce_returns', function (Blueprint $table) {
            if (Schema::hasColumn('ecommerce_returns', 'order_id')) {
                $table->dropConstrainedForeignId('order_id');
            }

            $columns = array_filter([
                Schema::hasColumn('ecommerce_returns', 'cancelled_at') ? 'cancelled_at' : null,
                Schema::hasColumn('ecommerce_returns', 'refunded_at') ? 'refunded_at' : null,
                Schema::hasColumn('ecommerce_returns', 'approved_at') ? 'approved_at' : null,
                Schema::hasColumn('ecommerce_returns', 'requested_at') ? 'requested_at' : null,
                Schema::hasColumn('ecommerce_returns', 'return_address') ? 'return_address' : null,
                Schema::hasColumn('ecommerce_returns', 'currency') ? 'currency' : null,
                Schema::hasColumn('ecommerce_returns', 'refund_method') ? 'refund_method' : null,
                Schema::hasColumn('ecommerce_returns', 'return_items') ? 'return_items' : null,
            ]);

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
