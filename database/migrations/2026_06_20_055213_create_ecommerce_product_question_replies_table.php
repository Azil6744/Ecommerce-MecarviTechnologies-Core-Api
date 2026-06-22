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
        Schema::create('ecommerce_product_question_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_question_id')->constrained('ecommerce_product_questions')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->enum('role', ['admin', 'customer'])->default('customer');
            $table->text('content');
            $table->integer('helpful_count')->default(0);
            $table->timestamps();
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_product_question_replies');
    }
};
