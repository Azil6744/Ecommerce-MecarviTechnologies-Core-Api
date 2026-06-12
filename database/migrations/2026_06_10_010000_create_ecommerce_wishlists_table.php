<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecommerce_wishlists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('name')->default('My Wishlist');
            $table->boolean('is_default')->default(true);
            $table->string('share_token')->nullable()->unique();
            $table->timestamps();

            $table->unique('user_id');
        });

        Schema::create('ecommerce_wishlist_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecommerce_wishlist_id')->constrained('ecommerce_wishlists')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['ecommerce_wishlist_id', 'slug']);
        });

        Schema::create('ecommerce_wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecommerce_wishlist_id')->constrained('ecommerce_wishlists')->cascadeOnDelete();
            $table->unsignedBigInteger('ecommerce_wishlist_collection_id')->nullable();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('saved_price', 10, 2)->default(0);
            $table->json('options')->nullable();
            $table->json('product_snapshot')->nullable();
            $table->timestamps();

            $table->unique(['ecommerce_wishlist_id', 'product_id']);
            $table->foreign('ecommerce_wishlist_collection_id', 'wishlist_items_collection_fk')
                ->references('id')
                ->on('ecommerce_wishlist_collections')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_wishlist_items');
        Schema::dropIfExists('ecommerce_wishlist_collections');
        Schema::dropIfExists('ecommerce_wishlists');
    }
};
