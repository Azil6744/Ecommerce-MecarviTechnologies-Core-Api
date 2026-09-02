<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EcommerceCoupon;

echo "Seeding exact Mockup Coupons and Deals into Ecommerce Coupons database..." . PHP_EOL;

$coupons = [
    [
        'code' => 'WELCOME10',
        'title' => 'Welcome Discount',
        'subtitle' => 'Enjoy 10% off on your first order with Mecarvi Embroidery.',
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'min_order_amount' => 50.00,
        'usage_limit' => 100,
        'used_count' => 42,
        'is_active' => true,
        'starts_at' => now()->subDays(10),
        'expires_at' => now()->addDays(90),
        'metadata' => [
            'is_deal' => false,
            'tag_label' => 'Percentage',
            'theme' => 'pink',
            'badge' => '10% OFF',
            'badge_sub' => 'On Your First Order',
            'max_discount_amount' => 50.00,
            'eligible_categories' => ['Embroidery', 'Printing', 'Signs'],
            'per_customer_limit' => 1,
            'stackable' => false,
            'icon' => 'scissors'
        ]
    ],
    [
        'code' => 'FREESHIP',
        'title' => 'Free Shipping',
        'subtitle' => 'Get free standard shipping on all orders over $75.',
        'discount_type' => 'free_shipping',
        'discount_value' => 0,
        'min_order_amount' => 75.00,
        'usage_limit' => 200,
        'used_count' => 78,
        'is_active' => true,
        'starts_at' => now()->subDays(15),
        'expires_at' => now()->addDays(120),
        'metadata' => [
            'is_deal' => false,
            'tag_label' => 'Free Shipping',
            'theme' => 'blue',
            'badge' => 'FREE SHIPPING',
            'badge_sub' => 'On Orders $75+',
            'max_discount_amount' => 25.00,
            'eligible_categories' => ['Embroidery', 'Printing', 'Apparel'],
            'per_customer_limit' => 3,
            'stackable' => false,
            'icon' => 'truck'
        ]
    ],
    [
        'code' => 'SAVE25',
        'title' => 'Save $25',
        'subtitle' => 'Save $25 on all orders of $150 or more.',
        'discount_type' => 'fixed',
        'discount_value' => 25,
        'min_order_amount' => 150.00,
        'usage_limit' => 150,
        'used_count' => 33,
        'is_active' => true,
        'starts_at' => now()->subDays(5),
        'expires_at' => now()->addDays(45),
        'metadata' => [
            'is_deal' => false,
            'tag_label' => 'Fixed Amount',
            'theme' => 'orange',
            'badge' => '$25 OFF',
            'badge_sub' => 'On Orders $150+',
            'max_discount_amount' => 25.00,
            'eligible_categories' => ['Embroidery', 'Printing', 'Signs'],
            'per_customer_limit' => 1,
            'stackable' => false,
            'icon' => 'tag'
        ]
    ],
    [
        'code' => 'EMB20',
        'title' => 'Embroidery Special',
        'subtitle' => 'Get 20% off on all embroidery services.',
        'discount_type' => 'percentage',
        'discount_value' => 20,
        'min_order_amount' => 100.00,
        'usage_limit' => 80,
        'used_count' => 19,
        'is_active' => true,
        'starts_at' => now()->addDays(5),
        'expires_at' => now()->addDays(35),
        'metadata' => [
            'is_deal' => false,
            'tag_label' => 'Percentage',
            'theme' => 'green',
            'badge' => '20% OFF',
            'badge_sub' => 'Embroidery Services',
            'applies_to' => 'Embroidery',
            'max_discount_amount' => 100.00,
            'eligible_categories' => ['Embroidery'],
            'per_customer_limit' => 2,
            'stackable' => false,
            'icon' => 'needle'
        ]
    ],
    [
        'code' => 'BUNDLE15',
        'title' => 'Bundle Savings',
        'subtitle' => 'Get 15% off on selected bundle deals.',
        'discount_type' => 'percentage',
        'discount_value' => 15,
        'min_order_amount' => 80.00,
        'usage_limit' => 120,
        'used_count' => 24,
        'is_active' => false,
        'starts_at' => now()->subDays(60),
        'expires_at' => now()->subDays(5),
        'metadata' => [
            'is_deal' => false,
            'tag_label' => 'Percentage',
            'theme' => 'purple',
            'badge' => '15% OFF',
            'badge_sub' => 'Bundle Deals',
            'applies_to' => 'Bundles',
            'max_discount_amount' => 75.00,
            'eligible_categories' => ['Bundles'],
            'per_customer_limit' => 1,
            'stackable' => false,
            'icon' => 'gift'
        ]
    ],
    [
        'code' => 'VIP30',
        'title' => 'VIP Exclusive',
        'subtitle' => 'Exclusive 30% off for VIP members only.',
        'discount_type' => 'percentage',
        'discount_value' => 30,
        'min_order_amount' => 200.00,
        'usage_limit' => 200,
        'used_count' => 58,
        'is_active' => true,
        'starts_at' => now()->subDays(20),
        'expires_at' => now()->addDays(60),
        'metadata' => [
            'is_deal' => false,
            'tag_label' => 'Percentage',
            'theme' => 'teal',
            'badge' => '30% OFF',
            'badge_sub' => 'VIP Members Only',
            'applies_to' => 'VIP Members',
            'customer_eligibility' => 'VIP Members Only',
            'max_discount_amount' => 150.00,
            'eligible_categories' => ['Embroidery', 'Printing', 'Signs', 'Apparel'],
            'per_customer_limit' => 1,
            'stackable' => false,
            'icon' => 'crown'
        ]
    ],
    [
        'code' => 'SAVE15',
        'title' => '15% Off Your Order',
        'subtitle' => 'Enter code at checkout to enjoy 15% off your order.',
        'discount_type' => 'percentage',
        'discount_value' => 15,
        'min_order_amount' => 100.00,
        'usage_limit' => 5000,
        'used_count' => 1250,
        'is_active' => true,
        'starts_at' => now()->subDays(2),
        'expires_at' => now()->addDays(3),
        'metadata' => [
            'is_deal' => false,
            'tag_label' => 'Percentage',
            'theme' => 'pink',
            'badge' => '15% OFF',
            'badge_sub' => 'YOUR ORDER',
            'max_discount_amount' => 100.00,
            'eligible_categories' => ['Embroidery', 'Printing', 'Signs'],
            'per_customer_limit' => 1,
            'stackable' => false,
            'member_rule' => 'Member-exclusive',
            'expires_days_text' => 'Expires in 3 days',
            'online_only' => true
        ]
    ]
];

