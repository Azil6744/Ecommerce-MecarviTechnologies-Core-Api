<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ecommerce_order_verifications', function (Blueprint $table) {
            if (!Schema::hasColumn('ecommerce_order_verifications', 'order_number')) {
                $table->string('order_number')->nullable()->after('order_id');
            }
            if (!Schema::hasColumn('ecommerce_order_verifications', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('order_number')->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('ecommerce_order_verifications', 'site_slug')) {
                $table->string('site_slug')->default('embroidery')->after('user_id');
            }
            if (!Schema::hasColumn('ecommerce_order_verifications', 'reason_title')) {
                $table->string('reason_title')->nullable()->after('flag_reason');
            }
            if (!Schema::hasColumn('ecommerce_order_verifications', 'reason_text')) {
                $table->text('reason_text')->nullable()->after('reason_title');
            }
            if (!Schema::hasColumn('ecommerce_order_verifications', 'decline_reason')) {
                $table->text('decline_reason')->nullable()->after('reason_text');
            }
            if (!Schema::hasColumn('ecommerce_order_verifications', 'deadline_at')) {
                $table->timestamp('deadline_at')->nullable()->after('decline_reason');
            }
            if (!Schema::hasColumn('ecommerce_order_verifications', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('deadline_at');
            }
            if (!Schema::hasColumn('ecommerce_order_verifications', 'declined_at')) {
                $table->timestamp('declined_at')->nullable()->after('verified_at');
            }
            if (!Schema::hasColumn('ecommerce_order_verifications', 'required_documents')) {
                $table->json('required_documents')->nullable()->after('declined_at');
            }
            if (!Schema::hasColumn('ecommerce_order_verifications', 'submitted_documents')) {
                $table->json('submitted_documents')->nullable()->after('required_documents');
            }
            if (!Schema::hasColumn('ecommerce_order_verifications', 'timeline')) {
                $table->json('timeline')->nullable()->after('submitted_documents');
            }
            if (!Schema::hasColumn('ecommerce_order_verifications', 'internal_notes')) {
                $table->json('internal_notes')->nullable()->after('timeline');
            }
            if (!Schema::hasColumn('ecommerce_order_verifications', 'customer_notes')) {
                $table->text('customer_notes')->nullable()->after('internal_notes');
            }
            if (!Schema::hasColumn('ecommerce_order_verifications', 'total_amount')) {
                $table->decimal('total_amount', 10, 2)->nullable()->after('customer_notes');
            }
            if (!Schema::hasColumn('ecommerce_order_verifications', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('total_amount');
            }
            if (!Schema::hasColumn('ecommerce_order_verifications', 'product_name')) {
                $table->string('product_name')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('ecommerce_order_verifications', 'product_specs')) {
                $table->string('product_specs')->nullable()->after('product_name');
            }
            if (!Schema::hasColumn('ecommerce_order_verifications', 'item_count')) {
                $table->integer('item_count')->default(1)->after('product_specs');
            }
            if (!Schema::hasColumn('ecommerce_order_verifications', 'product_image')) {
                $table->string('product_image')->nullable()->after('item_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecommerce_order_verifications', function (Blueprint $table) {
            $table->dropColumn([
                'order_number',
                'user_id',
                'site_slug',
                'reason_title',
                'reason_text',
                'decline_reason',
                'deadline_at',
                'verified_at',
                'declined_at',
                'required_documents',
                'submitted_documents',
                'timeline',
                'internal_notes',
                'customer_notes',
                'total_amount',
                'payment_method',
                'product_name',
                'product_specs',
                'item_count',
                'product_image',
            ]);
        });
    }
};
