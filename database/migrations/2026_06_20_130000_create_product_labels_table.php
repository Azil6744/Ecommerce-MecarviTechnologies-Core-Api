<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_labels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('color_code', 7);
            $table->unsignedInteger('display_order')->default(1);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('product_labels')->insert([
            [
                'name' => 'New Arrival',
                'slug' => 'new-arrival',
                'color_code' => '#22C55E',
                'display_order' => 1,
                'is_active' => true,
                'description' => 'Products that are newly added to the store.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Best Seller',
                'slug' => 'best-seller',
                'color_code' => '#3B82F6',
                'display_order' => 2,
                'is_active' => true,
                'description' => 'Our most popular and top selling products.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'On Sale',
                'slug' => 'on-sale',
                'color_code' => '#EF4444',
                'display_order' => 3,
                'is_active' => true,
                'description' => 'Products that are currently on sale.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Limited Stock',
                'slug' => 'limited-stock',
                'color_code' => '#F59E0B',
                'display_order' => 4,
                'is_active' => true,
                'description' => 'Products with limited stock available.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Featured',
                'slug' => 'featured',
                'color_code' => '#7B61FF',
                'display_order' => 5,
                'is_active' => true,
                'description' => 'Featured products on homepage and sections.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Exclusive',
                'slug' => 'exclusive',
                'color_code' => '#EC4899',
                'display_order' => 6,
                'is_active' => true,
                'description' => 'Exclusive products available only here.',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('product_labels');
    }
};
