<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecommerce_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('ecommerce_returns', 'refund_origin')) {
                $table->string('refund_origin')->default('return_refund')->after('status');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'claim_type')) {
                $table->string('claim_type')->nullable()->after('refund_origin');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'who_pays_shipping')) {
                $table->string('who_pays_shipping')->default('customer')->nullable()->after('return_method');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'rma_number')) {
                $table->string('rma_number')->nullable()->after('return_number');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('requested_at');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'inspection_condition')) {
                $table->string('inspection_condition')->nullable()->after('item_condition');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'inspection_notes')) {
                $table->text('inspection_notes')->nullable()->after('inspection_condition');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'inspection_evidence')) {
                $table->json('inspection_evidence')->nullable()->after('evidence_urls');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'decline_reason')) {
                $table->string('decline_reason')->nullable()->after('cancellation_reason');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'decline_details')) {
                $table->text('decline_details')->nullable()->after('decline_reason');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'customer_explanation')) {
                $table->text('customer_explanation')->nullable()->after('customer_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ecommerce_returns', function (Blueprint $table) {
            $cols = [
                'refund_origin',
                'claim_type',
                'who_pays_shipping',
                'rma_number',
                'received_at',
                'inspection_condition',
                'inspection_notes',
                'inspection_evidence',
                'decline_reason',
                'decline_details',
                'customer_explanation',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('ecommerce_returns', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
