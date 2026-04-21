<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EcommerceOrder;
use App\Models\EcommerceOrderProof;
use App\Models\EcommerceOrderVerification;
use App\Models\EcommerceQuotation;
use App\Models\User;

class OrderSectionMockDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = EcommerceOrder::latest()->take(10)->get();
        if ($orders->isEmpty()) return;

        // Create Order Proofs
        foreach($orders->take(5) as $order) {
            EcommerceOrderProof::firstOrCreate([
                'order_id' => $order->id,
            ], [
                'proof_type' => ['Embroidery Mockup', 'Artwork PDF', 'Color Proof'][array_rand(['Embroidery Mockup', 'Artwork PDF', 'Color Proof'])],
                'file_path' => '/mock-assets/proof-' . $order->id . '.pdf',
                'status' => ['awaiting_approval', 'approved', 'rejected'][array_rand(['awaiting_approval', 'approved', 'rejected'])],
            ]);
        }

        // Create Order Verifications
        foreach($orders->random(3) as $order) {
            EcommerceOrderVerification::firstOrCreate([
                'order_id' => $order->id,
            ], [
                'risk_level' => ['high', 'medium', 'low'][array_rand(['high', 'medium', 'low'])],
                'flag_reason' => 'System flagged due to unusual pattern',
                'status' => ['pending', 'reviewing', 'cleared'][array_rand(['pending', 'reviewing', 'cleared'])],
            ]);
        }

        // Create Quotations
        $user = User::first();
        if ($user) {
            EcommerceQuotation::firstOrCreate([
                'quote_number' => 'QT-' . date('Y') . '-001',
            ], [
                'user_id' => $user->id,
                'company_name' => 'Demo Company',
                'customer_name' => $user->name,
                'contact_email' => $user->email,
                'status' => 'pending',
                'total_estimated' => 450.00,
                'valid_until' => now()->addDays(30),
            ]);
        }
    }
}
