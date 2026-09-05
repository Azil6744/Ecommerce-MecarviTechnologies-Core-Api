<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EcommerceOrderVerification;
use App\Models\EcommerceOrder;
use App\Models\User;
use Carbon\Carbon;

class EcommerceOrderVerificationSeeder extends Seeder
{
    public function run(): void
    {
        $firstOrder = EcommerceOrder::first();
        $defaultOrderId = $firstOrder ? $firstOrder->id : null;
        $defaultUserId = $firstOrder ? $firstOrder->user_id : null;

        $records = [
            [
                'order_id' => $defaultOrderId,
                'order_number' => 'OR-2024-1456',
                'user_id' => $defaultUserId,
                'site_slug' => 'embroidery',
                'risk_level' => 'high',
                'flag_reason' => 'Multiple failed authorization attempts before success.',
                'reason_title' => 'Why do I need to verify my order?',
                'reason_text' => 'Our system detected an issue with your payment. To protect your account and ensure order security, we need a few documents from you.',
                'status' => 'action_required',
                'deadline_at' => Carbon::now()->addDays(3),
                'total_amount' => 68.75,
                'payment_method' => 'Mastercard ending 7890',
                'product_name' => 'Custom Stickers',
                'product_specs' => 'Vinyl • Waterproof',
                'item_count' => 3,
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
                    ['title' => 'Verification Request Sent', 'date' => 'May 22, 2024 • 08:40 AM', 'completed' => true],
                    ['title' => 'Response Received', 'date' => null, 'completed' => false],
                    ['title' => 'Review In Progress', 'date' => null, 'completed' => false],
                ],
                'internal_notes' => [
                    'Risk score 85. Multiple failed CVC attempts.'
                ],
            ],
            [
                'order_id' => $defaultOrderId,
                'order_number' => 'OR-2024-1458',
                'user_id' => $defaultUserId,
                'site_slug' => 'embroidery',
                'risk_level' => 'medium',
                'flag_reason' => 'Routine verification for high-volume transactions.',
                'reason_title' => 'Documents Requested',
                'reason_text' => 'Please provide the missing payment card back and photo ID.',
                'status' => 'pending_documents',
                'deadline_at' => Carbon::now()->addDays(5),
                'total_amount' => 129.50,
                'payment_method' => 'Visa ending 4242',
                'product_name' => 'Premium Business Cards',
                'product_specs' => 'Matte Finish • 350gsm',
                'item_count' => 2,
                'product_image' => '/assets/images/order-verification/cards.jpg',
                'required_documents' => [
                    'Payment Card (Front)',
                    'Payment Card (Back)',
                    'Photo ID'
                ],
                'submitted_documents' => [
                    ['id' => 'd3', 'name' => 'Payment Card (Front)', 'type' => 'card', 'status' => 'submitted', 'file_url' => '/assets/images/order-verification/payment-card.jpg', 'submitted_at' => 'May 22, 2024'],
                    ['id' => 'd4', 'name' => 'Payment Card (Back)', 'type' => 'card', 'status' => 'pending'],
                    ['id' => 'd5', 'name' => 'Photo ID', 'type' => 'id', 'status' => 'pending'],
                    ['id' => 'd6', 'name' => 'Proof of Payment', 'type' => 'payment', 'status' => 'not_required'],
                ],
                'timeline' => [
                    ['title' => 'Verification Request Sent', 'date' => 'May 22, 2024 • 10:24 AM', 'completed' => true],
                    ['title' => 'Partial Response Received', 'date' => 'May 22, 2024 • 11:30 AM', 'completed' => true],
                    ['title' => 'Awaiting Remaining Documents', 'date' => 'In Progress', 'completed' => false],
                ],
                'internal_notes' => [
                    'Front of card received. Awaiting back of card and photo ID.'
                ],
            ],
            [
                'order_id' => $defaultOrderId,
                'order_number' => 'OR-2024-1457',
                'user_id' => $defaultUserId,
                'site_slug' => 'embroidery',
                'risk_level' => 'low',
                'flag_reason' => 'PayPal merchant identity confirmation.',
                'reason_title' => 'Verification Completed',
                'reason_text' => 'Identity confirmation completed successfully.',
                'status' => 'verified',
                'verified_at' => Carbon::now()->subDays(1),
                'total_amount' => 85.00,
                'payment_method' => 'PayPal',
                'product_name' => 'Flyer A5',
                'product_specs' => 'Glossy • 300gsm',
                'item_count' => 1,
                'product_image' => '/assets/images/order-verification/flyer.jpg',
                'required_documents' => [
                    'Photo ID',
                    'Payment Confirmation'
                ],
                'submitted_documents' => [
                    ['id' => 'd7', 'name' => 'Photo ID', 'type' => 'id', 'status' => 'submitted', 'file_url' => '/assets/images/order-verification/photo-id.jpg', 'submitted_at' => 'May 22, 2024'],
                    ['id' => 'd8', 'name' => 'Payment Confirmation', 'type' => 'payment', 'status' => 'submitted', 'file_url' => '/assets/images/order-verification/proof-payment.jpg', 'submitted_at' => 'May 22, 2024'],
                ],
                'timeline' => [
                    ['title' => 'Verification Request Sent', 'date' => 'May 22, 2024 • 09:15 AM', 'completed' => true],
                    ['title' => 'Response Received', 'date' => 'May 22, 2024 • 10:00 AM', 'completed' => true],
                    ['title' => 'Verification Approved', 'date' => 'May 23, 2024 • 11:00 AM', 'completed' => true],
                ],
                'internal_notes' => [
                    'PayPal transaction ID verified against gateway logs.'
                ],
            ],
            [
                'order_id' => $defaultOrderId,
                'order_number' => 'OR-2024-1454',
                'user_id' => $defaultUserId,
                'site_slug' => 'embroidery',
                'risk_level' => 'low',
                'flag_reason' => 'Custom apparel bulk production identity verification.',
                'reason_title' => 'Verification Completed',
                'reason_text' => 'This verification has been completed. No further action is required.',
                'status' => 'verified',
                'verified_at' => Carbon::now()->subDays(2),
                'total_amount' => 210.40,
                'payment_method' => 'Visa ending 0123',
                'product_name' => 'T-Shirt Printing',
                'product_specs' => 'DTG • Front Print',
                'item_count' => 4,
                'product_image' => '/assets/images/order-verification/tshirt.jpg',
                'required_documents' => [
                    'Billing Statement',
                    'Photo ID'
                ],
                'submitted_documents' => [
                    ['id' => 'd9', 'name' => 'Billing Statement', 'type' => 'statement', 'status' => 'submitted', 'file_url' => '/assets/images/order-verification/billing-statement.jpg', 'submitted_at' => 'May 21, 2024'],
                    ['id' => 'd10', 'name' => 'Photo ID', 'type' => 'id', 'status' => 'submitted', 'file_url' => '/assets/images/order-verification/photo-id.jpg', 'submitted_at' => 'May 21, 2024'],
                ],
                'timeline' => [
                    ['title' => 'Verification Request Sent', 'date' => 'May 21, 2024 • 04:12 PM', 'completed' => true],
                    ['title' => 'Response Received', 'date' => 'May 21, 2024 • 06:00 PM', 'completed' => true],
                    ['title' => 'Verification Approved', 'date' => 'May 22, 2024 • 09:30 AM', 'completed' => true],
                ],
                'internal_notes' => [
                    'Statement billing address matches shipping address.'
                ],
            ],
            [
                'order_id' => $defaultOrderId,
                'order_number' => 'OR-2024-1450',
                'user_id' => $defaultUserId,
                'site_slug' => 'embroidery',
                'risk_level' => 'high',
                'flag_reason' => 'Payment method declined multiple times before successful authorization.',
                'reason_title' => 'Verification Declined (Final)',
                'reason_text' => 'After careful review, we were unable to verify the information and documents submitted for this order. This verification decision is final and cannot be appealed.',
                'decline_reason' => 'Unable to verify payment method ownership and/or supporting documents.',
                'status' => 'declined',
                'declined_at' => Carbon::now()->subDays(3),
                'total_amount' => 68.75,
                'payment_method' => 'Mastercard ending 7890',
                'product_name' => 'Custom Embroidery Patches',
                'product_specs' => 'Woven • Iron-on',
                'item_count' => 10,
                'product_image' => '/assets/images/order-verification/stickers.jpg',
                'required_documents' => [
                    'Payment Card (Front & Back)',
                    'Photo ID'
                ],
                'submitted_documents' => [
                    ['id' => 'd11', 'name' => 'Payment Card (Front)', 'type' => 'card', 'status' => 'rejected'],
                    ['id' => 'd12', 'name' => 'Photo ID', 'type' => 'id', 'status' => 'rejected'],
                ],
                'timeline' => [
                    ['title' => 'Verification Request Sent', 'date' => 'May 22, 2024 • 02:15 PM', 'completed' => true],
                    ['title' => 'Your Response Received', 'date' => 'May 24, 2024 • 10:32 AM', 'completed' => true],
                    ['title' => 'Decision Made', 'date' => 'May 29, 2024 • 11:59 PM', 'completed' => true],
                ],
                'internal_notes' => [
                    'Name on ID does not match cardholder billing record.'
                ],
            ]
        ];

        foreach ($records as $record) {
            EcommerceOrderVerification::updateOrCreate(
                ['order_number' => $record['order_number']],
                $record
            );
        }
    }
}
