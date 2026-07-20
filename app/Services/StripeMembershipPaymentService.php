<?php

namespace App\Services;

use App\Models\EcommerceSubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class StripeMembershipPaymentService
{
    public function authorizePlanPurchase(EcommerceSubscriptionPlan $plan, Request $request): array
    {
        $paymentMethod = strtolower((string) $request->input('payment_method', 'stripe'));
        if (! in_array($paymentMethod, ['stripe', 'card', 'credit_card'], true)) {
            return [
                'payment_status' => $request->input('payment_status', 'paid'),
                'payment_method' => $paymentMethod,
                'payment_processor' => $request->input('payment_processor'),
                'transaction_reference' => $request->input('transaction_reference', 'membership_' . $plan->id . '_' . now()->timestamp),
                'payment_details' => [],
            ];
        }

        $amount = $this->amountDueToday($plan, $request);
        if ($amount <= 0) {
            return [
                'payment_status' => 'paid',
                'payment_method' => 'stripe',
                'payment_processor' => 'stripe',
                'transaction_reference' => 'stripe_zero_due_' . $plan->id . '_' . now()->timestamp,
                'payment_details' => ['amount_due_today' => 0, 'test_mode' => $this->shouldUseLocalStripeTestMode()],
            ];
        }

        $stripePaymentMethod = (string) (
            $request->input('stripe_payment_method')
            ?? $request->input('payment_method_id')
            ?? $request->input('stripe_test_token')
            ?? $request->input('payment_token')
            ?? ''
        );

        if ($stripePaymentMethod === '') {
            throw ValidationException::withMessages([
                'stripe_payment_method' => ['Stripe test payment method is required. Use pm_card_visa or tok_visa for test mode.'],
            ]);
        }

        if ($this->shouldUseLocalStripeTestMode()) {
            $this->assertLocalTestPaymentMethod($stripePaymentMethod);

            return [
                'payment_status' => 'paid',
                'payment_method' => 'stripe',
                'payment_processor' => 'stripe_test',
                'transaction_reference' => 'test_stripe_membership_' . $plan->id . '_' . now()->timestamp,
                'payment_details' => [
                    'stripe_payment_method' => $stripePaymentMethod,
                    'amount_due_today' => $amount,
                    'test_mode' => true,
                ],
            ];
        }

        $secret = (string) config('services.stripe.secret');
        if ($secret === '') {
            throw ValidationException::withMessages([
                'stripe' => ['Stripe is not configured. Add STRIPE_SECRET or use local test mode.'],
            ]);
        }

        $response = Http::asForm()
            ->withBasicAuth($secret, '')
            ->timeout(15)
            ->post('https://api.stripe.com/v1/payment_intents', [
                'amount' => (int) round($amount * 100),
                'currency' => strtolower((string) ($plan->currency ?: 'USD')),
                'payment_method' => $stripePaymentMethod,
                'payment_method_types[0]' => 'card',
                'confirm' => 'true',
                'description' => 'Membership purchase: ' . $plan->name,
                'metadata[plan_id]' => (string) $plan->id,
                'metadata[plan_code]' => (string) $plan->internal_code,
                'metadata[purpose]' => 'membership_purchase',
            ]);

        if (! $response->successful() || $response->json('status') !== 'succeeded') {
            throw ValidationException::withMessages([
                'stripe' => [$response->json('error.message') ?: 'Stripe payment did not succeed.'],
            ]);
        }

        return [
            'payment_status' => 'paid',
            'payment_method' => 'stripe',
            'payment_processor' => 'stripe',
            'transaction_reference' => (string) $response->json('id'),
            'payment_details' => [
                'stripe_payment_intent' => $response->json('id'),
                'stripe_status' => $response->json('status'),
                'amount_due_today' => $amount,
                'test_mode' => str_starts_with($secret, 'sk_test_'),
            ],
        ];
    }

    private function amountDueToday(EcommerceSubscriptionPlan $plan, Request $request): float
    {
        $summaryAmount = data_get($request->input('payment_summary', []), 'amount_due_today');
        if (is_numeric($summaryAmount)) {
            return round(max(0, (float) $summaryAmount), 2);
        }

        return round(max(0, (float) $plan->price + (float) ($plan->setup_fee ?? 0)), 2);
    }

    private function shouldUseLocalStripeTestMode(): bool
    {
        return app()->environment(['local', 'testing']) && blank(config('services.stripe.secret'));
    }

    private function assertLocalTestPaymentMethod(string $paymentMethod): void
    {
        $approved = [
            'pm_card_visa',
            'tok_visa',
            'test_card_4242424242424242',
            '4242424242424242',
        ];

        if (! in_array($paymentMethod, $approved, true)) {
            throw ValidationException::withMessages([
                'stripe_payment_method' => ['Local Stripe test mode only accepts the 4242 4242 4242 4242 test card, tok_visa, or pm_card_visa.'],
            ]);
        }
    }
}
