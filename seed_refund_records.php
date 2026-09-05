<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\EcommerceOrder;
use App\Models\EcommerceReturn;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

echo "Seeding comprehensive Return and Refund records according to Developer Spec...\n";

// 1. Create or retrieve Users
$randell = User::firstOrCreate(
    ['email' => 'randell.roberts@email.com'],
    [
        'name' => 'Randell Roberts',
        'password' => Hash::make('password123'),
        'phone' => '(404) 555-9821',
        'role' => 'customer',
        'wallet_balance' => 25.00,
        'loyalty_points' => 150,
        'is_vip' => true,
    ]
);

$jasmine = User::firstOrCreate(
    ['email' => 'jasmine.b@email.com'],
    [
        'name' => 'Jasmine Brown',
        'password' => Hash::make('password123'),
        'phone' => '+1 (678) 555-0145',
        'role' => 'customer',
        'wallet_balance' => 0.00,
        'loyalty_points' => 60,
        'is_vip' => false,
    ]
);

$michael = User::firstOrCreate(
    ['email' => 'michael.j@email.com'],
    [
        'name' => 'Michael Johnson',
        'password' => Hash::make('password123'),
        'phone' => '+1 (770) 555-0122',
        'role' => 'customer',
        'wallet_balance' => 0.00,
        'loyalty_points' => 90,
        'is_vip' => false,
    ]
);

$samantha = User::firstOrCreate(
    ['email' => 'samantha.l@email.com'],
    [
        'name' => 'Samantha Lee',
        'password' => Hash::make('password123'),
        'phone' => '+1 (404) 555-0199',
        'role' => 'customer',
        'wallet_balance' => 0.00,
        'loyalty_points' => 80,
        'is_vip' => false,
    ]
);

$david = User::firstOrCreate(
    ['email' => 'david.w@email.com'],
    [
        'name' => 'David Walker',
        'password' => Hash::make('password123'),
        'phone' => '+1 (305) 555-0111',
        'role' => 'customer',
        'wallet_balance' => 0.00,
        'loyalty_points' => 20,
        'is_vip' => false,
    ]
);

$marcus = User::firstOrCreate(
    ['email' => 'marcus.vance@corporate.org'],
    [
        'name' => 'Marcus Vance',
        'password' => Hash::make('password123'),
        'phone' => '+1 (404) 555-8833',
        'role' => 'customer',
        'wallet_balance' => 0.00,
        'loyalty_points' => 240,
        'is_vip' => true,
    ]
);

$elena = User::firstOrCreate(
    ['email' => 'elena.r@email.com'],
    [
        'name' => 'Elena Rostova',
        'password' => Hash::make('password123'),
        'phone' => '+1 (404) 555-9080',
        'role' => 'customer',
        'wallet_balance' => 0.00,
        'loyalty_points' => 75,
        'is_vip' => false,
    ]
);

// 2. Create Orders
$order1 = EcommerceOrder::firstOrCreate(
    ['order_number' => '#ORD-2024-00478'],
    [
        'user_id' => $randell->id,
        'customer_name' => 'Randell Roberts',
        'customer_email' => 'randell.roberts@email.com',
        'customer_phone' => '(404) 555-9821',
        'shipping_address' => '120 Peachtree St NE, Atlanta, GA 30303',
        'total_amount' => 129.98,
        'subtotal' => 129.98,
        'shipping_amount' => 0.00,
        'status' => 'delivered',
        'payment_status' => 'paid',
        'payment_method' => 'VISA (•••• 4242)',
        'order_date' => Carbon::create(2026, 5, 10, 14, 30, 0),
        'created_at' => Carbon::create(2026, 5, 10, 14, 30, 0),
    ]
);

$order2 = EcommerceOrder::firstOrCreate(
    ['order_number' => '#OR-2024-1455'],
    [
        'user_id' => $jasmine->id,
        'customer_name' => 'Jasmine Brown',
        'customer_email' => 'jasmine.b@email.com',
        'customer_phone' => '+1 (678) 555-0145',
        'shipping_address' => '450 Church St, Decatur, GA 30030',
        'total_amount' => 49.98,
        'subtotal' => 49.98,
        'status' => 'delivered',
        'payment_status' => 'paid',
        'payment_method' => 'Mastercard (•••• 8888)',
        'order_date' => Carbon::create(2024, 5, 20, 11, 15, 0),
        'created_at' => Carbon::create(2024, 5, 20, 11, 15, 0),
    ]
);

