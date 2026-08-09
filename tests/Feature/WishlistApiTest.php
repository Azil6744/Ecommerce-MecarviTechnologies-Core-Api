<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\EcommerceCart;
use App\Models\EcommerceWishlist;
use App\Models\EcommerceWishlistCollection;
use App\Models\EcommerceWishlistItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistApiTest extends TestCase
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

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/ecommerce/wishlist');
        $response->assertStatus(401);
    }

    public function test_user_can_get_wishlist_and_add_items(): void
    {
        $user = User::factory()->create();
        $headers = $this->centralAuthHeadersFor($user);

        $category = Category::create(['name' => 'Custom Apparel', 'slug' => 'custom-apparel']);
        $product = Product::create([
            'name' => 'Custom Embroidered Hoodie',
            'sku' => 'HD-001',
            'price' => 45.00,
            'sale_price' => 39.99,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        // Fetch empty wishlist
        $response = $this->withHeaders($headers)->getJson('/api/ecommerce/wishlist');
        $response->assertOk()
            ->assertJsonPath('name', 'My Wishlist')
            ->assertJsonPath('summary.saved_count', 0)
            ->assertJsonPath('summary.estimated_total', 0);

        // Add item to wishlist
        $addResponse = $this->withHeaders($headers)->postJson('/api/ecommerce/wishlist/items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'options' => ['size' => 'XL', 'color' => 'Black'],
        ]);

        $addResponse->assertCreated()
            ->assertJsonPath('product_id', $product->id)
            ->assertJsonPath('quantity', 2)
            ->assertJsonPath('options.size', 'XL');

        // Verify item is returned in wishlist index
        $indexResponse = $this->withHeaders($headers)->getJson('/api/ecommerce/wishlist');
        $indexResponse->assertOk()
            ->assertJsonPath('summary.saved_count', 1)
            ->assertJsonPath('summary.estimated_total', 79.98); // 39.99 * 2
    }

    public function test_user_can_manage_collections_and_move_items(): void
    {
        $user = User::factory()->create();
        $headers = $this->centralAuthHeadersFor($user);

        $product = Product::create([
            'name' => 'Branded Cap',
            'sku' => 'CAP-001',
            'price' => 20.00,
            'is_active' => true,
        ]);

        // Create collection
        $createColl = $this->withHeaders($headers)->postJson('/api/ecommerce/wishlist/collections', [
            'name' => 'Summer Favorites',
        ]);

        $createColl->assertCreated()
            ->assertJsonPath('name', 'Summer Favorites')
            ->assertJsonPath('slug', 'summer-favorites');

        $collectionId = $createColl->json('id');

        // Add item directly to collection
        $addItem = $this->withHeaders($headers)->postJson('/api/ecommerce/wishlist/items', [
            'product_id' => $product->id,
            'quantity' => 1,
            'collection_id' => $collectionId,
        ]);

        $addItem->assertCreated()
            ->assertJsonPath('collection_id', $collectionId);

        $itemId = $addItem->json('id');

        // Update collection name
        $updateColl = $this->withHeaders($headers)->putJson("/api/ecommerce/wishlist/collections/{$collectionId}", [
            'name' => 'Summer Essentials',
        ]);
        $updateColl->assertOk()->assertJsonPath('name', 'Summer Essentials');

        // Filter wishlist by collection
        $filterResp = $this->withHeaders($headers)->getJson("/api/ecommerce/wishlist?collection_id={$collectionId}");
        $filterResp->assertOk()->assertJsonCount(1, 'items');

        // Move item out of collection
        $moveItem = $this->withHeaders($headers)->putJson("/api/ecommerce/wishlist/items/{$itemId}", [
            'collection_id' => null,
        ]);
        $moveItem->assertOk()->assertJsonPath('collection_id', null);

        // Delete collection
        $delColl = $this->withHeaders($headers)->deleteJson("/api/ecommerce/wishlist/collections/{$collectionId}");
        $delColl->assertOk();
    }

    public function test_user_can_bulk_remove_items(): void
    {
        $user = User::factory()->create();
        $headers = $this->centralAuthHeadersFor($user);

        $p1 = Product::create(['name' => 'Item 1', 'sku' => 'P1', 'price' => 10, 'is_active' => true]);
        $p2 = Product::create(['name' => 'Item 2', 'sku' => 'P2', 'price' => 15, 'is_active' => true]);

        $i1 = $this->withHeaders($headers)->postJson('/api/ecommerce/wishlist/items', ['product_id' => $p1->id])->json('id');
        $i2 = $this->withHeaders($headers)->postJson('/api/ecommerce/wishlist/items', ['product_id' => $p2->id])->json('id');

        $removeResp = $this->withHeaders($headers)->deleteJson('/api/ecommerce/wishlist/items', [
            'item_ids' => [$i1, $i2],
        ]);

        $removeResp->assertOk()->assertJsonPath('count', 2);

        $index = $this->withHeaders($headers)->getJson('/api/ecommerce/wishlist');
        $index->assertOk()->assertJsonPath('summary.saved_count', 0);
    }

    public function test_user_can_transfer_wishlist_items_to_cart(): void
    {
        $user = User::factory()->create();
        $headers = $this->centralAuthHeadersFor($user);

        $product = Product::create([
            'name' => 'Embroidery Patch',
            'sku' => 'PATCH-01',
            'price' => 12.50,
            'is_active' => true,
        ]);

        $item = $this->withHeaders($headers)->postJson('/api/ecommerce/wishlist/items', [
            'product_id' => $product->id,
            'quantity' => 3,
            'options' => ['backing' => 'Iron-on'],
        ])->json();

        $cartResponse = $this->withHeaders($headers)->postJson('/api/ecommerce/wishlist/add-to-cart', [
            'item_ids' => [$item['id']],
        ]);

        $cartResponse->assertOk()
            ->assertJsonPath('message', 'Wishlist items added to cart')
            ->assertJsonPath('cart.items.0.product_id', $product->id)
            ->assertJsonPath('cart.items.0.quantity', 3);

        $this->assertEquals(37.50, (float) $cartResponse->json('cart.total_amount'));
    }

    public function test_wishlist_sharing(): void
    {
        $user = User::factory()->create();
        $headers = $this->centralAuthHeadersFor($user);

        $product = Product::create(['name' => 'Shared Item', 'sku' => 'SHARE-01', 'price' => 50, 'is_active' => true]);
        $this->withHeaders($headers)->postJson('/api/ecommerce/wishlist/items', ['product_id' => $product->id]);

        $shareResp = $this->withHeaders($headers)->postJson('/api/ecommerce/wishlist/share');
        $shareResp->assertOk()->assertJsonStructure(['share_token', 'share_url']);

        $token = $shareResp->json('share_token');

        // Access shared wishlist publicly without authentication
        $publicResp = $this->getJson("/api/ecommerce/wishlist/shared/{$token}");
        $publicResp->assertOk()
            ->assertJsonPath('summary.saved_count', 1)
            ->assertJsonPath('items.0.product.name', 'Shared Item');
    }
}
