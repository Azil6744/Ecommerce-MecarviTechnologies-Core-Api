<?php

namespace Tests\Feature;

use App\Models\EcommerceCoupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EcommerceCouponControllerTest extends TestCase
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

    public function test_admin_can_create_coupon_and_validate_product_scoped_capped_discount(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        $eligibleProduct = Product::create([
            'name' => 'Eligible Polo',
            'sku' => 'ELIGIBLE-POLO',
            'price' => 100,
            'is_active' => true,
        ]);
        $otherProduct = Product::create([
            'name' => 'Other Hoodie',
            'sku' => 'OTHER-HOODIE',
            'price' => 100,
            'is_active' => true,
        ]);

        $response = $this
            ->withHeaders($this->centralAuthHeadersFor($admin))
            ->postJson('/api/ecommerce/admin/coupons', [
                'code' => 'SAVE50',
                'title' => 'Half off selected product',
                'discount_type' => 'percentage',
                'discount_value' => 50,
                'min_order_amount' => 25,
                'is_active' => true,
                'metadata' => [
                    'apply_scope' => 'specific_products',
                    'max_discount_amount' => 30,
                    'per_customer_limit' => 2,
                ],
                'product_ids' => [$eligibleProduct->id],
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.code', 'SAVE50')
            ->assertJsonPath('data.products.0.id', $eligibleProduct->id);

        $this
            ->getJson("/api/ecommerce/coupons/validate?code=SAVE50&subtotal=200&product_id={$eligibleProduct->id}")
            ->assertOk()
            ->assertJsonPath('data.discount_amount', 30);

        $this
            ->getJson("/api/ecommerce/coupons/validate?code=SAVE50&subtotal=200&product_id={$otherProduct->id}")
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_free_shipping_coupon_returns_capped_shipping_discount(): void
    {
        EcommerceCoupon::create([
            'code' => 'SHIP15',
            'title' => 'Shipping help',
            'discount_type' => 'free_shipping',
            'discount_value' => 0,
            'min_order_amount' => 0,
            'is_active' => true,
            'metadata' => [
                'max_shipping_cost' => 15,
            ],
        ]);

        $this
            ->getJson('/api/ecommerce/coupons/validate?code=SHIP15&subtotal=50&shipping_amount=22')
            ->assertOk()
            ->assertJsonPath('data.discount_amount', 0)
            ->assertJsonPath('data.shipping_discount_amount', 15);
    }
}
