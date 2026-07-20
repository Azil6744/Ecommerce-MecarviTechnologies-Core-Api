<?php

namespace Tests\Unit;

use App\Models\EcommerceSubscriptionPlan;
use App\Services\StripeMembershipPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StripeMembershipPaymentServiceTest extends TestCase
{
    public function test_it_authorizes_local_stripe_test_membership_payment(): void
    {
        Config::set('services.stripe.secret', null);

        $result = app(StripeMembershipPaymentService::class)->authorizePlanPurchase(
            $this->plan(),
            Request::create('/membership', 'POST', [
                'payment_method' => 'stripe',
                'stripe_payment_method' => 'pm_card_visa',
                'payment_summary' => ['amount_due_today' => 24],
            ])
        );

        $this->assertSame('paid', $result['payment_status']);
        $this->assertSame('stripe', $result['payment_method']);
        $this->assertSame('stripe_test', $result['payment_processor']);
        $this->assertStringStartsWith('test_stripe_membership_55_', $result['transaction_reference']);
        $this->assertTrue($result['payment_details']['test_mode']);
    }

    public function test_it_rejects_non_test_card_in_local_stripe_test_mode(): void
    {
        Config::set('services.stripe.secret', null);

        $this->expectException(ValidationException::class);

        app(StripeMembershipPaymentService::class)->authorizePlanPurchase(
            $this->plan(),
            Request::create('/membership', 'POST', [
                'payment_method' => 'stripe',
                'stripe_payment_method' => 'pm_card_chargeDeclined',
            ])
        );
    }

    private function plan(): EcommerceSubscriptionPlan
    {
        $plan = new EcommerceSubscriptionPlan([
            'name' => 'Embroidery Personal Plus',
            'price' => 19,
            'setup_fee' => 5,
            'currency' => 'USD',
            'internal_code' => 'EMB-PERSONAL-PLUS-M',
        ]);
        $plan->id = 55;

        return $plan;
    }
}
