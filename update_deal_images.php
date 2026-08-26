<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$images = [
    'BUY5GET1' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=500&auto=format&fit=crop&q=80',
    '10TEES200' => 'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=500&auto=format&fit=crop&q=80',
    '25POLOSSETUP' => 'https://images.unsplash.com/photo-1625910513413-7422eb1f32a5?w=500&auto=format&fit=crop&q=80',
    '50CARDS' => 'https://images.unsplash.com/photo-1606857521015-7f9fcf423740?w=500&auto=format&fit=crop&q=80',
    'HOODIES150' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=500&auto=format&fit=crop&q=80',
];

foreach ($images as $code => $url) {
    $c = \App\Models\EcommerceCoupon::where('code', $code)->first();
    if ($c) {
        $meta = $c->metadata ?? [];
        $meta['image_url'] = $url;
        $c->metadata = $meta;
        $c->save();
        echo "Updated image_url for: " . $code . PHP_EOL;
    }
}