$order3 = EcommerceOrder::firstOrCreate(
    ['order_number' => '#OR-2024-1454'],
    [
        'user_id' => $michael->id,
        'customer_name' => 'Michael Johnson',
        'customer_email' => 'michael.j@email.com',
        'customer_phone' => '+1 (770) 555-0122',
        'shipping_address' => '789 Floyd Rd, Mableton, GA 30126',
        'total_amount' => 114.97,
        'subtotal' => 114.97,
        'status' => 'shipped',
        'payment_status' => 'paid',
        'payment_method' => 'PayPal',
        'order_date' => Carbon::create(2024, 5, 18, 13, 30, 0),
        'created_at' => Carbon::create(2024, 5, 18, 13, 30, 0),
    ]
);

$order4 = EcommerceOrder::firstOrCreate(
    ['order_number' => '#OR-2024-1453'],
    [
        'user_id' => $samantha->id,
        'customer_name' => 'Samantha Lee',
        'customer_email' => 'samantha.l@email.com',
        'customer_phone' => '+1 (404) 555-0199',
        'shipping_address' => '100 Memorial Dr, Stone Mountain, GA 30083',
        'total_amount' => 49.99,
        'subtotal' => 49.99,
        'status' => 'delivered',
        'payment_status' => 'paid',
        'payment_method' => 'Amex (•••• 3005)',
        'order_date' => Carbon::create(2024, 5, 17, 21, 0, 0),
        'created_at' => Carbon::create(2024, 5, 17, 21, 0, 0),
    ]
);

$order5 = EcommerceOrder::firstOrCreate(
    ['order_number' => '#OR-2024-1452'],
    [
        'user_id' => $david->id,
        'customer_name' => 'David Walker',
        'customer_email' => 'david.w@email.com',
        'customer_phone' => '+1 (305) 555-0111',
        'shipping_address' => '220 Ocean Dr, Miami, FL 33139',
        'total_amount' => 15.99,
        'subtotal' => 15.99,
        'status' => 'delivered',
        'payment_status' => 'paid',
        'payment_method' => 'Discover (•••• 9012)',
        'order_date' => Carbon::create(2024, 5, 16, 16, 10, 0),
        'created_at' => Carbon::create(2024, 5, 16, 16, 10, 0),
    ]
);

$order6 = EcommerceOrder::firstOrCreate(
    ['order_number' => '#OR-2024-1449'],
    [
        'user_id' => $marcus->id,
        'customer_name' => 'Marcus Vance',
        'customer_email' => 'marcus.vance@corporate.org',
        'customer_phone' => '+1 (404) 555-8833',
        'shipping_address' => '500 North Point Pkwy, Alpharetta, GA 30022',
        'total_amount' => 450.00,
        'subtotal' => 450.00,
        'status' => 'delivered',
        'payment_status' => 'paid',
        'payment_method' => 'VISA (•••• 1199)',
        'order_date' => Carbon::create(2024, 5, 15, 10, 0, 0),
        'created_at' => Carbon::create(2024, 5, 15, 10, 0, 0),
    ]
);

$order7 = EcommerceOrder::firstOrCreate(
    ['order_number' => '#OR-2024-1448'],
    [
        'user_id' => $elena->id,
        'customer_name' => 'Elena Rostova',
        'customer_email' => 'elena.r@email.com',
        'customer_phone' => '+1 (404) 555-9080',
        'shipping_address' => '320 Roswell St, Marietta, GA 30060',
        'total_amount' => 120.00,
        'subtotal' => 120.00,
        'status' => 'delivered',
        'payment_status' => 'paid',
        'payment_method' => 'Mastercard (•••• 5544)',
        'order_date' => Carbon::create(2024, 5, 14, 14, 20, 0),
        'created_at' => Carbon::create(2024, 5, 14, 14, 20, 0),
    ]
);

// 3. Seed Exact Records matching UI / Spec

