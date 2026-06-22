<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('issue');
            $table->text('description')->nullable();
            $table->string('status')->default('Pending'); // Pending, Under Review, Resolved
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reports');
    }
};
