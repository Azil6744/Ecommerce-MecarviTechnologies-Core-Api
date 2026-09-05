<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecommerce_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('ecommerce_returns', 'resolution')) {
                $table->string('resolution')->nullable()->after('refund_method');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'item_condition')) {
                $table->string('item_condition')->nullable()->after('resolution');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'return_method')) {
                $table->string('return_method')->nullable()->after('item_condition');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'customer_notes')) {
                $table->text('customer_notes')->nullable()->after('reason');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'customer_response')) {
                $table->text('customer_response')->nullable()->after('customer_notes');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'requested_info')) {
                $table->json('requested_info')->nullable()->after('admin_note');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'return_window_days')) {
                $table->integer('return_window_days')->default(7)->after('approved_by');
            }
            if (!Schema::hasColumn('ecommerce_returns', 'return_window_deadline')) {
                $table->timestamp('return_window_deadline')->nullable()->after('return_window_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ecommerce_returns', function (Blueprint $table) {
            $cols = [
                'resolution',
                'item_condition',
                'return_method',
                'customer_notes',
                'customer_response',
                'requested_info',
                'return_window_days',
                'return_window_deadline',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('ecommerce_returns', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
