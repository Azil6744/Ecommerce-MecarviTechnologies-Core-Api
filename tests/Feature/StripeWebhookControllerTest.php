<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StripeWebhookControllerTest extends TestCase
{
    public function test_it_handles_invoice_payment_succeeded_webhook(): void
    {
        Http::fake([
            '*/v1/internal/admin/memberships' => Http::response([
                'data' => [
                    [
                        'id' => 999,
                        'user' => ['email' => 'customer@example.com'],
                        'plan_name' => 'Embroidery Personal Plus',
                        'price' => 19.00,
                        'billing_cycle' => 'monthly',
                        'status' => 'active',
                    ]
                ]
            ], 200),
            '*/v1/internal/admin/memberships/update' => Http::response(['success' => true], 200),
        ]);

        $payload = [
            'id' => 'evt_test_123',
            'type' => 'invoice.payment_succeeded',
            'data' => [
                'object' => [
                    'customer_email' => 'customer@example.com',
                    'payment_intent' => 'pi_test_succeeded',
                ]
            ]
        ];

        $response = $this->postJson('/api/ecommerce/webhooks/stripe', $payload);

        $response->assertOk();

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/v1/internal/admin/memberships/update') &&
                $request['membership_id'] === 999 &&
                $request['status'] === 'active' &&
                $request['transaction_reference'] === 'pi_test_succeeded';
        });
    }

    public function test_it_handles_invoice_payment_failed_webhook(): void
    {
        Http::fake([
            '*/v1/internal/admin/memberships' => Http::response([
                'data' => [
                    [
                        'id' => 999,
                        'user' => ['email' => 'customer@example.com'],
                        'plan_name' => 'Embroidery Personal Plus',
                        'price' => 19.00,
                        'billing_cycle' => 'monthly',
                        'status' => 'active',
                    ]
                ]
            ], 200),
            '*/v1/internal/admin/memberships/update' => Http::response(['success' => true], 200),
        ]);

        $payload = [
            'id' => 'evt_test_456',
            'type' => 'invoice.payment_failed',
            'data' => [
                'object' => [
                    'customer_email' => 'customer@example.com',
                ]
            ]
        ];

        $response = $this->postJson('/api/ecommerce/webhooks/stripe', $payload);

        $response->assertOk();

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/v1/internal/admin/memberships/update') &&
                $request['membership_id'] === 999 &&
                $request['status'] === 'past_due' &&
                $request['transaction_type'] === 'payment_failed';
        });
    }

    public function test_it_handles_customer_subscription_deleted_webhook(): void
    {
        Http::fake([
            '*/v1/internal/admin/memberships' => Http::response([
                'data' => [
                    [
                        'id' => 999,
                        'user' => ['email' => 'customer@example.com'],
                        'plan_name' => 'Embroidery Personal Plus',
                        'price' => 19.00,
                        'billing_cycle' => 'monthly',
                        'status' => 'active',
                    ]
                ]
            ], 200),
            '*/v1/internal/admin/memberships/update' => Http::response(['success' => true], 200),
        ]);

        $payload = [
            'id' => 'evt_test_789',
            'type' => 'customer.subscription.deleted',
            'data' => [
                'object' => [
                    'metadata' => [
                        'customer_email' => 'customer@example.com',
                    ]
                ]
            ]
        ];

        $response = $this->postJson('/api/ecommerce/webhooks/stripe', $payload);

        $response->assertOk();

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/v1/internal/admin/memberships/update') &&
                $request['membership_id'] === 999 &&
                $request['status'] === 'canceled' &&
                $request['transaction_type'] === 'cancellation';
        });
    }
}
