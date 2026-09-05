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
                'account_type' => 'personal',
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
                'name' => 'Essentials Plan',
                'account_type' => 'personal',
                'price' => 19.99,
                'billing_cycle' => 'Monthly',
                'coverage_type' => 'universal',
                'applicable_site' => 'all-sites',
                'status' => 'Active',
                'badge' => 'Popular Choice',
                'description' => 'Great for regular embroidery orders and digitizing.',
                'features' => json_encode(['10% Site-wide Discount', 'Free Delivery on all orders', 'Email support', 'Early access to new designs']),
                'benefit_config' => [
                    'percentage_discount' => 10,
                    'fixed_discount' => 0,
                    'free_delivery' => true,
                    'free_delivery_categories' => 'all',
                    'min_order_amount' => 0,
                    'allow_coupon_stacking' => true,
                    'loyalty_multiplier' => 1.5,
                ],
                'sort_order' => 2,
            ]
        );

        EcommerceSubscriptionPlan::updateOrCreate(
            ['internal_code' => 'BUS-PRO'],
            [
                'name' => 'Business Pro Plan',
                'account_type' => 'business',
                'price' => 49.99,
                'billing_cycle' => 'Monthly',
                'coverage_type' => 'universal',
                'applicable_site' => 'all-sites',
                'status' => 'Active',
                'badge' => 'popular choice',
                'description' => 'Maximum discounts, priority production, and dedicated manager.',
                'features' => json_encode(['25% Site-wide Discount', 'Free Express Delivery', '24/7 Priority support', 'Dedicated account manager']),
                'benefit_config' => [
                    'percentage_discount' => 25,
                    'fixed_discount' => 0,
                    'free_delivery' => true,
                    'free_delivery_categories' => 'all',
                    'min_order_amount' => 0,
                    'allow_coupon_stacking' => true,
                    'loyalty_multiplier' => 2.0,
                ],
                'sort_order' => 3,
            ]
        );
    }
}
