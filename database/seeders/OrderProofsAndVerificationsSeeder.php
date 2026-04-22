<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EcommerceOrderProof;
use App\Models\EcommerceOrderVerification;

class OrderProofsAndVerificationsSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing order IDs
        $orderIds = \App\Models\EcommerceOrder::pluck('id')->toArray();

        if (empty($orderIds)) {
            $this->command->warn('No orders found. Skipping seeder.');
            return;
        }

        // --- Order Proofs ---
        $proofTypes = ['Embroidery Mockup', 'Artwork PDF', 'Color Proof', 'Digitized Design'];
        $proofStatuses = ['awaiting_approval', 'approved', 'rejected'];

        foreach ($orderIds as $i => $orderId) {
            // 2 proofs per order
            EcommerceOrderProof::create([
                'order_id'   => $orderId,
                'proof_type' => $proofTypes[$i % count($proofTypes)],
                'file_path'  => 'proofs/sample-proof-' . $orderId . '-a.pdf',
                'status'     => $proofStatuses[0], // awaiting_approval
            ]);

            EcommerceOrderProof::create([
                'order_id'   => $orderId,
                'proof_type' => $proofTypes[($i + 1) % count($proofTypes)],
                'file_path'  => 'proofs/sample-proof-' . $orderId . '-b.png',
                'status'     => $proofStatuses[min($i, 2)],
            ]);
        }

        // --- Order Verifications ---
        $riskLevels  = ['high', 'medium', 'low'];
        $flagReasons = [
            'New customer with large order value ($500+)',
            'Shipping address differs from billing address',
            'Multiple failed payment attempts before success',
            'First-time order with rush delivery requested',
        ];
        $verifStatuses = ['pending', 'reviewing', 'cleared'];

        foreach ($orderIds as $i => $orderId) {
            EcommerceOrderVerification::create([
                'order_id'    => $orderId,
                'risk_level'  => $riskLevels[$i % count($riskLevels)],
                'flag_reason' => $flagReasons[$i % count($flagReasons)],
                'status'      => $verifStatuses[$i % count($verifStatuses)],
            ]);
        }

        $this->command->info('Seeded ' . count($orderIds) * 2 . ' order proofs and ' . count($orderIds) . ' order verifications.');
    }
}
