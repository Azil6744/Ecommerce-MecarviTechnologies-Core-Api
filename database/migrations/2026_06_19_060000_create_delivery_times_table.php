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
        Schema::create('delivery_times', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('estimated_days');
            $table->text('description')->nullable();
            $table->string('color_code')->default('#28A745');
            $table->decimal('pricing', 10, 2)->default(0.00);
            $table->integer('priority')->default(1);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_times');
    }
};
