<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecommerce_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('ecommerce_orders', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0)->after('total_amount');
            }

            if (! Schema::hasColumn('ecommerce_orders', 'shipping_address')) {
                $table->text('shipping_address')->nullable()->after('customer_phone');
            }

            if (! Schema::hasColumn('ecommerce_orders', 'billing_address')) {
                $table->text('billing_address')->nullable()->after('shipping_address');
            }

            if (! Schema::hasColumn('ecommerce_orders', 'tracking_carrier')) {
                $table->string('tracking_carrier')->nullable()->after('shipping_method');
            }

            if (! Schema::hasColumn('ecommerce_orders', 'tracking_number')) {
                $table->string('tracking_number')->nullable()->after('tracking_carrier');
            }

            if (! Schema::hasColumn('ecommerce_orders', 'tracking_url')) {
                $table->string('tracking_url', 1000)->nullable()->after('tracking_number');
            }

            if (! Schema::hasColumn('ecommerce_orders', 'estimated_delivery_at')) {
                $table->timestamp('estimated_delivery_at')->nullable()->after('tracking_url');
            }

            if (! Schema::hasColumn('ecommerce_orders', 'shipped_at')) {
                $table->timestamp('shipped_at')->nullable()->after('estimated_delivery_at');
            }

            if (! Schema::hasColumn('ecommerce_orders', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            }
        });

        if (! Schema::hasTable('ecommerce_order_status_events')) {
            Schema::create('ecommerce_order_status_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('ecommerce_orders')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('status');
                $table->string('label')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_order_status_events');

        Schema::table('ecommerce_orders', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('ecommerce_orders', 'delivered_at') ? 'delivered_at' : null,
                Schema::hasColumn('ecommerce_orders', 'shipped_at') ? 'shipped_at' : null,
                Schema::hasColumn('ecommerce_orders', 'estimated_delivery_at') ? 'estimated_delivery_at' : null,
                Schema::hasColumn('ecommerce_orders', 'tracking_url') ? 'tracking_url' : null,
                Schema::hasColumn('ecommerce_orders', 'tracking_number') ? 'tracking_number' : null,
                Schema::hasColumn('ecommerce_orders', 'tracking_carrier') ? 'tracking_carrier' : null,
                Schema::hasColumn('ecommerce_orders', 'billing_address') ? 'billing_address' : null,
                Schema::hasColumn('ecommerce_orders', 'shipping_address') ? 'shipping_address' : null,
                Schema::hasColumn('ecommerce_orders', 'subtotal') ? 'subtotal' : null,
            ]);

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
