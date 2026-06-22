<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('website_url')->nullable();
            $table->unsignedInteger('priority')->default(1);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('product_brands')->insert([
            [
                'name' => 'Mecarvi Premium',
                'slug' => 'mecarvi-premium',
                'website_url' => 'https://mecarvi.com',
                'priority' => 1,
                'description' => 'Our flagship premium brand for high quality custom embroidery products.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'StitchCraft',
                'slug' => 'stitchcraft',
                'website_url' => 'https://stitchcraft.example.com',
                'priority' => 2,
                'description' => 'Artisanal embroidery threads and craft essentials.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'ThreadPro',
                'slug' => 'threadpro',
                'website_url' => null,
                'priority' => 3,
                'description' => 'Industrial-grade stitching supplies and equipment accessories.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('product_brands');
    }
};
