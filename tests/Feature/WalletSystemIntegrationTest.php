<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\EcommerceOrder;
use App\Models\EcommerceWalletTransaction;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WalletSystemIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function centralAuthHeadersFor(User $user): array
    {
        $token = $user->createToken('feature-test')->plainTextToken;
        return [
            'Authorization' => 'Bearer ' . $token,
            'X-Central-Auth-Token' => $token,
        ];
    }

    public function test_wallet_service_adjust_wallet_success(): void
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'wallet_balance' => 10.00,
        ]);

        Http::fake([
            '*/v1/internal/admin/wallet/adjust' => Http::response([
                'success' => true,
                'data' => [
                    'balance_after' => 25.00,
                ]
            ], 200),
        ]);

        $result = WalletService::adjustWallet(
            $user->id,
            15.00,
            'affiliate_earned',
            'Referral bonus'
        );

        $this->assertTrue($result);

        $user->refresh();
        $this->assertEquals(25.00, (float) $user->wallet_balance);

        $this->assertDatabaseHas('ecommerce_wallet_transactions', [
            'user_id' => $user->id,
            'type' => 'affiliate_earned',
            'amount' => 15.00,
            'balance_after' => 25.00,
            'description' => 'Referral bonus',
            'status' => 'Completed',
        ]);
    }

    public function test_checkout_with_wallet_payment_method_success(): void
    {
        $category = \App\Models\Category::create([
            'name' => 'Shirts',
            'slug' => 'shirts',
            'is_active' => true,
        ]);

        $product = \App\Models\Product::create([
            'category_id' => $category->id,
            'name' => 'Premium Shirt',
            'sku' => 'PREM-SHIRT',
            'price' => 50.00,
            'is_active' => true,
        ]);

        $customer = User::factory()->create([
            'email' => 'buyer@example.com',
            'wallet_balance' => 100.00,
        ]);

        Http::fake([
            '*/user/wallet' => Http::response([
                'success' => true,
                'data' => [
                    'balance' => 100.00,
                ]
            ], 200),
            '*/v1/internal/admin/wallet/adjust' => Http::response([
                'success' => true,
                'data' => [
                    'balance_after' => 35.00,
                ]
            ], 200),
        ]);

        $checkoutResponse = $this
            ->withHeaders($this->centralAuthHeadersFor($customer))
            ->postJson('/api/ecommerce/checkout', [
                'payment_method' => 'wallet',
                'shipping_amount' => 10.00,
                'tax_amount' => 5.00,
                'total_amount' => 65.00,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'product_name' => 'Test Item',
                        'quantity' => 1,
                        'unit_price' => 50.00,
                        'total_price' => 50.00,
                    ]
                ],
            ]);

        $checkoutResponse->assertCreated();
        $this->assertEquals(65.00, (float) $checkoutResponse->json('order.total_amount'));
        $this->assertEquals('paid', $checkoutResponse->json('order.payment_status'));

        $customer->refresh();
        $this->assertEquals(35.00, (float) $customer->wallet_balance);
    }

    public function test_checkout_with_wallet_payment_method_insufficient_balance(): void
    {
        $category = \App\Models\Category::create([
            'name' => 'Shirts',
            'slug' => 'shirts',
            'is_active' => true,
        ]);

        $product = \App\Models\Product::create([
            'category_id' => $category->id,
            'name' => 'Premium Shirt',
            'sku' => 'PREM-SHIRT',
            'price' => 50.00,
            'is_active' => true,
        ]);

        $customer = User::factory()->create([
            'email' => 'buyer@example.com',
            'wallet_balance' => 20.00,
        ]);

        Http::fake([
            '*/user/wallet' => Http::response([
                'success' => true,
                'data' => [
                    'balance' => 20.00,
                ]
            ], 200),
        ]);

        $checkoutResponse = $this
            ->withHeaders($this->centralAuthHeadersFor($customer))
            ->postJson('/api/ecommerce/checkout', [
                'payment_method' => 'wallet',
                'shipping_amount' => 10.00,
                'tax_amount' => 5.00,
                'total_amount' => 60.00,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'product_name' => 'Test Item',
                        'quantity' => 1,
                        'unit_price' => 50.00,
                        'total_price' => 50.00,
                    ]
                ],
            ]);

        $checkoutResponse->assertStatus(500);
        $this->assertStringContainsString('Insufficient wallet balance', $checkoutResponse->json('message'));
    }

    public function test_register_with_referral_rewards(): void
    {
        // 1. Create a referrer user and an affiliate account for them
        $referrer = User::factory()->create([
            'name' => 'Referrer User',
            'email' => 'referrer@example.com',
        ]);

        $affiliate = \App\Models\EcommerceAffiliate::create([
            'user_id' => $referrer->id,
            'affiliate_code' => 'TESTCODE123',
            'total_earnings' => 0.00,
            'total_referrals' => 0,
            'status' => 'Active',
        ]);

        // 2. Set up referral rewards settings
        $settings = \App\Models\SiteSetting::first();
        if (!$settings) {
            $settings = new \App\Models\SiteSetting();
        }
        $settings->referral_reward_referrer = 15.00;
        $settings->referral_reward_referee = 10.00;
        $settings->save();

        // 3. Fake wallet adjustment responses
        Http::fake([
            '*/v1/internal/admin/wallet/adjust' => Http::response([
                'success' => true,
                'data' => [
                    'balance_after' => 15.00,
                ]
            ], 200),
        ]);

        // 4. Register a new referred user
        $registerResponse = $this->postJson('/api/v1/register', [
            'name' => 'Referred Customer',
            'email' => 'referred@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'referral_code' => 'TESTCODE123',
        ]);

        $registerResponse->assertStatus(201);
        $this->assertTrue($registerResponse->json('success'));

        // 5. Verify database records
        $this->assertDatabaseHas('ecommerce_referrals', [
            'referrer_id' => $referrer->id,
            'reward_amount_referrer' => 15.00,
            'reward_amount_referee' => 10.00,
        ]);

        $affiliate->refresh();
        $this->assertEquals(1, $affiliate->total_referrals);
        $this->assertEquals(15.00, (float)$affiliate->total_earnings);

        // 6. Verify that adjustWallet was called for both users
        Http::assertSent(function (\Illuminate\Http\Client\Request $request) use ($referrer) {
            return str_contains($request->url(), '/v1/internal/admin/wallet/adjust') &&
                str_contains($request->body(), 'Referral reward for inviting');
        });

        Http::assertSent(function (\Illuminate\Http\Client\Request $request) {
            return str_contains($request->url(), '/v1/internal/admin/wallet/adjust') &&
                str_contains($request->body(), 'Welcome reward for joining via referral code TESTCODE123');
        });
    }
}

