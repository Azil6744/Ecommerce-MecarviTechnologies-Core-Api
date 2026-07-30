<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ECOMMERCE GIFT CARD ORDERS ===\n";
$orders = DB::table('ecommerce_gift_card_orders')->get();
foreach ($orders as $o) {
    echo json_encode((array)$o, JSON_PRETTY_PRINT) . "\n";
}

echo "\n=== ECOMMERCE GIFT CARDS ===\n";
$cards = DB::table('ecommerce_gift_cards')->get();
foreach ($cards as $c) {
    echo json_encode((array)$c, JSON_PRETTY_PRINT) . "\n";
}
