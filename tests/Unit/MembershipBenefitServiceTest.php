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
}
