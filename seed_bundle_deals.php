<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Add real Bundle Deals into ecommerce_coupons table
\App\Models\EcommerceCoupon::firstOrCreate(
    ['code' => 'BUY5GET1'],
    [
        'title' => 'Buy 5 Shirts, Get 1 Free',
        'subtitle' => 'Mix styles & sizes',
        'discount_type' => 'buy_x_get_y',
        'discount_value' => 30,
        'min_order_amount' => 120,
        'is_active' => true,
        'starts_at' => now(),
        'expires_at' => now()->addDays(30),
        'metadata' => [
            'is_bundle' => true,
            'badge' => 'SAVE 16%',
            'badge_bg' => 'bg-[#E91E63]',
            'original_price' => 150.00,
            'bundle_price' => 120.00,
            'feature1_label' => 'Min. 5 shirts',
            'feature1_sub' => 'Assorted styles',
            'feature2_label' => '1 Shirt Free',
            'feature2_sub' => 'Lowest priced free',
            'button_text' => 'Claim Deal',
            'button_bg' => 'bg-[#E91E63] hover:bg-[#D81B60] text-white'
        ]
    ]
);

\App\Models\EcommerceCoupon::firstOrCreate(
    ['code' => '10TEES200'],
    [
        'title' => '10 Shirts for $200',
        'subtitle' => 'Premium quality tees',
        'discount_type' => 'free_shipping',
        'discount_value' => 80,
        'min_order_amount' => 200,
        'is_active' => true,
        'starts_at' => now(),
        'expires_at' => now()->addDays(45),
        'metadata' => [
            'is_bundle' => true,
            'badge' => 'SAVE $80',
            'badge_bg' => 'bg-[#00C853]',
            'original_price' => 280.00,
            'bundle_price' => 200.00,
            'feature1_label' => 'Min. 10 shirts',
            'feature1_sub' => 'Same style',
            'feature2_label' => 'Free Shipping',
            'feature2_sub' => 'On this bundle',
            'button_text' => 'View Bundle',
            'button_bg' => 'bg-[#00C853] hover:bg-[#00B248] text-white'
        ]
    ]
);

\App\Models\EcommerceCoupon::firstOrCreate(
    ['code' => '25POLOSSETUP'],
    [
        'title' => '25 Polos + Free Setup',
        'subtitle' => 'Includes embroidery',
        'discount_type' => 'fixed',
        'discount_value' => 120,
        'min_order_amount' => 350,
        'is_active' => true,
        'starts_at' => now(),
        'expires_at' => now()->addDays(30),
        'metadata' => [
            'is_bundle' => true,
            'badge' => 'SAVE 23%',
            'badge_bg' => 'bg-[#FF6D00]',
            'original_price' => 470.00,
            'bundle_price' => 350.00,
            'feature1_label' => 'Min. 25 polos',
            'feature1_sub' => 'Same design',
            'feature2_label' => 'Free Setup',
            'feature2_sub' => '$50 value',
            'button_text' => 'Claim Deal',
            'button_bg' => 'bg-[#FF6D00] hover:bg-[#E66200] text-white'
        ]
    ]
);

\App\Models\EcommerceCoupon::firstOrCreate(
    ['code' => '50CARDS'],
    [
        'title' => '50 Business Cards + Free Design',
        'subtitle' => 'High quality print',
        'discount_type' => 'percentage',
        'discount_value' => 17,
        'min_order_amount' => 45,
        'is_active' => true,
        'starts_at' => now(),
        'expires_at' => now()->addDays(45),
        'metadata' => [
            'is_bundle' => true,
            'badge' => 'SAVE 17%',
            'badge_bg' => 'bg-[#2979FF]',
            'original_price' => 70.00,
            'bundle_price' => 45.00,
            'feature1_label' => 'Min. 50 cards',
            'feature1_sub' => 'Same design',
            'feature2_label' => 'Free Design',
            'feature2_sub' => '$25 value',
            'button_text' => 'View Bundle',
            'button_bg' => 'bg-[#2979FF] hover:bg-[#1565C0] text-white'
        ]
    ]
);

$all = \App\Models\EcommerceCoupon::all();
echo "TOTAL_COUPONS_AND_DEALS_IN_DB: " . $all->count() . PHP_EOL;
foreach ($all as $c) {
    $isBundle = !empty($c->metadata['is_bundle']) ? '[BUNDLE DEAL]' : '[COUPON]';
    echo "- ID: {$c->id} | {$isBundle} Code: {$c->code} | Title: {$c->title} | Type: {$c->discount_type} | Active: " . ($c->is_active ? 'YES' : 'NO') . PHP_EOL;
}