// Record 1: Randell Roberts (Featured Pending Return-based Refund)
EcommerceReturn::updateOrCreate(
    ['return_number' => '#RFN-2024-00148'],
    [
        'user_id' => $randell->id,
        'order_id' => $order1->id,
        'order_number' => '#ORD-2024-00478',
        'customer_name' => 'Randell Roberts',
        'reason' => 'Wrong size ordered',
        'status' => 'pending',
        'refund_origin' => 'return_refund',
        'return_status' => 'Approved',
        'return_status_detail' => 'Items received and inspected.',
        'items_subtotal' => 84.49,
        'refund_amount' => 64.50,
        'estimated_refund_amount' => 64.50,
        'approved_amount' => 64.50,
        'refund_method' => 'Original Payment Method',
        'payment_method_details' => [
            'type' => 'VISA',
            'last4' => '4242',
        ],
        'return_items' => [
            [
                'id' => 'item-1',
                'productName' => 'Mecarvi Hoodie',
                'variant' => 'Black / Large',
                'sku' => 'MH-BLK-L',
                'unitPrice' => 59.99,
                'quantity' => 1,
                'refundAmount' => 59.99,
                'image' => '/assets/images/returns/hoodie.png',
                'conditionStatus' => 'Passed',
                'conditionNote' => 'Unused with original tags intact.',
                'isCustomized' => true,
            ],
            [
                'id' => 'item-2',
                'productName' => 'Mecarvi Cap',
                'variant' => 'Black',
                'sku' => 'MC-BLK',
                'unitPrice' => 24.50,
                'quantity' => 1,
                'refundAmount' => 24.50,
                'image' => '/assets/images/returns/cap.jpg',
                'conditionStatus' => 'Passed',
                'conditionNote' => 'Original packaging included.',
                'isCustomized' => true,
            ],
        ],
        'adjustments' => [
            'restockingFee' => 5.00,
            'restockingFeeTaxable' => true,
            'originalShippingCost' => 6.99,
            'originalShippingCostTaxable' => true,
            'returnShippingCost' => 8.00,
            'returnShippingCostTaxable' => false,
            'otherFee' => 0.00,
            'otherFeeTaxable' => false,
            'otherFeeReason' => '',
            'adjustmentSubtotal' => 19.99,
            'totalDeduction' => -19.99,
        ],
        'rma_number' => 'RMA-2024-00148',
        'who_pays_shipping' => 'customer',
        'return_method' => 'Ship to Mecarvi',
        'return_window_days' => 7,
        'received_at' => Carbon::create(2026, 5, 16, 10, 20, 0),
        'requested_at' => Carbon::create(2026, 5, 16, 10, 24, 0),
        'created_at' => Carbon::create(2026, 5, 16, 10, 24, 0),
    ]
);

// Record 2: Jasmine Brown (Approved Return-based Refund)
EcommerceReturn::updateOrCreate(
    ['return_number' => 'RF-2024-0566'],
    [
        'user_id' => $jasmine->id,
        'order_id' => $order2->id,
        'order_number' => '#OR-2024-1455',
        'customer_name' => 'Jasmine Brown',
        'reason' => 'Changed my mind.',
        'status' => 'approved',
        'refund_origin' => 'return_refund',
        'return_status' => 'Approved',
        'return_status_detail' => 'Return authorized and refund released.',
        'items_subtotal' => 24.99,
        'refund_amount' => 24.99,
        'approved_amount' => 24.99,
        'approved_by' => 'Krista Calliste',
        'approved_at' => Carbon::create(2024, 5, 30, 15, 0, 0),
        'refund_method' => 'Original Payment Method',
        'payment_method_details' => [
            'type' => 'Mastercard',
            'last4' => '8888',
        ],
        'return_items' => [
            [
                'id' => 'item-3',
                'productName' => 'Embroidered Hat',
                'variant' => 'White • One Size',
                'sku' => 'EHAT-WHT-OS',
                'unitPrice' => 24.99,
                'quantity' => 1,
                'refundAmount' => 24.99,
                'image' => 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=150&auto=format&fit=crop&q=80',
                'isCustomized' => false,
            ],
        ],
        'rma_number' => 'RMA-2024-00125',
        'requested_at' => Carbon::create(2024, 5, 30, 14, 15, 0),
        'created_at' => Carbon::create(2024, 5, 30, 14, 15, 0),
    ]
);

