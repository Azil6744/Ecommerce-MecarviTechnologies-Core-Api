<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\EcommerceOrder;
use App\Models\EcommerceOrderItem;
use App\Models\EcommerceOrderVerification;
use Carbon\Carbon;

class SeedVerificationTest extends Command
{
    protected $signature = 'test:verification {action=create : Action: create, approve, decline, reset} {--email= : User email}';
    protected $description = 'Seed or update test order verifications for testing the verification flow';

    public function handle(): int
    {
        $action = $this->argument('action');
        $email = $this->option('email') ?: 'developmentwithazil@gmail.com';

        $user = User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
        if (!$user) {
            $user = User::first();
        }

        if (!$user) {
            $this->error('No user found in database.');
            return 1;
        }

        $orderNumber = 'ORD-2026-784512';

        if ($action === 'create') {
            // Delete existing test order / verification if any
            EcommerceOrderVerification::where('order_number', $orderNumber)->delete();
            EcommerceOrder::where('order_number', $orderNumber)->delete();

            $order = EcommerceOrder::create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->phone ?? '+1 555-0199',
                'status' => 'pending',
                'payment_status' => 'pending_verification',
                'payment_method' => 'Mastercard ending 7890',
                'currency' => 'USD',
                'subtotal' => 145.00,
                'total_amount' => 145.00,
                'order_date' => Carbon::now(),
            ]);

            EcommerceOrderItem::create([
                'order_id' => $order->id,
                'product_name' => 'Custom Embroidered Hoodies',
                'product_sku' => 'HOODIE-EMB-01',
                'quantity' => 2,
                'unit_price' => 72.50,
                'total_price' => 145.00,
                'product_options' => [
                    'Color' => 'Navy Blue',
                    'Size' => 'L',
                    'Stitch Type' => 'Chest Embroidery'
                ]
            ]);

            $verification = EcommerceOrderVerification::create([
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $user->id,
                'site_slug' => 'embroidery',
                'risk_level' => 'high',
                'flag_reason' => 'First-time high-value transaction. Billing address confirmation required.',
                'reason_title' => 'Why do I need to verify my order?',
                'reason_text' => 'Our system flagged this transaction for standard payment card and identity verification before sending to production.',
                'status' => 'action_required',
                'deadline_at' => Carbon::now()->addDays(3),
                'total_amount' => 145.00,
                'payment_method' => 'Mastercard ending 7890',
                'product_name' => 'Custom Embroidered Hoodies',
                'product_specs' => 'Navy Blue • Size L • Chest Embroidery',
                'item_count' => 2,
                'product_image' => '/assets/images/order-verification/stickers.jpg',
                'required_documents' => [
                    'Payment Card (Front & Back)',
                    'Photo ID'
                ],
                'submitted_documents' => [
                    ['id' => 'd1', 'name' => 'Payment Card (Front & Back)', 'type' => 'card', 'status' => 'pending'],
                    ['id' => 'd2', 'name' => 'Photo ID', 'type' => 'id', 'status' => 'pending'],
                ],
                'timeline' => [
                    ['title' => 'Verification Request Sent', 'date' => Carbon::now()->format('M d, Y • h:i A'), 'completed' => true],
                    ['title' => 'Your Response Received', 'date' => null, 'completed' => false],
                    ['title' => 'Decision Made', 'date' => null, 'completed' => false],
                ],
                'internal_notes' => [
                    'Automated security flag triggered. Risk level: High.'
                ]
            ]);

            $this->info("Created test order verification for {$user->name} ({$user->email}): {$orderNumber}");
            $this->info("Status: ACTION REQUIRED. You can now test uploading documents in the web panel.");
            return 0;
        }

        if ($action === 'approve') {
            $verification = EcommerceOrderVerification::where('order_number', $orderNumber)->first();
            if (!$verification) {
                $this->error("Verification record {$orderNumber} not found.");
                return 1;
            }

            $submittedDocs = $verification->submitted_documents ?? [];
            $verifiedDocs = array_map(function($doc) {
                $doc['status'] = 'verified';
                return $doc;
            }, $submittedDocs);

            $timeline = $verification->timeline ?? [];
            $timeline[] = [
                'title' => 'Verification Approved',
                'date' => Carbon::now()->format('M d, Y • h:i A'),
                'completed' => true
            ];

            $verification->update([
                'status' => 'verified',
                'verified_at' => Carbon::now(),
                'submitted_documents' => $verifiedDocs,
                'timeline' => $timeline,
            ]);

            $this->info("Verification {$orderNumber} marked as APPROVED / COMPLETED.");
            return 0;
        }

        if ($action === 'decline') {
            $verification = EcommerceOrderVerification::where('order_number', $orderNumber)->first();
            if (!$verification) {
                $this->error("Verification record {$orderNumber} not found.");
                return 1;
            }

            $timeline = $verification->timeline ?? [];
            $timeline[] = [
                'title' => 'Decision Made (Declined)',
                'date' => Carbon::now()->format('M d, Y • h:i A'),
                'completed' => true
            ];

            $verification->update([
                'status' => 'declined',
                'declined_at' => Carbon::now(),
                'decline_reason' => 'Unable to verify payment ownership and cardholder ID.',
                'timeline' => $timeline,
            ]);

            $this->info("Verification {$orderNumber} marked as DECLINED.");
            return 0;
        }

        if ($action === 'reset') {
            EcommerceOrderVerification::where('order_number', $orderNumber)->delete();
            EcommerceOrder::where('order_number', $orderNumber)->delete();
            $this->info("Test verification {$orderNumber} removed.");
            return 0;
        }

        $this->error("Unknown action: {$action}");
        return 1;
    }
}
