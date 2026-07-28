<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SiteSetting;
use App\Models\User;
use App\Models\EcommerceOrder;
use App\Models\EcommerceLoyaltyTransaction;

// 1. Site Settings / Loyalty Settings
try {
    $settings = SiteSetting::first();
    echo "--- Site Settings ---\n";
    if ($settings) {
        echo "loyalty_settings: " . json_encode(json_decode($settings->loyalty_settings), JSON_PRETTY_PRINT) . "\n";
        echo "loyalty_points_earned_per_unit_price: " . ($settings->loyalty_points_earned_per_unit_price ?? 'N/A') . "\n";
        echo "loyalty_points_earned_points: " . ($settings->loyalty_points_earned_points ?? 'N/A') . "\n";
    } else {
        echo "No SiteSetting found\n";
    }
} catch (\Exception $e) {
    echo "Error querying site settings: " . $e->getMessage() . "\n";
}

// 2. Users
try {
    echo "\n--- Users ---\n";
    foreach (User::all() as $u) {
        echo "ID: {$u->id}, Name: {$u->name}, Email: {$u->email}, Loyalty Points: {$u->loyalty_points}\n";
    }
} catch (\Exception $e) {
    echo "Error querying users: " . $e->getMessage() . "\n";
}

// 3. Orders
try {
    echo "\n--- Orders ---\n";
    foreach (EcommerceOrder::all() as $o) {
        echo "ID: {$o->id}, Num: {$o->order_number}, User: {$o->user_id}, Status: {$o->status}, Payment: {$o->payment_status}, Subtotal: {$o->subtotal}, Total: {$o->total_amount}, Earned: {$o->loyalty_points_earned}, Redeemed: {$o->loyalty_points_redeemed}\n";
    }
} catch (\Exception $e) {
    echo "Error querying orders: " . $e->getMessage() . "\n";
}

// 4. Loyalty Transactions
try {
    echo "\n--- Loyalty Transactions ---\n";
    foreach (EcommerceLoyaltyTransaction::all() as $tx) {
        echo "ID: {$tx->id}, User ID: {$tx->user_id}, Order ID: {$tx->order_id}, Type: {$tx->transaction_type}, Points: {$tx->points}, Value: {$tx->dollar_value}, Status: {$tx->status}, Reason: {$tx->reason}\n";
    }
} catch (\Exception $e) {
    echo "Error querying loyalty transactions: " . $e->getMessage() . "\n";
}
