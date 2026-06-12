<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecommerce_order_proof_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proof_id')->constrained('ecommerce_order_proofs')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('author_type')->default('customer');
            $table->text('comment');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['proof_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_order_proof_comments');
    }
};
