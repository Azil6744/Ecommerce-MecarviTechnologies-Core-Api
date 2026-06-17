<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PortfolioSection;
use App\Models\PortfolioItem;

class PortfolioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean existing portfolio sections
        PortfolioSection::query()->delete();

        // Seed section configuration
        PortfolioSection::create([
            'id' => 1,
            'main_heading' => 'OUR WORK SPEAKS IN STITCHES.',
            'description' => 'A gallery of embroidered products crafted for brands that demand the best.',
            'background_color' => '#ffffff',
            'background_image' => null,
        ]);

        // Clean existing items to prevent duplicates on multiple runs
        PortfolioItem::query()->delete();

        // Seed the 10 portfolio items matching the reference design
        $items = [
            [
                'title' => '3D PUFF EMBROIDERY',
                'link' => '',
                'image' => 'portfolio-items/portfolio_3d_puff.png',
                'order' => 1,
                'category' => 'CUSTOM EMBROIDERY',
            ],
            [
                'title' => 'PREMIUM HEADWEAR',
                'link' => '',
                'image' => 'portfolio-items/portfolio_headwear.png',
                'order' => 2,
                'category' => 'HEADWEAR',
            ],
            [
                'title' => 'EMBROIDERED BAGS',
                'link' => '',
                'image' => 'portfolio-items/portfolio_bags.png',
                'order' => 3,
                'category' => 'BAGS',
            ],
            [
                'title' => 'CUSTOM PATCHES',
                'link' => '',
                'image' => 'portfolio-items/portfolio_patches.png',
                'order' => 4,
                'category' => 'PATCHES',
            ],
            [
                'title' => 'LEFT CHEST EMBROIDERY',
                'link' => '',
                'image' => 'portfolio-items/portfolio_left_chest.png',
                'order' => 5,
                'category' => 'CUSTOM EMBROIDERY',
            ],
            [
                'title' => 'APPAREL EMBROIDERY',
                'link' => '',
                'image' => 'portfolio-items/portfolio_apparel.png',
                'order' => 6,
                'category' => 'APPAREL',
            ],
            [
                'title' => 'TOWELS & LINENS',
                'link' => '',
                'image' => 'portfolio-items/portfolio_towels.png',
                'order' => 7,
                'category' => 'TOWELS',
            ],
            [
                'title' => 'UNIFORMS',
                'link' => '',
                'image' => 'portfolio-items/portfolio_uniforms.png',
                'order' => 8,
                'category' => 'UNIFORMS',
            ],
            [
                'title' => 'LEATHER PATCHES',
                'link' => '',
                'image' => 'portfolio-items/portfolio_leather_patches.png',
                'order' => 9,
                'category' => 'PATCHES',
            ],
            [
                'title' => 'JACKETS & OUTERWEAR',
                'link' => '',
                'image' => 'portfolio-items/portfolio_jackets.png',
                'order' => 10,
                'category' => 'JACKETS',
            ],
        ];

        foreach ($items as $item) {
            PortfolioItem::create($item);
        }
    }
}
