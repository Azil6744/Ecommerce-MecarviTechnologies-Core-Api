<?php

namespace Database\Seeders;

use App\Models\GlobalAttribute;
use Illuminate\Database\Seeder;

class GlobalAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Thread Type
        $threadType = GlobalAttribute::create([
            'name' => 'Thread Type',
            'type' => 'Dropdown',
            'description' => 'Choose the type of embroidery thread for your design.',
            'pricing_mode' => 'per_item',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $threadType->values()->createMany([
            [
                'name' => 'Polyester',
                'description' => 'Durable and bleach-resistant thread. Great for workwear.',
                'price' => 0.00,
                'pricing_mode' => 'per_item',
                'status' => 'active',
                'sort_order' => 1,
            ],
            [
                'name' => 'Rayon',
                'description' => 'High-sheen thread for a bright, silky appearance.',
                'price' => 0.50,
                'pricing_mode' => 'per_item',
                'status' => 'active',
                'sort_order' => 2,
            ],
            [
                'name' => 'Metallic',
                'description' => 'Shiny metallic thread for luxury or high-impact designs.',
                'price' => 1.50,
                'pricing_mode' => 'per_item',
                'status' => 'active',
                'sort_order' => 3,
            ],
        ]);

        // 2. Embroidery Placement
        $placement = GlobalAttribute::create([
            'name' => 'Embroidery Placement',
            'type' => 'Dropdown',
            'description' => 'Where would you like the embroidery placed on the product?',
            'pricing_mode' => 'per_item',
            'status' => 'active',
            'sort_order' => 2,
        ]);

        $placement->values()->createMany([
            [
                'name' => 'Left Chest',
                'description' => 'Standard placement for logos.',
                'price' => 0.00,
                'pricing_mode' => 'per_item',
                'status' => 'active',
                'sort_order' => 1,
            ],
            [
                'name' => 'Right Chest',
                'description' => 'Alternative front chest placement.',
                'price' => 0.00,
                'pricing_mode' => 'per_item',
                'status' => 'active',
                'sort_order' => 2,
            ],
            [
                'name' => 'Full Back',
                'description' => 'Large embroidery across the back.',
                'price' => 5.00,
                'pricing_mode' => 'per_item',
                'status' => 'active',
                'sort_order' => 3,
            ],
            [
                'name' => 'Left Sleeve',
                'description' => 'Discreet brand placement on the left sleeve.',
                'price' => 2.00,
                'pricing_mode' => 'per_item',
                'status' => 'active',
                'sort_order' => 4,
            ],
        ]);
    }
}
