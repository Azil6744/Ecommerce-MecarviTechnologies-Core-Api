<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('product_page_hero_sections')->count() === 0) {
            DB::table('product_page_hero_sections')->insert([
                'hero_title' => "Elevate Your Brand\nwith Premium Embroidery",
                'description_title' => "Premium Custom Embroidery",
                'hero_description' => "High-quality custom embroidery on apparel, headwear, bags and more. Crafted with precision to make your brand stand out.",
                'section_bg_color' => '#ffffff',
                'image_url' => null,
                'background_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('product_page_hero_sections')
            ->where('description_title', 'Premium Custom Embroidery')
            ->delete();
    }
};
