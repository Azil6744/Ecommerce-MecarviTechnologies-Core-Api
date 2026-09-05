<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\EcommerceOrder;
use App\Models\EcommerceOrderItem;
use App\Models\EcommerceDispute;
use App\Models\EcommerceCustomerFile;
use App\Models\EcommerceGiftCard;
use App\Models\EcommerceGiftCardTransaction;
use App\Models\EcommerceLoyaltyTransaction;
use App\Models\EcommerceMembership;
use App\Models\EcommerceTicket;
use App\Models\EcommerceTicketReply;
use App\Models\EcommerceCustomerVerification;
use App\Models\ProductReport;
use App\Models\EcommerceCoupon;
use App\Models\Donation;
use App\Models\Charity;
use App\Models\EcommerceWalletTransaction;
use App\Models\EcommerceConversation;
use App\Models\EcommerceConversationMessage;
use App\Models\EcommerceReferral;
use App\Models\UserLoginHistory;
use App\Models\UserAdminChange;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserProfileTabsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->orWhere('role', 'super_admin')->first();
        $adminId = $admin ? $admin->id : null;

        // Ensure users have populated profile fields
        $users = User::all();
        foreach ($users as $user) {
            $isBiz = in_array($user->role, ['seller', 'business', 'business_user', 'company']);
            $prefix = $isBiz ? 'BIZ-' : 'CUST-';

            if (empty($user->customer_account_number)) {
                $user->customer_account_number = $prefix . str_pad($user->id, 7, '0', STR_PAD_LEFT);
            }
            if (empty($user->address)) {
                $user->address = "Grand Anse, St. George's Grenada, W.I.";
            }
            if (empty($user->dob)) {
                $user->dob = Carbon::create(1990, 6, 15);
            }
            if (empty($user->gender)) {
                $user->gender = 'Male';
            }
            if (empty($user->membership_status)) {
                $user->membership_status = $isBiz ? 'Gold Partner' : 'Gold Member';
            }
            if (empty($user->avatar)) {
                $user->avatar = "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&auto=format&fit=crop&q=80";
            }
            if ($isBiz) {
                $user->business_name = $user->name . " (TopStitch Embroidery Ltd)";
                $user->tax_id = "TAX-GD-8849201";
                $user->business_type = "Textile & Embroidery (LLC)";
            }
            if (!$user->loyalty_points) {
                $user->loyalty_points = 4875;
            }
            $user->save();
        }

        // Specifically populate primary customer user (e.g. ID 51 or first customer)
        $primaryCustomer = User::where('role', 'customer')->first();
        if ($primaryCustomer) {
            $this->seedCustomerTabsData($primaryCustomer, $adminId);
        }

        // Specifically populate primary seller/business user (e.g. ID 57 or first seller)
        $primarySeller = User::where('role', 'seller')->orWhere('role', 'business')->first();
        if ($primarySeller && $primarySeller->id !== ($primaryCustomer ? $primaryCustomer->id : null)) {
            $this->seedCustomerTabsData($primarySeller, $adminId);
        }
    }

    private function seedCustomerTabsData(User $user, ?int $adminId): void
    {
        $now = Carbon::now();

        // 1. Orders & Order Items
        if (EcommerceOrder::where('user_id', $user->id)->count() === 0) {
            $orderTemplates = [
                ['number' => 'ORD-2026-00156', 'amount' => 1245.00, 'status' => 'completed', 'items' => 12, 'days' => 1, 'payment' => 'Credit Card (•••• 4242)', 'tracking' => '1Z9999AA10123456784'],
                ['number' => 'ORD-2026-00154', 'amount' => 2560.00, 'status' => 'shipped', 'items' => 15, 'days' => 3, 'payment' => 'PayPal', 'tracking' => '1Z9999AA10123456783'],
                ['number' => 'ORD-2026-00155', 'amount' => 875.50, 'status' => 'completed', 'items' => 8, 'days' => 4, 'payment' => 'Credit Card (•••• 4242)', 'tracking' => '1Z9999AA10123456782'],
                ['number' => 'ORD-2026-00153', 'amount' => 320.00, 'status' => 'refunded', 'items' => 5, 'days' => 5, 'payment' => 'Credit Card (•••• 4242)', 'tracking' => '1Z9999AA10123456781'],
                ['number' => 'ORD-2026-00152', 'amount' => 1150.75, 'status' => 'completed', 'items' => 15, 'days' => 6, 'payment' => 'Credit Card (•••• 4242)', 'tracking' => '1Z9999AA10123456780'],
                ['number' => 'ORD-2026-00151', 'amount' => 690.00, 'status' => 'shipped', 'items' => 8, 'days' => 7, 'payment' => 'PayPal', 'tracking' => '1Z9999AA10123456779'],
                ['number' => 'ORD-2026-00150', 'amount' => 1890.00, 'status' => 'completed', 'items' => 9, 'days' => 8, 'payment' => 'Credit Card (•••• 4242)', 'tracking' => '1Z9999AA10123456778'],
                ['number' => 'ORD-2026-00149', 'amount' => 1275.25, 'status' => 'completed', 'items' => 7, 'days' => 9, 'payment' => 'Credit Card (•••• 4242)', 'tracking' => '1Z9999AA10123456777'],
            ];

            foreach ($orderTemplates as $tmpl) {
                $orderNumber = 'ORD-' . $user->id . '-' . substr($tmpl['number'], 4);
                $order = EcommerceOrder::create([
                    'user_id' => $user->id,
                    'order_number' => $orderNumber,
                    'status' => $tmpl['status'],
                    'total_amount' => $tmpl['amount'],
                    'subtotal' => $tmpl['amount'] * 0.9,
                    'tax_amount' => $tmpl['amount'] * 0.1,
                    'payment_method' => $tmpl['payment'],
                    'payment_status' => $tmpl['status'] === 'refunded' ? 'refunded' : 'paid',
                    'tracking_number' => $tmpl['tracking'],
                    'currency' => 'USD',
                    'order_date' => $now->copy()->subDays($tmpl['days']),
                    'customer_name' => $user->name,
                    'customer_email' => $user->email,
                    'created_at' => $now->copy()->subDays($tmpl['days'])->setHour(10)->setMinute(23),
                    'updated_at' => $now->copy()->subDays($tmpl['days'])->setHour(10)->setMinute(23),
                ]);

                // Create Order items
                EcommerceOrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => null,
                    'product_name' => 'Custom Embroidery Polo Shirt',
                    'quantity' => $tmpl['items'],
                    'unit_price' => round($tmpl['amount'] / $tmpl['items'], 2),
                    'total_price' => $tmpl['amount'],
                    'product_options' => [
                        'image_url' => 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=300&auto=format&fit=crop&q=80',
                    ],
                ]);
            }
        }

        // 2. Login History
        if (UserLoginHistory::where('user_id', $user->id)->count() === 0) {
            $loginLogs = [
                ['device_type' => 'desktop', 'device_title' => 'Chrome on Windows', 'device_details' => "Windows 11\nChrome 125.0.0.0", 'location' => "Grand Anse, St. George's\nGrenada, W.I.", 'ip_address' => '192.168.1.1', 'network' => 'Local Network', 'status' => 'Successful', 'hours' => 2],
                ['device_type' => 'mobile', 'device_title' => 'Safari on iPhone', 'device_details' => "iPhone 14\nSafari 17.4", 'location' => "Grand Anse, St. George's\nGrenada, W.I.", 'ip_address' => '203.0.113.45', 'network' => 'ISP', 'status' => 'Successful', 'hours' => 26],
                ['device_type' => 'desktop', 'device_title' => 'Firefox on Mac', 'device_details' => "MacBook Pro\nFirefox 126.0", 'location' => "St. George's\nGrenada, W.I.", 'ip_address' => '198.51.100.23', 'network' => 'ISP', 'status' => 'Successful', 'hours' => 30],
                ['device_type' => 'mobile', 'device_title' => 'Chrome on Android', 'device_details' => "Android 13\nChrome 124.0.0.0", 'location' => "Grand Anse, St. George's\nGrenada, W.I.", 'ip_address' => '192.0.2.10', 'network' => 'Mobile Network', 'status' => 'Failed', 'hours' => 50],
                ['device_type' => 'desktop', 'device_title' => 'Edge on Windows', 'device_details' => "Windows 11\nEdge 124.0.0.0", 'location' => "St. George's\nGrenada, W.I.", 'ip_address' => '192.0.2.10', 'network' => 'Mobile Network', 'status' => 'Successful', 'hours' => 52],
                ['device_type' => 'desktop', 'device_title' => 'MacBook Air', 'device_details' => 'Safari 17.3', 'location' => "St. George's\nGrenada, W.I.", 'ip_address' => '203.0.113.89', 'network' => 'ISP', 'status' => 'Successful', 'hours' => 74],
            ];

            foreach ($loginLogs as $log) {
                UserLoginHistory::create([
                    'user_id' => $user->id,
                    'device_type' => $log['device_type'],
                    'device_title' => $log['device_title'],
                    'device_details' => $log['device_details'],
                    'location' => $log['location'],
                    'ip_address' => $log['ip_address'],
                    'network' => $log['network'],
                    'status' => $log['status'],
                    'created_at' => $now->copy()->subHours($log['hours']),
                    'updated_at' => $now->copy()->subHours($log['hours']),
                ]);
            }
        }

        // 3. Admin Changes
        if (UserAdminChange::where('user_id', $user->id)->count() === 0) {
            $adminChanges = [
                ['title' => 'Account Status Updated', 'actor_name' => 'Admin User', 'actor_role' => 'Super Administrator', 'description' => 'Customer account status has been updated.', 'changed_fields' => 'Status', 'before_value' => 'Active', 'after_value' => 'Active (Verified)', 'days' => 1],
                ['title' => 'Email Address Updated', 'actor_name' => 'Sarah J.', 'actor_role' => 'Support Team', 'description' => 'Customer email address verified.', 'changed_fields' => 'Email Address', 'before_value' => 'marcus@topstitch.com', 'after_value' => 'marcus@topstitch.com', 'days' => 2],
                ['title' => 'Phone Number Updated', 'actor_name' => 'Sarah J.', 'actor_role' => 'Support Team', 'description' => 'Customer phone number verified.', 'changed_fields' => 'Phone Number', 'before_value' => '+1 (473) 405-7896', 'after_value' => '+1 (473) 405-7896', 'days' => 2],
                ['title' => 'Address Updated', 'actor_name' => 'Sarah J.', 'actor_role' => 'Support Team', 'description' => 'Customer address information verified.', 'changed_fields' => 'Address', 'before_value' => "Grand Anse, St. George's, Grenada", 'after_value' => "Grand Anse, St. George's Grenada, W.I.", 'days' => 3],
                ['title' => 'Membership Status Updated', 'actor_name' => 'Admin User', 'actor_role' => 'Super Administrator', 'description' => 'Customer upgraded to Gold tier.', 'changed_fields' => 'Membership Status', 'before_value' => 'Silver Member', 'after_value' => 'Gold Member', 'days' => 4],
            ];

            foreach ($adminChanges as $chg) {
                UserAdminChange::create([
                    'user_id' => $user->id,
                    'admin_id' => $adminId,
                    'actor_name' => $chg['actor_name'],
                    'actor_role' => $chg['actor_role'],
                    'title' => $chg['title'],
                    'description' => $chg['description'],
                    'changed_fields' => $chg['changed_fields'],
                    'before_value' => $chg['before_value'],
                    'after_value' => $chg['after_value'],
                    'created_at' => $now->copy()->subDays($chg['days'])->setHour(11)->setMinute(32),
                    'updated_at' => $now->copy()->subDays($chg['days'])->setHour(11)->setMinute(32),
                ]);
            }
        }

        // 4. Messages / Conversations
        if (EcommerceConversation::where('user_id', $user->id)->count() === 0) {
            $conv1 = EcommerceConversation::create([
                'user_id' => $user->id,
                'subject' => 'Inquiry About Custom Embroidery Order',
                'status' => 'open',
                'last_message_at' => $now->copy()->subHours(2),
                'created_at' => $now->copy()->subHours(4),
            ]);

            EcommerceConversationMessage::create([
                'conversation_id' => $conv1->id,
                'sender_type' => 'customer',
                'sender_id' => $user->id,
                'message' => 'Hi, I would like to know the price for embroidering a logo on 50 polo shirts. Please let me know the cost and estimated delivery time. Thanks!',
                'created_at' => $now->copy()->subHours(4),
            ]);

            EcommerceConversationMessage::create([
                'conversation_id' => $conv1->id,
                'sender_type' => 'admin',
                'sender_id' => $adminId ?: 1,
                'message' => "Hello {$user->name},\nThank you for reaching out! For 50 polo shirts with a standard logo (4\" size), the price is $275.00. Estimated delivery time is 7-10 business days.\nLet us know if you need any changes.",
                'created_at' => $now->copy()->subHours(3),
            ]);

            EcommerceConversationMessage::create([
                'conversation_id' => $conv1->id,
                'sender_type' => 'customer',
                'sender_id' => $user->id,
                'message' => 'That sounds good. Can you send me a sample of the logo placement?',
                'created_at' => $now->copy()->subHours(2),
            ]);

            EcommerceConversationMessage::create([
                'conversation_id' => $conv1->id,
                'sender_type' => 'admin',
                'sender_id' => $adminId ?: 1,
                'message' => 'Sure! Please check the attached mockup for the logo placement on the polo shirt.',
                'created_at' => $now->copy()->subHours(1),
            ]);

            $conv2 = EcommerceConversation::create([
                'user_id' => $user->id,
                'subject' => 'Order #ORD-7894 Production Update',
                'status' => 'in_progress',
                'last_message_at' => $now->copy()->subDays(1),
                'created_at' => $now->copy()->subDays(1),
            ]);

            EcommerceConversationMessage::create([
                'conversation_id' => $conv2->id,
                'sender_type' => 'customer',
                'sender_id' => $user->id,
                'message' => 'Can you provide an update on the production for our bulk cap order?',
                'created_at' => $now->copy()->subDays(1),
            ]);
        }

        // 5. Loyalty Points Transactions
        if (EcommerceLoyaltyTransaction::where('user_id', $user->id)->count() === 0) {
            $loyaltyItems = [
                ['type' => 'earned', 'points' => 250, 'reason' => 'Purchase Earned', 'details' => 'Order #ORD-8844', 'days' => 1],
                ['type' => 'earned', 'points' => 200, 'reason' => 'Referral Signup Bonus', 'details' => 'Referred by: John D.', 'days' => 3],
                ['type' => 'earned', 'points' => 100, 'reason' => 'Review Bonus', 'details' => 'Product Review', 'days' => 6],
                ['type' => 'earned', 'points' => 1000, 'reason' => 'Membership Renewal Bonus', 'details' => 'Mecarvi Gold', 'days' => 9],
                ['type' => 'earned', 'points' => 300, 'reason' => 'Birthday Bonus', 'details' => 'Happy Birthday!', 'days' => 13],
                ['type' => 'redeemed', 'points' => -500, 'reason' => '$5 Off Coupon', 'details' => 'Order #ORD-8844', 'days' => 13],
                ['type' => 'redeemed', 'points' => -1500, 'reason' => 'Free Shipping', 'details' => 'Order #ORD-7761', 'days' => 26],
                ['type' => 'redeemed', 'points' => -1000, 'reason' => '$10 Off Coupon', 'details' => 'Order #ORD-6521', 'days' => 41],
                ['type' => 'adjustment', 'points' => 500, 'reason' => 'Manual Bonus', 'details' => 'Thank you for your loyalty!', 'days' => 2],
                ['type' => 'adjustment', 'points' => -300, 'reason' => 'Point Deduction', 'details' => 'Order cancellation', 'days' => 10],
            ];

            foreach ($loyaltyItems as $li) {
                EcommerceLoyaltyTransaction::create([
                    'user_id' => $user->id,
                    'transaction_type' => $li['type'],
                    'points' => $li['points'],
                    'dollar_value' => abs($li['points']) * 0.01,
                    'status' => 'completed',
                    'reason' => $li['reason'],
                    'reason_details' => $li['details'],
                    'created_at' => $now->copy()->subDays($li['days'])->setHour(10)->setMinute(32),
                    'updated_at' => $now->copy()->subDays($li['days'])->setHour(10)->setMinute(32),
                ]);
            }
        }

        // 6. Gift Cards
        if (EcommerceGiftCard::where('user_id', $user->id)->count() === 0) {
            $giftCards = [
                ['code' => 'MEGC' . $user->id . '5X8KL2Q9', 'amount' => 50.00, 'current' => 0.00, 'status' => 'redeemed', 'recipient_email' => $user->email, 'sender_name' => 'Store Admin', 'days' => 10],
                ['code' => 'MEGC' . $user->id . 'B7H3M9P1', 'amount' => 100.00, 'current' => 100.00, 'status' => 'active', 'recipient_email' => 'jennifer@gmail.com', 'sender_name' => $user->name, 'days' => 14],
                ['code' => 'MEGC' . $user->id . 'Q2D7R8F5', 'amount' => 75.00, 'current' => 0.00, 'status' => 'redeemed', 'recipient_email' => $user->email, 'sender_name' => 'Promotion', 'days' => 22],
                ['code' => 'MEGC' . $user->id . 'V8F2K6M4', 'amount' => 250.00, 'current' => 250.00, 'status' => 'active', 'recipient_email' => $user->email, 'sender_name' => 'Holiday Reward', 'days' => 5],
            ];

            foreach ($giftCards as $gc) {
                EcommerceGiftCard::create([
                    'user_id' => $user->id,
                    'code' => $gc['code'],
                    'initial_balance' => $gc['amount'],
                    'current_balance' => $gc['current'],
                    'status' => $gc['status'],
                    'recipient_name' => $user->name,
                    'recipient_email' => $gc['recipient_email'],
                    'sender_name' => $gc['sender_name'],
                    'currency' => 'USD',
                    'expires_at' => $now->copy()->addMonths(6),
                    'created_at' => $now->copy()->subDays($gc['days']),
                    'updated_at' => $now->copy()->subDays($gc['days']),
                ]);
            }
        }

        // 7. Support Tickets
        if (EcommerceTicket::where('user_id', $user->id)->count() === 0) {
            $tickets = [
                ['number' => '#TKT-' . $user->id . '-10081', 'title' => 'Order not received', 'desc' => 'I placed an order on May 8th but haven\'t received it yet.', 'status' => 'resolved', 'priority' => 'Medium', 'category' => 'Order & Delivery', 'days' => 11],
                ['number' => '#TKT-' . $user->id . '-10075', 'title' => 'Payment issue', 'desc' => 'I was charged twice for the same order. Please assist.', 'status' => 'in_progress', 'priority' => 'High', 'category' => 'Billing & Payment', 'days' => 8],
                ['number' => '#TKT-' . $user->id . '-10069', 'title' => 'Customization not showing', 'desc' => 'The custom text I added is not showing in the preview.', 'status' => 'in_progress', 'priority' => 'Medium', 'category' => 'Product & Artwork', 'days' => 10],
                ['number' => '#TKT-' . $user->id . '-10062', 'title' => 'Shipping address change', 'desc' => 'I need to update my shipping address for my recent order.', 'status' => 'closed', 'priority' => 'High', 'category' => 'Order & Delivery', 'days' => 12],
            ];

            foreach ($tickets as $tkt) {
                $ticket = EcommerceTicket::create([
                    'ticket_number' => $tkt['number'],
                    'user_id' => $user->id,
                    'customer_name' => $user->name,
                    'contact_email' => $user->email,
                    'contact_phone' => $user->phone,
                    'subject' => $tkt['title'],
                    'category' => $tkt['category'],
                    'priority' => $tkt['priority'],
                    'status' => $tkt['status'],
                    'message' => $tkt['desc'],
                    'created_at' => $now->copy()->subDays($tkt['days'])->setHour(10)->setMinute(32),
                    'updated_at' => $now->copy()->subDays($tkt['days'])->setHour(10)->setMinute(32),
                ]);

                EcommerceTicketReply::create([
                    'ecommerce_ticket_id' => $ticket->id,
                    'user_id' => $adminId ?: $user->id,
                    'admin_reply' => true,
                    'message' => "Hello {$user->name}, thank you for contacting support regarding your issue. We are reviewing this right now.",
                    'created_at' => $now->copy()->subDays($tkt['days'])->addMinutes(15),
                ]);
            }
        }

        // 8. Customer Verifications
        if (EcommerceCustomerVerification::where('user_id', $user->id)->count() === 0) {
            EcommerceCustomerVerification::create([
                'user_id' => $user->id,
                'document_type' => 'Passport',
                'status' => 'verified',
                'notes' => 'Identity verified by Administrator.',
                'created_at' => $now->copy()->subDays(10),
            ]);

            EcommerceCustomerVerification::create([
                'user_id' => $user->id,
                'document_type' => 'Utility Bill',
                'status' => 'verified',
                'notes' => 'Address verified successfully.',
                'created_at' => $now->copy()->subDays(9),
            ]);
        }

        // 9. Order Disputes
        if (EcommerceDispute::where('user_id', $user->id)->count() === 0) {
            $disputes = [
                ['dispute_number' => 'DSP-' . $user->id . '-001', 'order_number' => 'ORD-' . $user->id . '-001258', 'type' => 'Wrong Item', 'amount' => 85.00, 'status' => 'OPEN', 'desc' => 'Customer received a different color than what was ordered.', 'days' => 7],
                ['dispute_number' => 'DSP-' . $user->id . '-002', 'order_number' => 'ORD-' . $user->id . '-001196', 'type' => 'Quality Issue', 'amount' => 65.00, 'status' => 'UNDER REVIEW', 'desc' => 'Customer claims embroidery quality is not as expected.', 'days' => 11],
                ['dispute_number' => 'DSP-' . $user->id . '-003', 'order_number' => 'ORD-' . $user->id . '-001054', 'type' => 'Late Delivery', 'amount' => 0.00, 'status' => 'RESOLVED', 'desc' => 'Package was delivered later than the promised delivery date.', 'days' => 19],
                ['dispute_number' => 'DSP-' . $user->id . '-004', 'order_number' => 'ORD-' . $user->id . '-000987', 'type' => 'Damaged Item', 'amount' => 45.00, 'status' => 'CLOSED', 'desc' => 'Item arrived damaged during shipping.', 'days' => 26],
            ];

            foreach ($disputes as $dsp) {
                EcommerceDispute::create([
                    'dispute_number' => $dsp['dispute_number'],
                    'user_id' => $user->id,
                    'order_number' => $dsp['order_number'],
                    'customer_name' => $user->name,
                    'email' => $user->email,
                    'type' => $dsp['type'],
                    'status' => $dsp['status'],
                    'amount' => $dsp['amount'],
                    'description' => $dsp['desc'],
                    'created_at' => $now->copy()->subDays($dsp['days']),
                    'updated_at' => $now->copy()->subDays($dsp['days']),
                ]);
            }
        }

        // 10. Product Reports
        if (ProductReport::where('user_id', $user->id)->count() === 0) {
            $reports = [
                ['code' => 'RPT-' . $user->id . '-000124', 'name' => 'Mecarvi Embroidery Baseball Cap', 'issue' => 'Copyright / Trademark infringement', 'status' => 'UNDER REVIEW', 'days' => 7],
                ['code' => 'RPT-' . $user->id . '-000123', 'name' => 'Mecarvi Embroidery Hoodie', 'issue' => 'Inappropriate content', 'status' => 'UNDER REVIEW', 'days' => 8],
                ['code' => 'RPT-' . $user->id . '-000122', 'name' => 'Mecarvi Embroidery Backpack', 'issue' => 'Product not as described', 'status' => 'ACTION TAKEN', 'days' => 10],
                ['code' => 'RPT-' . $user->id . '-000121', 'name' => 'Mecarvi Embroidery Polo Shirt', 'issue' => 'Low quality / Poor craftsmanship', 'status' => 'ACTION TAKEN', 'days' => 12],
                ['code' => 'RPT-' . $user->id . '-000120', 'name' => 'Mecarvi Embroidery Duffel Bag', 'issue' => 'Misleading product information', 'status' => 'ACTION TAKEN', 'days' => 15],
                ['code' => 'RPT-' . $user->id . '-000119', 'name' => 'Mecarvi Embroidery Tote Bag', 'issue' => 'Accidental order / No issue found', 'status' => 'NO VIOLATION', 'days' => 17],
            ];

            foreach ($reports as $rpt) {
                ProductReport::create([
                    'user_id' => $user->id,
                    'report_code' => $rpt['code'],
                    'product_name' => $rpt['name'],
                    'issue' => $rpt['issue'],
                    'description' => $rpt['issue'],
                    'status' => $rpt['status'],
                    'customer_name' => $user->name,
                    'customer_email' => $user->email,
                    'product_image' => 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=300&auto=format&fit=crop&q=80',
                    'created_at' => $now->copy()->subDays($rpt['days']),
                    'updated_at' => $now->copy()->subDays($rpt['days']),
                ]);
            }
        }

        // 11. Customer Files / Downloads
        if (EcommerceCustomerFile::where('user_id', $user->id)->count() === 0) {
            $files = [
                ['name' => 'Mecarvi-Logo.dst', 'type' => 'DST', 'size' => 2569011, 'cat' => 'Digitized Files', 'days' => 1],
                ['name' => 'Baseball-Cap.pes', 'type' => 'PES', 'size' => 4320256, 'cat' => 'Digitized Files', 'days' => 1],
                ['name' => 'Floral-Design.exp', 'type' => 'EXP', 'size' => 3963617, 'cat' => 'Digitized Files', 'days' => 3],
                ['name' => 'Company-Logo.ai', 'type' => 'AI', 'size' => 2076180, 'cat' => 'Vector Files', 'days' => 6],
                ['name' => 'Order-INV-000123.pdf', 'type' => 'PDF', 'size' => 193536, 'cat' => 'Order Documents', 'days' => 9],
                ['name' => 'Hoodie-Mockup.jpg', 'type' => 'JPG', 'size' => 2254438, 'cat' => 'Artwork & Designs', 'days' => 11],
            ];

            foreach ($files as $fl) {
                EcommerceCustomerFile::create([
                    'user_id' => $user->id,
                    'file_name' => $fl['name'],
                    'file_path' => 'downloads/' . $fl['name'],
                    'file_type' => $fl['type'],
                    'category' => $fl['cat'],
                    'size_bytes' => $fl['size'],
                    'download_count' => 3,
                    'status' => 'active',
                    'created_at' => $now->copy()->subDays($fl['days']),
                    'updated_at' => $now->copy()->subDays($fl['days']),
                ]);
            }
        }

        // 12. Memberships History
        if (EcommerceMembership::where('user_id', $user->id)->count() === 0) {
            EcommerceMembership::create([
                'user_id' => $user->id,
                'plan_name' => 'Mecarvi Platinum One',
                'status' => 'active',
                'price' => 480.00,
                'billing_cycle' => 'yearly',
                'next_billing_date' => $now->copy()->addYear(),
                'created_at' => $now->copy()->subDays(7),
            ]);

            EcommerceMembership::create([
                'user_id' => $user->id,
                'plan_name' => 'Mecarvi Gold',
                'status' => 'expired',
                'price' => 480.00,
                'billing_cycle' => 'yearly',
                'next_billing_date' => $now->copy()->subDays(7),
                'created_at' => $now->copy()->subYear()->subDays(7),
            ]);
        }

        // 13. Donations History
        if (Donation::where('user_id', $user->id)->orWhere('donor_email', $user->email)->count() === 0) {
            $donations = [
                ['charity' => 'Grenada Red Cross', 'cat' => 'Emergency Relief', 'amount' => 100.00, 'method' => 'Visa **** 4242', 'brand' => 'VISA', 'days' => 1],
                ['charity' => 'Grenada SPCA', 'cat' => 'Animal Welfare Support', 'amount' => 50.00, 'method' => 'Mastercard **** 8888', 'brand' => 'MC', 'days' => 36],
                ['charity' => 'Carriacou Education Fund', 'cat' => 'Student Scholarship', 'amount' => 75.00, 'method' => 'Visa **** 4242', 'brand' => 'VISA', 'days' => 77],
                ['charity' => 'Grenada Cancer Society', 'cat' => 'Cancer Awareness Campaign', 'amount' => 120.00, 'method' => 'American Express **** 1005', 'brand' => 'AMEX', 'days' => 100],
            ];

            foreach ($donations as $don) {
                Donation::create([
                    'user_id' => $user->id,
                    'donor_name' => $user->name,
                    'donor_email' => $user->email,
                    'charity_name' => $don['charity'],
                    'charity_category' => $don['cat'],
                    'charity_logo_type' => 'generic_charity',
                    'amount' => $don['amount'],
                    'payment_method_brand' => $don['brand'],
                    'payment_method_details' => $don['method'],
                    'status' => 'Completed',
                    'created_at' => $now->copy()->subDays($don['days']),
                    'updated_at' => $now->copy()->subDays($don['days']),
                ]);
            }
        }

        // 14. Coupons & Deals
        if (EcommerceCoupon::count() === 0) {
            EcommerceCoupon::create([
                'code' => 'MARCUS20',
                'title' => '20% Off Customer Special',
                'subtitle' => 'Special discount for valued customer',
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'min_order_amount' => 50.00,
                'usage_limit' => 100,
                'used_count' => 1,
                'is_active' => true,
                'starts_at' => $now->copy()->subDays(5),
                'expires_at' => $now->copy()->addDays(25),
            ]);

            EcommerceCoupon::create([
                'code' => 'FREESHIP',
                'title' => 'Free Express Shipping',
                'subtitle' => 'Free shipping on any order',
                'discount_type' => 'free_shipping',
                'discount_value' => 0,
                'min_order_amount' => 0.00,
                'usage_limit' => 500,
                'used_count' => 12,
                'is_active' => true,
                'starts_at' => $now->copy()->subDays(10),
                'expires_at' => $now->copy()->addDays(20),
            ]);

            EcommerceCoupon::create([
                'code' => 'WELCOME10',
                'title' => '10% Off Welcome Promo',
                'subtitle' => 'Welcome gift for newly joined members',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'min_order_amount' => 30.00,
                'usage_limit' => 1000,
                'used_count' => 45,
                'is_active' => true,
                'starts_at' => $now->copy()->subDays(30),
                'expires_at' => $now->copy()->addDays(60),
            ]);
        }
    }
}
