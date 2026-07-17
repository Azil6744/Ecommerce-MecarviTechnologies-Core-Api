<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Charity;
use App\Models\Category;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\EcommerceOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharitySystemIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function createTestProduct()
    {
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
        ]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Premium Shirt',
            'sku' => 'PREM-SHIRT',
            'price' => 35.00,
            'is_active' => true,
        ]);
    }

    public function test_checkout_validation_for_charity_donation(): void
    {
        // 1. Seed site settings with charity donation disabled
        SiteSetting::create([
            'site_name' => 'Mecarvi',
            'charity_donation_enabled' => false,
            'charity_name' => 'Mock Charity',
            'charity_default_amount' => 5.00,
        ]);

        // 2. Seed active charity
        Charity::create([
            'name' => 'Mock Charity',
            'tagline' => 'Test tagline',
            'description' => 'Test description',
            'contact_person' => 'Jane Doe',
            'address' => '123 Charity Lane',
            'phone' => '1234567890',
            'email' => 'contact@charity.org',
            'web' => 'charity.org',
            'status' => 'Active',
            'category' => 'Community',
            'logo_svg_type' => 'feeding_america',
        ]);

        $product = $this->createTestProduct();

        // 3. Post checkout with a donation when disabled -> should return 400
        $response = $this->postJson('/api/ecommerce/checkout', [
            'customer_name' => 'Guest User',
            'customer_email' => 'guest@example.com',
            'guest_shipping_address' => [
                'first_name' => 'Guest',
                'last_name' => 'User',
                'address' => '123 Guest St',
                'city' => 'Atlanta',
                'state' => 'GA',
                'zip_code' => '30303',
                'country' => 'United States',
                'phone' => '1234567890',
            ],
            'guest_billing_address' => [
                'first_name' => 'Guest',
                'last_name' => 'User',
                'address' => '123 Guest St',
                'city' => 'Atlanta',
                'state' => 'GA',
                'zip_code' => '30303',
                'country' => 'United States',
                'phone' => '1234567890',
            ],
            'payment_method' => 'stripe',
            'shipping_method' => 'express',
            'shipping_amount' => 10.00,
            'packaging_amount' => 5.00,
            'donation_amount' => 5.00,
            'selected_charity' => 'Mock Charity',
            'total_amount' => 100.00,
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_name' => 'Premium Shirt',
                    'quantity' => 2,
                    'unit_price' => 35.00,
                    'total_price' => 70.00,
                ]
            ],
        ]);

        $response->assertStatus(400);
        $this->assertStringContainsString('Charity donations are currently disabled', $response->json('message'));
    }

    public function test_guest_checkout_and_donation_ledger_creation(): void
    {
        // 1. Seed site settings with charity donation enabled
        SiteSetting::create([
            'site_name' => 'Mecarvi',
            'charity_donation_enabled' => true,
            'charity_name' => 'Mock Charity',
            'charity_default_amount' => 5.00,
        ]);

        // 2. Seed active charity
        Charity::create([
            'name' => 'Mock Charity',
            'tagline' => 'Test tagline',
            'description' => 'Test description',
            'contact_person' => 'Jane Doe',
            'address' => '123 Charity Lane',
            'phone' => '1234567890',
            'email' => 'contact@charity.org',
            'web' => 'charity.org',
            'status' => 'Active',
            'category' => 'Community',
            'logo_svg_type' => 'feeding_america',
        ]);

        $product = $this->createTestProduct();

        // 3. Post checkout successfully as a guest
        $response = $this->postJson('/api/ecommerce/checkout', [
            'customer_name' => 'Guest User',
            'customer_email' => 'guest@example.com',
            'guest_shipping_address' => [
                'first_name' => 'Guest',
                'last_name' => 'User',
                'address' => '123 Guest St',
                'city' => 'Atlanta',
                'state' => 'GA',
                'zip_code' => '30303',
                'country' => 'United States',
                'phone' => '1234567890',
            ],
            'guest_billing_address' => [
                'first_name' => 'Guest',
                'last_name' => 'User',
                'address' => '123 Guest St',
                'city' => 'Atlanta',
                'state' => 'GA',
                'zip_code' => '30303',
                'country' => 'United States',
                'phone' => '1234567890',
            ],
            'payment_method' => 'stripe',
            'shipping_method' => 'express',
            'shipping_amount' => 10.00,
            'packaging_amount' => 5.00,
            'donation_amount' => 5.00,
            'selected_charity' => 'Mock Charity',
            'total_amount' => 100.00,
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_name' => 'Premium Shirt',
                    'quantity' => 2,
                    'unit_price' => 35.00,
                    'total_price' => 70.00,
                ]
            ],
        ]);

        $response->assertCreated();
        
        $order = EcommerceOrder::orderBy('id', 'desc')->first();
        $this->assertNotNull($order);
        $this->assertNull($order->user_id);
        $this->assertSame('Guest User', $order->customer_name);
        $this->assertSame('guest@example.com', $order->customer_email);

        // Verify donation record is in the database ledger log
        $this->assertDatabaseHas('donations', [
            'order_id' => $order->order_number,
            'amount' => '5.00',
            'payment_method_brand' => 'Stripe',
            'status' => 'Completed',
        ]);
    }
}