// Record 3: Michael Johnson (Processing Direct Refund Claim - Dispute)
EcommerceReturn::updateOrCreate(
    ['return_number' => 'RF-2024-0565'],
    [
        'user_id' => $michael->id,
        'order_id' => $order3->id,
        'order_number' => '#OR-2024-1454',
        'customer_name' => 'Michael Johnson',
        'reason' => 'Package not received',
        'status' => 'processing',
        'refund_origin' => 'direct_refund',
        'claim_type' => 'package_not_received',
        'customer_explanation' => 'Carrier tracking shows delivered at front porch, but package was never received. Checked with building management and neighbors.',
        'items_subtotal' => 74.97,
        'refund_amount' => 74.97,
        'estimated_refund_amount' => 74.97,
        'refund_method' => 'PayPal',
        'payment_method_details' => [
            'type' => 'PayPal',
            'accountEmail' => 'michael.j@email.com',
        ],
        'return_items' => [
            [
                'id' => 'item-4',
                'productName' => 'Polo Shirt with Logo',
                'variant' => 'Size: M • Navy',
                'sku' => 'POLO-NVY-M',
                'unitPrice' => 24.99,
                'quantity' => 3,
                'refundAmount' => 74.97,
                'image' => 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?w=150&auto=format&fit=crop&q=80',
                'isCustomized' => true,
            ],
        ],
        'requested_at' => Carbon::create(2024, 5, 30, 11, 5, 0),
        'created_at' => Carbon::create(2024, 5, 30, 11, 5, 0),
    ]
);

// Record 4: Samantha Lee (Completed Return-based Refund)
EcommerceReturn::updateOrCreate(
    ['return_number' => 'RF-2024-0564'],
    [
        'user_id' => $samantha->id,
        'order_id' => $order4->id,
        'order_number' => '#OR-2024-1453',
        'customer_name' => 'Samantha Lee',
        'reason' => "Size doesn't fit.",
        'status' => 'completed',
        'refund_origin' => 'return_refund',
        'return_status' => 'Completed',
        'items_subtotal' => 49.99,
        'refund_amount' => 49.99,
        'approved_amount' => 49.99,
        'approved_by' => 'Krista Calliste',
        'approved_at' => Carbon::create(2024, 5, 29, 10, 15, 0),
        'refunded_at' => Carbon::create(2024, 5, 29, 10, 20, 0),
        'refund_method' => 'Original Payment Method',
        'payment_method_details' => [
            'type' => 'Amex',
            'last4' => '3005',
        ],
        'return_items' => [
            [
                'id' => 'item-5',
                'productName' => 'Zip Up Jacket',
                'variant' => 'Size: XL • Gray',
                'sku' => 'ZJ-GRY-XL',
                'unitPrice' => 49.99,
                'quantity' => 1,
                'refundAmount' => 49.99,
                'image' => 'https://images.unsplash.com/photo-1544441893-675973e31985?w=150&auto=format&fit=crop&q=80',
                'isCustomized' => false,
            ],
        ],
        'rma_number' => 'RMA-2024-00127',
        'requested_at' => Carbon::create(2024, 5, 29, 9, 42, 0),
        'created_at' => Carbon::create(2024, 5, 29, 9, 42, 0),
    ]
);

