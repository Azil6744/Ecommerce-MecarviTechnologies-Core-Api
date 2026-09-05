<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecommerce_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('ecommerce_returns', 'adjustments')) {
                $table->json('adjustments')->nullable()->after('refund_method');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'items_subtotal')) {
                $table->decimal('items_subtotal', 10, 2)->nullable()->after('adjustments');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'estimated_refund_amount')) {
                $table->decimal('estimated_refund_amount', 10, 2)->nullable()->after('items_subtotal');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'approved_amount')) {
                $table->decimal('approved_amount', 10, 2)->nullable()->after('estimated_refund_amount');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'approved_by')) {
                $table->string('approved_by')->nullable()->after('approved_amount');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'admin_note')) {
                $table->text('admin_note')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'return_status')) {
                $table->string('return_status')->default('Approved')->nullable()->after('status');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'return_status_detail')) {
                $table->string('return_status_detail')->nullable()->after('return_status');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'evidence_urls')) {
                $table->json('evidence_urls')->nullable()->after('return_items');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'payment_method_details')) {
                $table->json('payment_method_details')->nullable()->after('refund_method');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ecommerce_returns', function (Blueprint $table) {
            $cols = [
                'adjustments',
                'items_subtotal',
                'estimated_refund_amount',
                'approved_amount',
                'approved_by',
                'admin_note',
                'return_status',
                'return_status_detail',
                'evidence_urls',
                'payment_method_details',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('ecommerce_returns', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
