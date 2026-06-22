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
        Schema::create('ecommerce_product_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->text('question');
            $table->enum('status', ['unanswered', 'answered'])->default('unanswered');
            $table->timestamps();
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_product_questions');
    }
};