foreach ($coupons as $couponData) {
    EcommerceCoupon::updateOrCreate(
        ['code' => $couponData['code']],
        $couponData
    );
    echo "? Seeded Coupon: {$couponData['code']} - {$couponData['title']}" . PHP_EOL;
}

$deals = [
    [
        'code' => '10POLOS200',
        'title' => '10 Premium Polos Bundle',
        'subtitle' => 'Elevate Your Brand in Style',
        'discount_type' => 'fixed',
        'discount_value' => 90,
        'min_order_amount' => 200.00,
        'usage_limit' => 100,
        'used_count' => 38,
        'is_active' => true,
        'starts_at' => now()->subDays(5),
        'expires_at' => now()->addDays(45),
        'metadata' => [
            'is_deal' => true,
            'is_bundle' => true,
            'deal_category' => 'bundles',
            'theme' => 'navy',
            'badge_hero' => '10 POLOS FOR $200',
            'badge_sub' => 'PREMIUM POLO BUNDLE',
            'badge' => 'SAVE $90.00',
            'badge_bg' => 'bg-[#1E1B4B]',
            'bundle_price' => 200.00,
            'original_price' => 290.00,
            'savings_amount' => 90.00,
            'image_url' => 'https://images.unsplash.com/photo-1625910513413-7422eb1f32a5?w=600&auto=format&fit=crop&q=80',
            'bullets' => [
                'Premium Polo Shirts',
                'Left Chest Embroidery',
                'Includes Setup & Thread'
            ],
            'whats_included' => ['10 Polos Total', 'Mix Sizes (S-3XL)', 'Mix Colors', 'Setup Included', 'Same Category Only'],
            'eligible_products' => 'Custom pique and performance polo shirts',
            'available_sizes' => 'XS - 4XL',
            'available_colors' => ['#000000', '#FFFFFF', '#1E3A8A', '#DC2626', '#15803D'],
            'decoration_options' => 'Embroidery, Left Chest Logo',
            'button_text' => 'View Details',
            'button_bg' => 'bg-[#E91E63] hover:bg-[#D81B60] text-white'
        ]
    ],
    [
        'code' => '12TEES150',
        'title' => '12 T-Shirts for $150',
        'subtitle' => 'Soft. Durable. Perfect for Any Brand.',
        'discount_type' => 'fixed',
        'discount_value' => 54,
        'min_order_amount' => 150.00,
        'usage_limit' => 150,
        'used_count' => 64,
        'is_active' => true,
        'starts_at' => now()->subDays(10),
        'expires_at' => now()->addDays(60),
        'metadata' => [
            'is_deal' => true,
            'is_bundle' => true,
            'deal_category' => 'bundles',
            'theme' => 'orange',
            'badge_hero' => '12 T-SHIRTS FOR $150',
            'badge_sub' => 'PREMIUM COTTON TEES',
            'badge' => 'SAVE $54.00',
            'badge_bg' => 'bg-[#EA580C]',
            'bundle_price' => 150.00,
            'original_price' => 204.00,
            'savings_amount' => 54.00,
            'image_url' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600&auto=format&fit=crop&q=80',
            'bullets' => [
                'Premium Cotton Tees',
                'Left Chest Embroidery',
                'Includes Setup & Thread'
            ],
            'whats_included' => ['12 T-Shirts Total', 'Mix Sizes (XS-4XL)', 'Mix Colors', 'Setup Included', 'Same Category Only'],
            'eligible_products' => 'Heavyweight cotton and ring-spun crewnecks',
            'available_sizes' => 'XS - 4XL',
            'available_colors' => ['#000000', '#FFFFFF', '#6B7280', '#DC2626', '#2563EB'],
            'decoration_options' => 'Blank, Screen Print, Embroidery, Heat Transfer',
            'button_text' => 'View Details',
            'button_bg' => 'bg-[#E91E63] hover:bg-[#D81B60] text-white'
        ]
    ],
    [
        'code' => '50DIGITIZING100',
        'title' => '50 Logos Digitized for $100',
        'subtitle' => 'High Quality. Fast Turnaround.',
        'discount_type' => 'fixed',
        'discount_value' => 26,
        'min_order_amount' => 100.00,
        'usage_limit' => 80,
        'used_count' => 29,
        'is_active' => true,
        'starts_at' => now()->subDays(3),
        'expires_at' => now()->addDays(50),
        'metadata' => [
            'is_deal' => true,
            'is_bundle' => true,
            'deal_category' => 'digitizing',
            'theme' => 'green',
            'badge_hero' => '50 LOGOS DIGITIZED FOR $100',
            'badge_sub' => 'HIGH QUALITY DIGITIZING',
            'badge' => 'SAVE $26.00',
            'badge_bg' => 'bg-[#15803D]',
            'bundle_price' => 100.00,
            'original_price' => 126.00,
            'savings_amount' => 26.00,
            'image_url' => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=600&auto=format&fit=crop&q=80',
            'bullets' => [
                'High Quality Digitizing',
                'PES, DST, EXP & More',
                '2 Revisions Included'
            ],
            'whats_included' => ['50 Design Files', 'All Machine Formats', '2 Free Revisions', 'Production Preview Sheet'],
            'eligible_products' => 'Left chest, cap, jacket back embroidery files',
            'available_sizes' => 'Standard, Medium, Large, Cap',
            'available_colors' => ['#000000'],
            'decoration_options' => 'Digitizing vector/raster conversions',
            'button_text' => 'View Details',
            'button_bg' => 'bg-[#E91E63] hover:bg-[#D81B60] text-white'
        ]
    ],
    [
        'code' => '5CAPS1FREE',
        'title' => '5 Caps Get 1 Free',
        'subtitle' => 'Top Off Your Brand',
        'discount_type' => 'buy_x_get_y',
        'discount_value' => 25,
        'min_order_amount' => 125.00,
        'usage_limit' => 120,
        'used_count' => 45,
        'is_active' => true,
        'starts_at' => now()->subDays(7),
        'expires_at' => now()->addDays(30),
        'metadata' => [
            'is_deal' => true,
            'is_bundle' => true,
            'deal_category' => 'bundles',
            'theme' => 'blue',
            'badge_hero' => '5 CAPS GET 1 FREE',
            'badge_sub' => 'MIX COLORS & STYLES',
            'badge' => 'SAVE $25.00',
            'badge_bg' => 'bg-[#0284C7]',
            'bundle_price' => 125.00,
            'original_price' => 150.00,
            'savings_amount' => 25.00,
            'image_url' => 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=600&auto=format&fit=crop&q=80',
            'bullets' => [
                'Any Style Caps',
                'All Embroidery Included',
                'Includes Setup & Thread'
            ],
            'whats_included' => ['6 Caps Total', 'Mix Styles (Trucker/Snapback)', 'Mix Colors', '1 Free Cap', 'Setup Included'],
            'eligible_products' => 'Structured, unstructured, dad hats, trucker caps',
            'available_sizes' => 'Adjustable, Fitted S/M, L/XL',
            'available_colors' => ['#000000', '#FFFFFF', '#DC2626', '#1E3A8A', '#374151'],
            'decoration_options' => 'Front 3D Puff / Flat Embroidery',
            'button_text' => 'View Details',
            'button_bg' => 'bg-[#E91E63] hover:bg-[#D81B60] text-white'
        ]
    ],
    [
        'code' => '3HOODIES150',
        'title' => '3 Hoodies for $150',
        'subtitle' => 'Warm. Durable. Always On Brand.',
        'discount_type' => 'fixed',
        'discount_value' => 50,
        'min_order_amount' => 150.00,
        'usage_limit' => 100,
        'used_count' => 41,
        'is_active' => true,
        'starts_at' => now()->subDays(12),
        'expires_at' => now()->addDays(30),
        'metadata' => [
            'is_deal' => true,
            'is_bundle' => true,
            'deal_category' => 'bundles',
            'theme' => 'purple',
            'badge_hero' => '3 HOODIES FOR $150',
            'badge_sub' => 'HEAVY BLEND FLEECE',
            'badge' => 'SAVE $50.00',
            'badge_bg' => 'bg-[#7C3AED]',
            'bundle_price' => 150.00,
            'original_price' => 200.00,
            'savings_amount' => 50.00,
            'image_url' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=600&auto=format&fit=crop&q=80',
            'bullets' => [
                'Heavy Blend Fleece',
                'Left Chest Embroidery',
                'Includes Setup & Thread'
            ],
            'whats_included' => ['3 Hoodies Total', 'Mix Sizes (S-3XL)', 'Mix Colors', 'Setup Included', 'Fleece Lined'],
            'eligible_products' => 'Heavyweight 50/50 cotton/poly pullover hoodies',
            'available_sizes' => 'S - 4XL',
            'available_colors' => ['#000000', '#6B7280', '#1E3A8A', '#991B1B', '#374151'],
            'decoration_options' => 'Chest Embroidery, Kangaroo Pocket Setup',
            'button_text' => 'View Details',
            'button_bg' => 'bg-[#E91E63] hover:bg-[#D81B60] text-white'
        ]
    ],
    [
        'code' => 'EMBSTARTERKIT',
        'title' => 'Embroidery Machine Starter Kit',
        'subtitle' => 'Everything You Need to Get Started',
        'discount_type' => 'fixed',
        'discount_value' => 900,
        'min_order_amount' => 2950.00,
        'usage_limit' => 25,
        'used_count' => 12,
        'is_active' => true,
        'starts_at' => now()->subDays(4),
        'expires_at' => now()->addDays(40),
        'metadata' => [
            'is_deal' => true,
            'is_bundle' => true,
            'deal_category' => 'equipment',
            'theme' => 'pink',
            'badge_hero' => '1 EMBROIDERY MACHINE STARTER KIT',
            'badge_sub' => 'EVERYTHING YOU NEED TO GET STARTED',
            'badge' => 'SAVE $900.00',
            'badge_bg' => 'bg-[#BE123C]',
            'bundle_price' => 2950.00,
            'original_price' => 3850.00,
            'savings_amount' => 900.00,
            'image_url' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=600&auto=format&fit=crop&q=80',
            'bullets' => [
                'Embroidery Machine',
                'Starter Kit Included',
                'Training & Support'
            ],
            'whats_included' => ['Commercial Single-Head Machine', 'Full Starter Thread Kit (40 Cones)', 'Cap Attachment & Frames', '1-on-1 Virtual Training Session', '2-Year Parts Warranty'],
            'eligible_products' => 'Commercial and industrial embroidery systems',
            'available_sizes' => 'Standard Stand / Table Mount',
            'available_colors' => ['#FFFFFF'],
            'decoration_options' => 'Full Multi-Needle Automated System',
            'button_text' => 'View Details',
            'button_bg' => 'bg-[#E91E63] hover:bg-[#D81B60] text-white'
        ]
    ],
    [
        'code' => 'BUY5GET1',
        'title' => 'Buy 5 Shirts, Get 1 Free',
        'subtitle' => 'Mix styles and sizes. Add 6 eligible shirts to cart and the lowest-priced shirt is free.',
        'discount_type' => 'buy_x_get_y',
        'discount_value' => 40,
        'min_order_amount' => 120.00,
        'usage_limit' => 200,
        'used_count' => 145,
        'is_active' => true,
        'starts_at' => now()->subDays(5),
        'expires_at' => now()->addDays(6),
        'metadata' => [
            'is_deal' => true,
            'is_bundle' => true,
            'deal_category' => 'bundles',
            'theme' => 'pink',
            'badge_hero' => 'BUY 5 SHIRTS, GET 1 FREE',
            'badge_sub' => 'LOWEST PRICED ITEM FREE',
            'badge' => 'SAVE $40',
            'badge_bg' => 'bg-[#E91E63]',
            'bundle_price' => 120.00,
            'original_price' => 160.00,
            'savings_amount' => 40.00,
            'image_url' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600&auto=format&fit=crop&q=80',
            'bullets' => [
                'Add any 6 eligible shirts to cart',
                'Lowest-priced eligible shirt is free',
                'Standard production times apply'
            ],
            'whats_included' => ['6 Shirts Total', 'Mix Sizes', 'Mix Colors', '1 Free Item', 'Same Category Only'],
            'eligible_products' => 'T-shirts, polos, unisex tees, women\'s tees',
            'available_sizes' => 'XS - 4XL',
            'available_colors' => ['#000000', '#F43F5E', '#FFFFFF', '#64748B', '#1E3A8A'],
            'decoration_options' => 'Blank, screen print, embroidery, heat transfer',
            'deal_type_label' => 'Bundle Deal',
            'purchase_requirement' => 'Add any 6 eligible shirts to cart',
            'reward_description' => 'Lowest-priced eligible shirt is free',
            'minimum_purchase' => '5 paid shirts',
            'maximum_use' => '1 per order',
            'customer_eligibility' => 'All customers',
            'channel' => 'Online only at mecarvi.com',
            'stackable_rule' => 'Cannot be combined with other coupons or bundle deals',
            'exclusions' => 'Not valid on clearance items, gift cards, memberships, or prior purchases',
            'fulfillment' => 'Standard production times apply',
            'availability_claimed' => 145,
            'availability_remaining' => 55,
            'expires_days_text' => 'Expires in 6 days',
            'button_text' => 'Claim Deal',
            'button_bg' => 'bg-[#E91E63] hover:bg-[#D81B60] text-white'
        ]
    ]
];

foreach ($deals as $dealData) {
    EcommerceCoupon::updateOrCreate(
        ['code' => $dealData['code']],
        $dealData
    );
    echo "? Seeded Deal: {$dealData['code']} - {$dealData['title']}" . PHP_EOL;
}

echo PHP_EOL . "--- DATABASE SUMMARY ---" . PHP_EOL;
echo "Total in DB: " . EcommerceCoupon::count() . PHP_EOL;
echo "Total Regular Coupons: " . EcommerceCoupon::where(function($q) {
    $q->whereNull('metadata->is_deal')->orWhere('metadata->is_deal', false);
})->where(function($q) {
    $q->whereNull('metadata->is_bundle')->orWhere('metadata->is_bundle', false);
})->count() . PHP_EOL;
echo "Total Bundle Deals: " . EcommerceCoupon::where(function($q) {
    $q->where('metadata->is_deal', true)->orWhere('metadata->is_bundle', true);
})->count() . PHP_EOL;

echo "Seeding completed successfully!" . PHP_EOL;