// Record 5: David Walker (Declined Return Request - Policy Rejection)
EcommerceReturn::updateOrCreate(
    ['return_number' => 'RF-2024-0563'],
    [
        'user_id' => $david->id,
        'order_id' => $order5->id,
        'order_number' => '#OR-2024-1452',
        'customer_name' => 'David Walker',
        'reason' => 'Better price elsewhere.',
        'status' => 'declined',
        'refund_origin' => 'return_refund',
        'return_status' => 'Declined',
        'decline_reason' => 'Return requirements not met',
        'decline_details' => 'Customized embroidery items cannot be returned due to price differences or change of mind per policy.',
        'admin_note' => 'Declined per customized return policy.',
        'items_subtotal' => 15.99,
        'refund_amount' => 15.99,
        'refund_method' => 'Original Payment Method',
        'payment_method_details' => [
            'type' => 'Discover',
            'last4' => '9012',
        ],
        'return_items' => [
            [
                'id' => 'item-6',
                'productName' => 'Tote Bag',
                'variant' => 'Color: Green',
                'sku' => 'TB-GRN-OS',
                'unitPrice' => 15.99,
                'quantity' => 1,
                'refundAmount' => 15.99,
                'image' => 'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?w=150&auto=format&fit=crop&q=80',
                'isCustomized' => true,
            ],
        ],
        'rma_number' => 'RMA-2024-00128',
        'requested_at' => Carbon::create(2024, 5, 28, 16, 30, 0),
        'created_at' => Carbon::create(2024, 5, 28, 16, 30, 0),
    ]
);

// Record 6: Marcus Vance (Approved Direct Refund Claim - Embroidery Error)
EcommerceReturn::updateOrCreate(
    ['return_number' => 'RF-2024-0562'],
    [
        'user_id' => $marcus->id,
        'order_id' => $order6->id,
        'order_number' => '#OR-2024-1449',
        'customer_name' => 'Marcus Vance',
        'reason' => 'Incorrect spelling/text',
        'status' => 'approved',
        'refund_origin' => 'direct_refund',
        'claim_type' => 'incorrect_spelling_or_defect',
        'customer_explanation' => '3 polos had Mecarvi Tech spelled as Mecarvi Teck. Approved artwork proof clearly had Tech.',
        'items_subtotal' => 135.00,
        'refund_amount' => 135.00,
        'approved_amount' => 135.00,
        'approved_by' => 'Krista Calliste',
        'approved_at' => Carbon::create(2024, 5, 27, 14, 0, 0),
        'refund_method' => 'Original Payment Method',
        'payment_method_details' => [
            'type' => 'VISA',
            'last4' => '1199',
        ],
        'return_items' => [
            [
                'id' => 'item-7',
                'productName' => 'Custom Embroidered Polo',
                'variant' => 'Size: L • Black',
                'sku' => 'POLO-CUSTOM-BLK',
                'unitPrice' => 45.00,
                'quantity' => 3,
                'refundAmount' => 135.00,
                'image' => 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?w=150&auto=format&fit=crop&q=80',
                'isCustomized' => true,
            ],
        ],
        'rma_number' => 'RMA-2024-00129',
        'requested_at' => Carbon::create(2024, 5, 27, 13, 15, 0),
        'created_at' => Carbon::create(2024, 5, 27, 13, 15, 0),
    ]
);

// Record 7: Elena Rostova (Pending Return Request - Thread difference)
EcommerceReturn::updateOrCreate(
    ['return_number' => 'RF-2024-0561'],
    [
        'user_id' => $elena->id,
        'order_id' => $order7->id,
        'order_number' => '#OR-2024-1448',
        'customer_name' => 'Elena Rostova',
        'reason' => 'Incorrect logo/design embroidered',
        'status' => 'pending',
        'refund_origin' => 'return_refund',
        'return_status' => 'Under Review',
        'items_subtotal' => 60.00,
        'refund_amount' => 60.00,
        'estimated_refund_amount' => 60.00,
        'refund_method' => 'Original Payment Method',
        'payment_method_details' => [
            'type' => 'Mastercard',
            'last4' => '5544',
        ],
        'return_items' => [
            [
                'id' => 'item-8',
                'productName' => 'Embroidered Crewneck',
                'variant' => 'Size: M • Forest Green',
                'sku' => 'CRW-GRN-M',
                'unitPrice' => 60.00,
                'quantity' => 1,
                'refundAmount' => 60.00,
                'image' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=150&auto=format&fit=crop&q=80',
                'isCustomized' => true,
            ],
        ],
        'rma_number' => 'RMA-2024-00130',
        'requested_at' => Carbon::create(2024, 5, 26, 11, 20, 0),
        'created_at' => Carbon::create(2024, 5, 26, 11, 20, 0),
    ]
);

echo "Seeded " . EcommerceReturn::count() . " return/refund records successfully.\n";
