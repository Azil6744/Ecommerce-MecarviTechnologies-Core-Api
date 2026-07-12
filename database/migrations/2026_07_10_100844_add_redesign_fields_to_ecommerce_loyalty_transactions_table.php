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
        Schema::table('ecommerce_loyalty_transactions', function (Blueprint $table) {
            $table->text('reason_details')->nullable()->after('reason');
            $table->text('notes')->nullable()->after('reason_details');
            $table->string('reference_type')->nullable()->after('notes');
            $table->string('reference_id')->nullable()->after('reference_type');
            $table->date('reference_date')->nullable()->after('reference_id');
            $table->string('supporting_document')->nullable()->after('reference_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecommerce_loyalty_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'reason_details',
                'notes',
                'reference_type',
                'reference_id',
                'reference_date',
                'supporting_document'
            ]);
        });
    }
};
