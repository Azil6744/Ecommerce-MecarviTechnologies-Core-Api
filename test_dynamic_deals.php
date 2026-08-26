<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Add a 5th real Admin Deal to test 100% dynamic rendering from backend
\App\Models\EcommerceCoupon::firstOrCreate(
    ['code' => 'HOODIES150'],
    [
        'title' => '5 Hoodies for $150',
        'subtitle' => 'Custom embroidered fleece hoodies',
        'discount_type' => 'fixed',
        'discount_value' => 50,
        'min_order_amount' => 150,
        'is_active' => true,
        'starts_at' => now(),
        'expires_at' => now()->addDays(60),
        'metadata' => [
            'is_bundle' => true,
            'badge' => 'SAVE $50',
            'badge_bg' => 'bg-[#7C3AED]',
            'original_price' => 200.00,
            'bundle_price' => 150.00,
            'feature1_label' => 'Min. 5 hoodies',
            'feature1_sub' => 'Assorted colors',
            'feature2_label' => 'Free Logo Embroidery',
            'feature2_sub' => '$40 value',
            'image_url' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=500&auto=format&fit=crop&q=80',
            'button_text' => 'Claim Deal',
            'button_bg' => 'bg-[#7C3AED] hover:bg-[#6D28D9] text-white'
        ]
    ]
);

$deals = \App\Models\EcommerceCoupon::where('metadata->is_bundle', true)->get();
echo "TOTAL_DYNAMIC_BUNDLE_DEALS_IN_DB: " . $deals->count() . PHP_EOL;
foreach ($deals as $d) {
    echo "- ID: {$d->id} | Code: {$d->code} | Title: {$d->title} | Price: $" . ($d->metadata['bundle_price'] ?? $d->min_order_amount) . PHP_EOL;
}
