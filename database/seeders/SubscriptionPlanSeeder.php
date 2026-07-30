<?php

namespace Database\Seeders;

use App\Models\EcommerceSubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        EcommerceSubscriptionPlan::updateOrCreate(
            ['internal_code' => 'FREE-PLAN'],
            [
                'name' => 'Free Plan',
                'price' => 0.00,
                'billing_cycle' => 'Monthly',
                'coverage_type' => 'universal',
                'applicable_site' => 'all-sites',
                'status' => 'Active',
                'badge' => 'Free Access',
                'description' => 'Basic access to catalog browsing and quotes.',
                'features' => json_encode(['Basic support', 'Catalog access']),
                'sort_order' => 1,
            ]
        );

        EcommerceSubscriptionPlan::updateOrCreate(
            ['internal_code' => 'BASIC-PLAN'],
            [
                'name' => 'basic',
                'price' => 19.98,
                'billing_cycle' => 'Monthly',
                'coverage_type' => 'universal',
                'applicable_site' => 'all-sites',
                'status' => 'Active',
                'badge' => 'perfect for basic plan',
                'description' => 'Great for regular embroidery orders and digitizing.',
                'features' => json_encode(['10% discount on digitizing', 'Standard turnaround', 'Email support']),
                'sort_order' => 2,
            ]
        );

        EcommerceSubscriptionPlan::updateOrCreate(
            ['internal_code' => 'BUS-PRO'],
            [
                'name' => 'Business Pro Plan',
                'price' => 49.99,
                'billing_cycle' => 'Monthly',
                'coverage_type' => 'universal',
                'applicable_site' => 'all-sites',
                'status' => 'Active',
                'badge' => 'popular choice',
                'description' => 'Maximum discounts, priority production, and dedicated manager.',
                'features' => json_encode(['25% discount on all orders', '24/7 Priority support', 'Free revision credits', 'Dedicated account manager']),
                'sort_order' => 3,
            ]
        );
    }
}
