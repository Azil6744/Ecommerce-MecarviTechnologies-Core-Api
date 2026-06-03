<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecommerce_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('ecommerce_orders', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('status');
            }

            if (! Schema::hasColumn('ecommerce_orders', 'payment_status')) {
                $table->string('payment_status')->default('unpaid')->after('payment_method');
            }

            if (! Schema::hasColumn('ecommerce_orders', 'shipping_method')) {
                $table->string('shipping_method')->nullable()->after('payment_status');
            }

            if (! Schema::hasColumn('ecommerce_orders', 'currency')) {
                $table->string('currency', 10)->default('GBP')->after('shipping_method');
            }

            if (! Schema::hasColumn('ecommerce_orders', 'shipping_amount')) {
                $table->decimal('shipping_amount', 10, 2)->default(0)->after('total_amount');
            }

            if (! Schema::hasColumn('ecommerce_orders', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('shipping_amount');
            }

            if (! Schema::hasColumn('ecommerce_orders', 'tax_amount')) {
                $table->decimal('tax_amount', 10, 2)->default(0)->after('discount_amount');
            }
        });

        Schema::table('ecommerce_quotations', function (Blueprint $table) {
            if (! Schema::hasColumn('ecommerce_quotations', 'product_id')) {
                $table->foreignId('product_id')->nullable()->after('user_id')->constrained('products')->nullOnDelete();
            }

            if (! Schema::hasColumn('ecommerce_quotations', 'customer_email')) {
                $table->string('customer_email')->nullable()->after('contact_email');
            }

            if (! Schema::hasColumn('ecommerce_quotations', 'customer_phone')) {
                $table->string('customer_phone')->nullable()->after('customer_email');
            }

            if (! Schema::hasColumn('ecommerce_quotations', 'quantity')) {
                $table->unsignedInteger('quantity')->default(1)->after('customer_phone');
            }

            if (! Schema::hasColumn('ecommerce_quotations', 'customization')) {
                $table->json('customization')->nullable()->after('quantity');
            }

            if (! Schema::hasColumn('ecommerce_quotations', 'metadata')) {
                $table->json('metadata')->nullable()->after('customization');
            }
        });

        Schema::table('ecommerce_tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('ecommerce_tickets', 'product_id')) {
                $table->foreignId('product_id')->nullable()->after('user_id')->constrained('products')->nullOnDelete();
            }

            if (! Schema::hasColumn('ecommerce_tickets', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('product_id')->constrained('ecommerce_orders')->nullOnDelete();
            }

            if (! Schema::hasColumn('ecommerce_tickets', 'source_page')) {
                $table->string('source_page')->nullable()->after('message');
            }

            if (! Schema::hasColumn('ecommerce_tickets', 'metadata')) {
                $table->json('metadata')->nullable()->after('source_page');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ecommerce_tickets', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('ecommerce_tickets', 'metadata') ? 'metadata' : null,
                Schema::hasColumn('ecommerce_tickets', 'source_page') ? 'source_page' : null,
                Schema::hasColumn('ecommerce_tickets', 'order_id') ? 'order_id' : null,
                Schema::hasColumn('ecommerce_tickets', 'product_id') ? 'product_id' : null,
            ]);

            if ($columns) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('ecommerce_quotations', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('ecommerce_quotations', 'metadata') ? 'metadata' : null,
                Schema::hasColumn('ecommerce_quotations', 'customization') ? 'customization' : null,
                Schema::hasColumn('ecommerce_quotations', 'quantity') ? 'quantity' : null,
                Schema::hasColumn('ecommerce_quotations', 'customer_phone') ? 'customer_phone' : null,
                Schema::hasColumn('ecommerce_quotations', 'customer_email') ? 'customer_email' : null,
                Schema::hasColumn('ecommerce_quotations', 'product_id') ? 'product_id' : null,
            ]);

            if ($columns) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('ecommerce_orders', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('ecommerce_orders', 'tax_amount') ? 'tax_amount' : null,
                Schema::hasColumn('ecommerce_orders', 'discount_amount') ? 'discount_amount' : null,
                Schema::hasColumn('ecommerce_orders', 'shipping_amount') ? 'shipping_amount' : null,
                Schema::hasColumn('ecommerce_orders', 'currency') ? 'currency' : null,
                Schema::hasColumn('ecommerce_orders', 'shipping_method') ? 'shipping_method' : null,
                Schema::hasColumn('ecommerce_orders', 'payment_status') ? 'payment_status' : null,
                Schema::hasColumn('ecommerce_orders', 'payment_method') ? 'payment_method' : null,
            ]);

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
