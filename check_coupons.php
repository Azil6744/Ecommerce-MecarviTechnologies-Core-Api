<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Ensure all 4 real coupons exist in the database
\App\Models\EcommerceCoupon::firstOrCreate(
    ['code' => 'PRINT10'],
    [
        'title' => '10% Off Printing Services',
        'subtitle' => 'Exclusive 10% off on all printing services.',
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'min_order_amount' => 50,
        'is_active' => true,
        'starts_at' => now(),
        'expires_at' => now()->addDays(3),
        'metadata' => ['note' => 'Expiring soon offer', 'badge' => '10% OFF']
    ]
);

\App\Models\EcommerceCoupon::firstOrCreate(
    ['code' => 'WELCOME20'],
    [
        'title' => '20% Off Your First Order',
        'subtitle' => 'Enjoy 20% off on your first purchase.',
        'discount_type' => 'percentage',
        'discount_value' => 20,
        'min_order_amount' => 75,
        'is_active' => false,
        'starts_at' => now()->subDays(60),
        'expires_at' => now()->subDays(10),
        'metadata' => ['note' => 'Expired offer', 'badge' => '20% OFF']
    ]
);

$all = \App\Models\EcommerceCoupon::all();
echo "TOTAL_DATABASE_COUPONS: " . $all->count() . PHP_EOL;
foreach ($all as $c) {
    echo "- ID: " . $c->id . " | Code: " . $c->code . " | Title: " . $c->title . " | Status: " . $c->status . PHP_EOL;
}
