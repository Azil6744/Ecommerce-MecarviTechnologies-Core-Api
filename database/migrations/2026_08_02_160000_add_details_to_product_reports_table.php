<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('product_reports', 'report_code')) {
                $table->string('report_code')->nullable();
            }
            if (!Schema::hasColumn('product_reports', 'order_number')) {
                $table->string('order_number')->nullable();
            }
            if (!Schema::hasColumn('product_reports', 'quantity')) {
                $table->integer('quantity')->default(1);
            }
            if (!Schema::hasColumn('product_reports', 'purchase_date')) {
                $table->string('purchase_date')->nullable();
            }
            if (!Schema::hasColumn('product_reports', 'issue_type')) {
                $table->string('issue_type')->nullable();
            }
            if (!Schema::hasColumn('product_reports', 'category')) {
                $table->string('category')->nullable();
            }
            if (!Schema::hasColumn('product_reports', 'product_name')) {
                $table->string('product_name')->nullable();
            }
            if (!Schema::hasColumn('product_reports', 'product_image')) {
                $table->string('product_image')->nullable();
            }
            if (!Schema::hasColumn('product_reports', 'attachments_count')) {
                $table->integer('attachments_count')->default(0);
            }
            if (!Schema::hasColumn('product_reports', 'product_images')) {
                $table->text('product_images')->nullable();
            }
            if (!Schema::hasColumn('product_reports', 'admin_feedback')) {
                $table->text('admin_feedback')->nullable();
            }
            if (!Schema::hasColumn('product_reports', 'customer_replies')) {
                $table->text('customer_replies')->nullable();
            }
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
            if (!Schema::hasColumn('product_reports', 'status_history')) {
                $table->text('status_history')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_reports', function (Blueprint $table) {
            $table->dropColumn([
                'report_code', 'order_number', 'quantity', 'purchase_date',
                'issue_type', 'category', 'product_name', 'product_image',
                'attachments_count', 'product_images', 'admin_feedback',
                'customer_replies', 'status_history'
            ]);
        });
    }
};
