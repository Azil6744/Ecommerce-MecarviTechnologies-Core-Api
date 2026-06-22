<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_colors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('hex_code', 7);
            $table->string('swatch_image')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('product_colors')->insert([
            ['name' => 'Black', 'slug' => 'black', 'hex_code' => '#000000', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'White', 'slug' => 'white', 'hex_code' => '#FFFFFF', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Navy Blue', 'slug' => 'navy-blue', 'hex_code' => '#001F3F', 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Red', 'slug' => 'red', 'hex_code' => '#FF0000', 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Royal Blue', 'slug' => 'royal-blue', 'hex_code' => '#4169E1', 'sort_order' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Forest Green', 'slug' => 'forest-green', 'hex_code' => '#228B22', 'sort_order' => 6, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Charcoal Grey', 'slug' => 'charcoal-grey', 'hex_code' => '#36454F', 'sort_order' => 7, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('product_colors');
    }
};
