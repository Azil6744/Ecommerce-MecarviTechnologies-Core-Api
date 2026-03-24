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
        Schema::create('ecommerce_quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quote_number')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('company_name')->nullable();
            $table->string('customer_name');
            $table->string('contact_email');
            $table->string('status')->default('Pending Approval');
            $table->decimal('total_estimated', 10, 2);
            $table->date('valid_until');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_quotations');
    }
};
