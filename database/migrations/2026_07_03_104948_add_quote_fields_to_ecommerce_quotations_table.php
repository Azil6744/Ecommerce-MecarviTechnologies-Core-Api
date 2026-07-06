<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecommerce_quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('ecommerce_quotations', 'quote_price')) {
                $table->decimal('quote_price', 10, 2)->nullable()->after('total_estimated');
            }
            if (!Schema::hasColumn('ecommerce_quotations', 'quote_details')) {
                $table->text('quote_details')->nullable()->after('quote_price');
            }
            if (!Schema::hasColumn('ecommerce_quotations', 'quoted_at')) {
                $table->timestamp('quoted_at')->nullable()->after('quote_details');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecommerce_quotations', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('ecommerce_quotations', 'quote_price')) {
                $columns[] = 'quote_price';
            }
            if (Schema::hasColumn('ecommerce_quotations', 'quote_details')) {
                $columns[] = 'quote_details';
            }
            if (Schema::hasColumn('ecommerce_quotations', 'quoted_at')) {
                $columns[] = 'quoted_at';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
