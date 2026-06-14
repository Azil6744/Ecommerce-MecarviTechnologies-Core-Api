<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\EcommerceOrder;
use App\Models\EcommerceOrderItem;
use App\Models\EcommerceQuotation;
use App\Models\EcommerceTicket;
use App\Models\EcommerceTicketReply;
use App\Models\EcommerceTicketNote;
use App\Models\EcommerceDispute;
use App\Models\EcommerceReview;
use App\Models\EcommerceWalletTransaction;
use App\Models\EcommerceGiftCard;
use App\Models\EcommerceMembership;
use App\Models\EcommerceAffiliate;
use App\Models\EcommerceOrderProof;
use App\Models\EcommerceOrderVerification;
use App\Models\Product;
use App\Models\EcommerceWishlist;
use App\Models\EcommerceWishlistCollection;
use App\Models\EcommerceWishlistItem;
use App\Models\EcommerceCompareItem;
use App\Models\EcommerceReturn;
use App\Models\EcommerceConversation;
use App\Models\EcommerceConversationMessage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class TestUserMockDataSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'test@example.com';
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Test User',
                'email' => $email,
                'username' => 'testuser',
                'phone' => '+1234567890',
                'role' => 'customer',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
        }

        // Clean up any existing data for this user to ensure seeder is idempotent
        EcommerceOrder::where('user_id', $user->id)->delete();
        EcommerceQuotation::where('user_id', $user->id)->delete();
        EcommerceTicket::where('user_id', $user->id)->delete();
        EcommerceDispute::where('user_id', $user->id)->delete();
        EcommerceReview::where('user_id', $user->id)->delete();
        EcommerceWalletTransaction::where('user_id', $user->id)->delete();
        EcommerceGiftCard::where('user_id', $user->id)->delete();
        EcommerceMembership::where('user_id', $user->id)->delete();
        EcommerceAffiliate::where('user_id', $user->id)->delete();
        EcommerceWishlist::where('user_id', $user->id)->delete();
        EcommerceCompareItem::where('user_id', $user->id)->delete();
        EcommerceReturn::where('user_id', $user->id)->delete();
        EcommerceConversation::where('user_id', $user->id)->delete();

        // Get a demo product to link
        $product = Product::first();
        if (!$product) {
            $product = Product::create([
                'name' => 'Heavy Duty Embroidered Work Shirt',
                'sku' => 'WORK-SHIRT-001',
                'price' => 37.00,
                'sale_price' => 37.00,
                'is_active' => true,
            ]);
        }

        // 1. Seed Orders
        $order1 = EcommerceOrder::create([
            'order_number' => 'ORD-2026-111111',
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone,
            'company_name' => 'Test Corp',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'stripe',
            'currency' => 'GBP',
            'subtotal' => 185.00,
            'total_amount' => 185.00,
            'order_date' => Carbon::now()->subDays(1),
            'shipping_address' => "123 Test Lane\nLondon, EC1A 1BB\nUnited Kingdom",
            'billing_address' => "123 Test Lane\nLondon, EC1A 1BB\nUnited Kingdom",
            'metadata' => [
                'customization' => [
                    'embroidery_type' => 'Standard Embroidery',
                    'placement' => 'Left Chest',
                    'size_label' => 'Standard (4" Wide)',
                    'quantity_label' => 'Pieces',
                    'thread_colors' => ['Red', 'White'],
                ]
            ]
        ]);

        EcommerceOrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => 5,
            'unit_price' => 37.00,
            'total_price' => 185.00,
            'product_options' => [
                'embroidery_type' => 'Standard Embroidery',
                'placement' => 'Left Chest',
            ]
        ]);

        $order2 = EcommerceOrder::create([
            'order_number' => 'ORD-2026-222222',
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone,
            'company_name' => 'Test Corp',
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'stripe',
            'currency' => 'GBP',
            'subtotal' => 74.00,
            'total_amount' => 74.00,
            'order_date' => Carbon::now()->subDays(5),
            'shipping_address' => "123 Test Lane\nLondon, EC1A 1BB\nUnited Kingdom",
            'billing_address' => "123 Test Lane\nLondon, EC1A 1BB\nUnited Kingdom",
            'metadata' => [
                'customization' => [
                    'embroidery_type' => 'Standard Embroidery',
                    'placement' => 'Left Chest',
                    'size_label' => 'Standard (4" Wide)',
                    'quantity_label' => 'Pieces',
                    'thread_colors' => ['Navy'],
                ]
            ]
        ]);

        EcommerceOrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => 2,
            'unit_price' => 37.00,
            'total_price' => 74.00,
            'product_options' => [
                'embroidery_type' => 'Standard Embroidery',
                'placement' => 'Left Chest',
            ]
        ]);

        $order3 = EcommerceOrder::create([
            'order_number' => 'ORD-2026-333333',
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone,
            'company_name' => 'Test Corp',
            'status' => 'processing',
            'payment_status' => 'paid',
            'payment_method' => 'stripe',
            'currency' => 'GBP',
            'subtotal' => 111.00,
            'total_amount' => 111.00,
            'order_date' => Carbon::now()->subDays(2),
            'shipping_address' => "123 Test Lane\nLondon, EC1A 1BB\nUnited Kingdom",
            'billing_address' => "123 Test Lane\nLondon, EC1A 1BB\nUnited Kingdom",
            'metadata' => [
                'customization' => [
                    'embroidery_type' => 'Standard Embroidery',
                    'placement' => 'Left Chest',
                    'size_label' => 'Standard (4" Wide)',
                    'quantity_label' => 'Pieces',
                    'thread_colors' => ['Black'],
                ]
            ]
        ]);

        EcommerceOrderItem::create([
            'order_id' => $order3->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => 3,
            'unit_price' => 37.00,
            'total_price' => 111.00,
            'product_options' => [
                'embroidery_type' => 'Standard Embroidery',
                'placement' => 'Left Chest',
            ]
        ]);

        $order4 = EcommerceOrder::create([
            'order_number' => 'ORD-2026-444444',
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone,
            'company_name' => 'Test Corp',
            'status' => 'cancelled',
            'payment_status' => 'unpaid',
            'payment_method' => 'stripe',
            'currency' => 'GBP',
            'subtotal' => 148.00,
            'total_amount' => 148.00,
            'order_date' => Carbon::now()->subDays(10),
            'shipping_address' => "123 Test Lane\nLondon, EC1A 1BB\nUnited Kingdom",
            'billing_address' => "123 Test Lane\nLondon, EC1A 1BB\nUnited Kingdom",
            'metadata' => [
                'customization' => [
                    'embroidery_type' => 'Standard Embroidery',
                    'placement' => 'Left Chest',
                    'size_label' => 'Standard (4" Wide)',
                    'quantity_label' => 'Pieces',
                    'thread_colors' => ['Black'],
                ]
            ]
        ]);

        EcommerceOrderItem::create([
            'order_id' => $order4->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => 4,
            'unit_price' => 37.00,
            'total_price' => 148.00,
            'product_options' => [
                'embroidery_type' => 'Standard Embroidery',
                'placement' => 'Left Chest',
            ]
        ]);

        // 2. Seed Order Proofs
        EcommerceOrderProof::create([
            'order_id' => $order1->id,
            'proof_type' => 'Embroidery Mockup',
            'file_path' => '/mock-assets/proof-mockup-1.pdf',
            'status' => 'awaiting_approval',
        ]);

        EcommerceOrderProof::create([
            'order_id' => $order2->id,
            'proof_type' => 'Embroidery Mockup',
            'file_path' => '/mock-assets/proof-mockup-2.pdf',
            'status' => 'approved',
        ]);

        // 3. Seed Order Verifications
        EcommerceOrderVerification::create([
            'order_id' => $order1->id,
            'risk_level' => 'low',
            'flag_reason' => 'Standard automated review passed',
            'status' => 'cleared',
        ]);

        // 4. Seed Quotations
        EcommerceQuotation::create([
            'quote_number' => 'QUO-2026-ABCDEF',
            'user_id' => $user->id,
            'product_id' => $product->id,
            'company_name' => 'Test Corp',
            'customer_name' => $user->name,
            'contact_email' => $user->email,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone,
            'quantity' => 50,
            'total_estimated' => 1850.00,
            'valid_until' => Carbon::now()->addDays(30),
            'status' => 'pending',
            'customization' => [
                'embroidery_type' => 'Standard Embroidery',
                'placement' => 'Left Chest',
            ]
        ]);

        EcommerceQuotation::create([
            'quote_number' => 'QUO-2026-GHIJKL',
            'user_id' => $user->id,
            'product_id' => $product->id,
            'company_name' => 'Test Corp',
            'customer_name' => $user->name,
            'contact_email' => $user->email,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone,
            'quantity' => 10,
            'total_estimated' => 370.00,
            'valid_until' => Carbon::now()->addDays(15),
            'status' => 'approved',
            'customization' => [
                'embroidery_type' => 'Standard Embroidery',
                'placement' => 'Left Chest',
            ]
        ]);

        // 5. Seed Support Tickets
        $ticket1 = EcommerceTicket::create([
            'ticket_number' => 'TKT-2026-000001',
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order1->id,
            'customer_name' => $user->name,
            'contact_email' => $user->email,
            'contact_phone' => $user->phone,
            'preferred_contact_method' => 'email',
            'subject' => 'Artwork setup assistance',
            'category' => 'design',
            'priority' => 'high',
            'is_urgent' => true,
            'status' => 'open',
            'message' => 'Hello, I uploaded my logo but I am not sure if the thread count matches the specifications. Can you please review?',
            'source_page' => 'checkout',
        ]);

        EcommerceTicketReply::create([
            'ecommerce_ticket_id' => $ticket1->id,
            'user_id' => null, // Admin reply
            'admin_reply' => true,
            'message' => 'Thank you for reaching out. Our design team will review your logo artwork and send a proof shortly.',
        ]);

        $ticket2 = EcommerceTicket::create([
            'ticket_number' => 'TKT-2026-000002',
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'contact_email' => $user->email,
            'preferred_contact_method' => 'email',
            'subject' => 'General Inquiry about Custom Caps',
            'category' => 'sales',
            'priority' => 'medium',
            'is_urgent' => false,
            'status' => 'closed',
            'message' => 'Are you able to embroider on the side of the caps as well?',
            'closed_at' => Carbon::now()->subDays(1),
        ]);

        EcommerceTicketReply::create([
            'ecommerce_ticket_id' => $ticket2->id,
            'user_id' => null, // Admin reply
            'admin_reply' => true,
            'message' => 'Yes, side embroidery is supported for an additional charge of £2.50 per cap. Let us know if you would like to proceed!',
        ]);

        // 6. Seed Disputes
        EcommerceDispute::create([
            'dispute_number' => 'DSP-2026-000001',
            'user_id' => $user->id,
            'order_number' => $order4->order_number,
            'customer_name' => $user->name,
            'type' => 'refund',
            'status' => 'under_review',
            'description' => 'Requested refund for cancelled order before production started.',
            'email' => $user->email,
            'phone' => $user->phone,
            'amount' => 148.00,
        ]);

        // 7. Seed Reviews
        EcommerceReview::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'rating' => 5,
            'title' => 'Top Quality Embroidery!',
            'comment' => 'The stitching on the shirts is perfect, very happy with the quality.',
            'status' => 'approved',
        ]);

        // 8. Seed Wallet Transactions
        EcommerceWalletTransaction::create([
            'user_id' => $user->id,
            'type' => 'credit',
            'amount' => 250.00,
            'balance_after' => 250.00,
            'description' => 'Demo wallet credit',
            'status' => 'completed',
            'reference_id' => 'REF-TXN-1001',
        ]);

        EcommerceWalletTransaction::create([
            'user_id' => $user->id,
            'type' => 'debit',
            'amount' => 74.00,
            'balance_after' => 176.00,
            'description' => 'Purchase of Order ' . $order2->order_number,
            'status' => 'completed',
            'reference_id' => 'REF-TXN-1002',
        ]);

        // 9. Seed Gift Cards
        EcommerceGiftCard::create([
            'user_id' => $user->id,
            'order_id' => $order2->id,
            'code' => 'MEC-GIFT-50-OFF',
            'recipient_name' => $user->name,
            'recipient_email' => $user->email,
            'sender_name' => 'Mecarvi Promo',
            'initial_balance' => 50.00,
            'current_balance' => 50.00,
            'status' => 'active',
            'currency' => 'GBP',
            'expires_at' => Carbon::now()->addYear(),
        ]);

        // 10. Seed Membership
        EcommerceMembership::create([
            'user_id' => $user->id,
            'plan_name' => 'Silver Tier Premium',
            'status' => 'active',
            'price' => 19.99,
            'billing_cycle' => 'monthly',
            'next_billing_date' => Carbon::now()->addMonth(),
        ]);

        // 11. Seed Affiliate info
        EcommerceAffiliate::create([
            'user_id' => $user->id,
            'affiliate_code' => 'TESTUSER10',
            'total_earnings' => 125.00,
            'total_referrals' => 4,
            'status' => 'active',
        ]);

        // 12. Seed Wishlist
        $wishlist = EcommerceWishlist::create([
            'user_id' => $user->id,
            'name' => 'My Main Wishlist',
            'is_default' => true,
            'share_token' => Str::random(32),
        ]);

        $collection1 = EcommerceWishlistCollection::create([
            'ecommerce_wishlist_id' => $wishlist->id,
            'name' => 'Work Outfits',
            'slug' => 'work-outfits',
            'sort_order' => 1,
        ]);

        EcommerceWishlistItem::create([
            'ecommerce_wishlist_id' => $wishlist->id,
            'ecommerce_wishlist_collection_id' => null,
            'product_id' => $product->id,
            'quantity' => 1,
            'saved_price' => $product->price,
            'options' => [
                'size' => 'L',
                'color' => 'Navy',
            ],
            'product_snapshot' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
            ]
        ]);

        $product2 = Product::where('id', '!=', $product->id)->first();
        if (!$product2) {
            $product2 = Product::create([
                'name' => 'Premium Embroidered Baseball Cap',
                'sku' => 'CAP-002',
                'price' => 18.50,
                'sale_price' => 18.50,
                'is_active' => true,
            ]);
        }

        EcommerceWishlistItem::create([
            'ecommerce_wishlist_id' => $wishlist->id,
            'ecommerce_wishlist_collection_id' => $collection1->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'saved_price' => $product2->price,
            'options' => [
                'color' => 'Black',
            ],
            'product_snapshot' => [
                'id' => $product2->id,
                'name' => $product2->name,
                'sku' => $product2->sku,
                'price' => $product2->price,
            ]
        ]);

        // 13. Seed Compare Items
        EcommerceCompareItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        EcommerceCompareItem::create([
            'user_id' => $user->id,
            'product_id' => $product2->id,
        ]);

        // 14. Seed Returns
        EcommerceReturn::create([
            'return_number' => 'RET-2026-999999',
            'user_id' => $user->id,
            'order_id' => $order2->id,
            'order_number' => $order2->order_number,
            'customer_name' => $user->name,
            'reason' => 'Ordered wrong embroidery placement. Need to replace it.',
            'return_items' => [
                [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => 1,
                    'price' => 37.00,
                ]
            ],
            'status' => 'pending',
            'refund_amount' => 37.00,
            'refund_method' => 'stripe',
            'currency' => 'GBP',
            'return_address' => "123 Test Lane\nLondon, EC1A 1BB\nUnited Kingdom",
            'requested_at' => Carbon::now()->subDays(1),
        ]);

        // 15. Seed Conversations & Messages
        $convo1 = EcommerceConversation::create([
            'user_id' => $user->id,
            'subject' => 'Question about bulk pricing',
            'status' => 'open',
            'linked_type' => 'product',
            'linked_id' => $product->id,
            'linked_label' => $product->name,
            'last_customer_message_at' => Carbon::now()->subHours(2),
            'last_admin_message_at' => Carbon::now()->subHours(1),
            'last_message_at' => Carbon::now()->subHours(1),
        ]);

        EcommerceConversationMessage::create([
            'conversation_id' => $convo1->id,
            'sender_id' => $user->id,
            'sender_type' => 'customer',
            'message' => 'Hi, I would like to order 250 of these work shirts. Is there a bulk discount?',
            'read_at' => Carbon::now()->subHours(2),
            'created_at' => Carbon::now()->subHours(2),
        ]);

        EcommerceConversationMessage::create([
            'conversation_id' => $convo1->id,
            'sender_id' => null,
            'sender_type' => 'admin',
            'message' => 'Hello! Yes, for orders over 100 units, we offer a 15% discount. Let me know if you would like me to generate a custom quote for you.',
            'read_at' => Carbon::now()->subMinutes(30),
            'created_at' => Carbon::now()->subHours(1),
        ]);

        $convo2 = EcommerceConversation::create([
            'user_id' => $user->id,
            'subject' => 'Inquiry about order delivery',
            'status' => 'closed',
            'linked_type' => 'order',
            'linked_id' => $order2->id,
            'linked_label' => $order2->order_number,
            'last_customer_message_at' => Carbon::now()->subDays(5),
            'last_admin_message_at' => Carbon::now()->subDays(4),
            'last_message_at' => Carbon::now()->subDays(4),
            'closed_at' => Carbon::now()->subDays(3),
        ]);

        EcommerceConversationMessage::create([
            'conversation_id' => $convo2->id,
            'sender_id' => $user->id,
            'sender_type' => 'customer',
            'message' => 'Can you confirm if order ' . $order2->order_number . ' has been shipped?',
            'read_at' => Carbon::now()->subDays(5),
            'created_at' => Carbon::now()->subDays(5),
        ]);

        EcommerceConversationMessage::create([
            'conversation_id' => $convo2->id,
            'sender_id' => null,
            'sender_type' => 'admin',
            'message' => 'Yes, it was shipped on Monday. You can track it using the tracking link on your dashboard.',
            'read_at' => Carbon::now()->subDays(4),
            'created_at' => Carbon::now()->subDays(4),
        ]);
    }
}
