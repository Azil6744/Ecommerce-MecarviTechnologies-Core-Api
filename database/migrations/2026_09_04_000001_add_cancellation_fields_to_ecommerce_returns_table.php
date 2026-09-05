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
        Schema::table('ecommerce_returns', function (Blueprint $table) {
            if (! Schema::hasColumn('ecommerce_returns', 'cancellation_reason')) {
                $table->string('cancellation_reason')->nullable()->after('reason');
            }

            if (! Schema::hasColumn('ecommerce_returns', 'cancellation_details')) {
                $table->text('cancellation_details')->nullable()->after('cancellation_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecommerce_returns', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('ecommerce_returns', 'cancellation_details') ? 'cancellation_details' : null,
                Schema::hasColumn('ecommerce_returns', 'cancellation_reason') ? 'cancellation_reason' : null,
            ]);

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
