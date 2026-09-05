<?php

namespace Tests\Unit;

use App\Services\MembershipBenefitService;
use PHPUnit\Framework\TestCase;

class MembershipBenefitServiceTest extends TestCase
{
    public function test_it_applies_best_active_membership_checkout_benefits(): void
    {
        $result = (new MembershipBenefitService())->evaluate([
            [
                'id' => 10,
                'status' => 'active',
                'plan_name' => 'Starter',
                'coverage_type' => 'individual_site',
                'covered_sites' => ['embroidery'],
                'benefits_snapshot' => [
                    'benefits' => [
                        ['type' => 'percentage_discount', 'value' => 10],
                        ['type' => 'free_delivery', 'value' => 1],
                    ],
                ],
            ],
            [
                'id' => 11,
                'status' => 'active',
                'plan_name' => 'Elite',
                'coverage_type' => 'universal',
                'benefits_snapshot' => [
                    'benefits' => [
                        ['type' => 'fixed_discount', 'value' => 45],
                    ],
                ],
            ],
        ], 200, 20, 'embroidery');

        $this->assertSame(11, $result['membership_id']);
        $this->assertSame(45.0, $result['membership_discount_amount']);
        $this->assertSame('fixed_discount', $result['membership_benefit_usage'][0]['type']);
    }

    public function test_it_honors_coupon_combination_and_minimum_order_restrictions(): void
    {
        $result = (new MembershipBenefitService())->evaluate([
            [
                'id' => 20,
                'status' => 'active',
                'covered_sites' => ['embroidery'],
                'benefits_snapshot' => [
                    ['type' => 'percentage_discount', 'value' => 25, 'can_combine_with_coupons' => false],
                    ['type' => 'fixed_discount', 'value' => 15, 'minimum_order_amount' => 100],
                ],
            ],
        ], 80, 10, 'embroidery', true);

        $this->assertSame(0.0, $result['membership_discount_amount']);
        $this->assertSame([], $result['membership_benefit_usage']);
    }

    public function test_it_skips_memberships_that_do_not_cover_the_current_site(): void
    {
        $result = (new MembershipBenefitService())->evaluate([
            [
                'id' => 30,
                'status' => 'active',
                'coverage_type' => 'individual_site',
                'covered_sites' => ['other-site'],
                'benefits_snapshot' => [
                    ['type' => 'fixed_discount', 'value' => 100],
                ],
            ],
        ], 120, 15, 'embroidery');

        $this->assertNull($result['membership_id']);
        $this->assertSame(0.0, $result['membership_discount_amount']);
    }

    public function test_it_extracts_benefits_from_admin_wizard_benefits_table_structure(): void
    {
        $result = (new MembershipBenefitService())->evaluate([
            [
                'id' => 40,
                'status' => 'active',
                'plan_name' => 'Mecarvi Gold VIP',
                'coverage_type' => 'individual_site',
                'covered_sites' => ['mecarvi-embroidery'],
                'benefits_snapshot' => [
                    'benefits_table' => [
                        [
                            'id' => 'benefit-1',
                            'title' => 'Member Discount',
                            'type' => 'Discount',
                            'details' => '15% off',
                            'min_order' => '$50.00',
                            'status' => true,
                        ],
                        [
                            'id' => 'benefit-2',
                            'title' => 'Free Standard Shipping',
                            'type' => 'Shipping',
                            'details' => 'Free shipping',
                            'min_order' => '$0.00',
                            'status' => true,
                        ],
                        [
                            'id' => 'benefit-3',
                            'title' => 'Disabled Special Perk',
                            'type' => 'Discount',
                            'details' => '50% off',
                            'min_order' => '$0.00',
                            'status' => false, // Disabled
                        ],
                        [
                            'id' => 'benefit-4',
                            'title' => 'Monthly Store Credit',
                            'type' => 'Store Credit',
                            'details' => '$10 store credit',
                            'min_order' => '$0.00',
                            'status' => true,
                        ],
                    ],
                ],
            ],
        ], 100.0, 15.0, 'embroidery');

        // Total should be: 15% of $100 ($15) + $10 store credit ($10) + Free shipping ($15) = $40
        $this->assertSame(40.0, $result['membership_discount_amount']);
        $this->assertCount(3, $result['membership_benefit_usage']);
    }

    public function test_it_handles_site_slug_prefix_matching(): void
    {
        // Membership configured with 'mecarvi-embroidery' matches checkout request with 'embroidery'
        $result = (new MembershipBenefitService())->evaluate([
            [
                'id' => 50,
                'status' => 'active',
                'applicable_site' => 'mecarvi-embroidery',
                'covered_sites' => ['mecarvi-embroidery'],
                'benefits_snapshot' => [
                    'percentage_discount' => 10,
                ],
            ],
        ], 100, 10, 'embroidery');

        $this->assertSame(50, $result['membership_id']);
        $this->assertSame(10.0, $result['membership_discount_amount']);
    }
}
