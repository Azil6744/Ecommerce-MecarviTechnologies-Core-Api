<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceCart;
use App\Models\EcommerceWishlist;
use App\Models\EcommerceWishlistCollection;
use App\Models\EcommerceWishlistItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WishlistController extends Controller
{
    private function wishlistFor(Request $request): EcommerceWishlist
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        return EcommerceWishlist::firstOrCreate(
            ['user_id' => $user->id],
            ['name' => 'My Wishlist', 'is_default' => true]
        );
    }

    private function productSnapshot(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => $product->price,
            'sale_price' => $product->sale_price,
            'images' => $product->images,
            'short_description' => $product->short_description,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
            ] : null,
            'attributes' => $product->attributes,
        ];
    }

    private function itemPayload(EcommerceWishlistItem $item): array
    {
        $product = $item->product;
        $snapshot = $item->product_snapshot ?? [];

        return [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'collection_id' => $item->ecommerce_wishlist_collection_id,
            'quantity' => $item->quantity,
            'saved_price' => $item->saved_price,
            'options' => $item->options ?? [],
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
            'product' => $product ? [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'images' => $product->images,
                'short_description' => $product->short_description,
                'stock_quantity' => $product->stock_quantity,
                'attributes' => $product->attributes,
                'category' => $product->category,
                'previewAssets' => $product->previewAssets,
            ] : $snapshot,
        ];
    }

    private function wishlistPayload(EcommerceWishlist $wishlist, Request $request): array
    {
        $query = $wishlist->items()
            ->with([
                'collection',
                'product.category',
                'product.previewAssets' => fn ($assetQuery) => $assetQuery->where('is_active', true),
            ]);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($productQuery) use ($search) {
                    $productQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%");
                })->orWhere('product_snapshot->name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('collection_id')) {
            $collectionId = $request->input('collection_id');
            if ($collectionId === 'none' || $collectionId === '0') {
                $query->whereNull('ecommerce_wishlist_collection_id');
            } else {
                $query->where('ecommerce_wishlist_collection_id', $collectionId);
            }
        }

        if ($request->filled('category')) {
            $category = $request->string('category')->toString();
            if ($category !== 'All Categories') {
                $query->whereHas('product.category', function ($catQuery) use ($category) {
                    $catQuery->where('name', $category);
                });
            }
        }

        match ($request->input('sort', 'recent')) {
            'oldest' => $query->oldest(),
            'price_asc' => $query->orderBy('saved_price'),
            'price_desc' => $query->orderByDesc('saved_price'),
            'name_asc' => $query->join('products', 'products.id', '=', 'ecommerce_wishlist_items.product_id')
                ->orderBy('products.name')
                ->select('ecommerce_wishlist_items.*'),
            default => $query->latest(),
        };

        $items = $query->get();
        $allItems = $wishlist->items()->with('product')->get();

        $estimatedTotal = round($allItems->sum(function (EcommerceWishlistItem $item) {
            $price = (float) ($item->product->sale_price ?? $item->product->price ?? $item->saved_price ?? 0);
            return $price * (int) ($item->quantity ?? 1);
        }), 2);

        $collections = $wishlist->collections()
            ->withCount('items')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (EcommerceWishlistCollection $collection) => [
                'id' => $collection->id,
                'name' => $collection->name,
                'slug' => $collection->slug,
                'count' => $collection->items_count,
            ])
            ->values();

        return [
            'id' => $wishlist->id,
            'name' => $wishlist->name,
            'share_token' => $wishlist->share_token,
            'items' => $items->map(fn (EcommerceWishlistItem $item) => $this->itemPayload($item))->values(),
            'collections' => $collections,
            'summary' => [
                'saved_count' => $allItems->count(),
                'estimated_total' => $estimatedTotal,
            ],
        ];
    }

    public function index(Request $request)
    {
        return response()->json($this->wishlistPayload($this->wishlistFor($request), $request));
    }

    public function addItem(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
            'options' => 'nullable|array',
            'collection_id' => 'nullable|integer',
        ]);

        $wishlist = $this->wishlistFor($request);
        $product = Product::with('category')->findOrFail($data['product_id']);
        $collectionId = $data['collection_id'] ?? null;

        if ($collectionId) {
            $collectionId = $wishlist->collections()->whereKey($collectionId)->value('id');
        }

        $item = $wishlist->items()->updateOrCreate(
            ['product_id' => $product->id],
            [
                'ecommerce_wishlist_collection_id' => $collectionId,
                'quantity' => $data['quantity'] ?? 1,
                'saved_price' => (float) ($product->sale_price ?? $product->price ?? 0),
                'options' => $data['options'] ?? [],
                'product_snapshot' => $this->productSnapshot($product),
            ]
        );

        return response()->json($this->itemPayload($item->load(['product.category', 'product.previewAssets'])), 201);
    }

    public function updateItem(Request $request, int $itemId)
    {
        $wishlist = $this->wishlistFor($request);
        $item = $wishlist->items()->findOrFail($itemId);
        $data = $request->validate([
            'quantity' => 'nullable|integer|min:1',
            'collection_id' => 'nullable',
        ]);

        if (array_key_exists('quantity', $data)) {
            $item->quantity = $data['quantity'];
        }

        if (array_key_exists('collection_id', $data)) {
            $collectionId = $data['collection_id'];
            $item->ecommerce_wishlist_collection_id = $collectionId
                ? $wishlist->collections()->whereKey($collectionId)->value('id')
                : null;
        }

        $item->save();

        return response()->json($this->itemPayload($item->load(['product.category', 'product.previewAssets'])));
    }

    public function removeItem(Request $request, int $itemId)
    {
        $wishlist = $this->wishlistFor($request);
        $wishlist->items()->findOrFail($itemId)->delete();

        return response()->json(['message' => 'Wishlist item removed']);
    }

    public function bulkRemoveItems(Request $request)
    {
        $wishlist = $this->wishlistFor($request);
        $data = $request->validate([
            'item_ids' => 'required|array',
            'item_ids.*' => 'integer',
        ]);

        $deletedCount = $wishlist->items()->whereIn('id', $data['item_ids'])->delete();

        return response()->json([
            'message' => "Removed {$deletedCount} items from wishlist",
            'count' => $deletedCount,
        ]);
    }

    public function createCollection(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $wishlist = $this->wishlistFor($request);
        $baseSlug = Str::slug($data['name']) ?: 'collection';
        $slug = $baseSlug;
        $suffix = 2;

        while ($wishlist->collections()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        $collection = $wishlist->collections()->create([
            'name' => $data['name'],
            'slug' => $slug,
            'sort_order' => (int) $wishlist->collections()->max('sort_order') + 1,
        ]);

        return response()->json([
            'id' => $collection->id,
            'name' => $collection->name,
            'slug' => $collection->slug,
            'count' => 0,
        ], 201);
    }

    public function updateCollection(Request $request, int $collectionId)
    {
        $wishlist = $this->wishlistFor($request);
        $collection = $wishlist->collections()->findOrFail($collectionId);
        $data = $request->validate([
            'name' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        if (!empty($data['name']) && $data['name'] !== $collection->name) {
            $baseSlug = Str::slug($data['name']) ?: 'collection';
            $slug = $baseSlug;
            $suffix = 2;
            while ($wishlist->collections()->where('slug', $slug)->where('id', '!=', $collection->id)->exists()) {
                $slug = "{$baseSlug}-{$suffix}";
                $suffix++;
            }
            $collection->name = $data['name'];
            $collection->slug = $slug;
        }

        if (array_key_exists('sort_order', $data)) {
            $collection->sort_order = (int) $data['sort_order'];
        }

        $collection->save();

        return response()->json([
            'id' => $collection->id,
            'name' => $collection->name,
            'slug' => $collection->slug,
            'count' => $collection->items()->count(),
        ]);
    }

    public function deleteCollection(Request $request, int $collectionId)
    {
        $wishlist = $this->wishlistFor($request);
        $collection = $wishlist->collections()->findOrFail($collectionId);

        $collection->items()->update(['ecommerce_wishlist_collection_id' => null]);
        $collection->delete();

        return response()->json(['message' => 'Collection deleted successfully']);
    }

    public function addSelectedToCart(Request $request)
    {
        $data = $request->validate([
            'item_ids' => 'nullable|array',
            'item_ids.*' => 'integer',
        ]);

        $wishlist = $this->wishlistFor($request);
        $itemsQuery = $wishlist->items()->with('product')->whereHas('product', fn ($query) => $query->where('is_active', true));

        if (!empty($data['item_ids'])) {
            $itemsQuery->whereIn('id', $data['item_ids']);
        }

        $items = $itemsQuery->get();
        $cart = EcommerceCart::firstOrCreate(
            ['user_id' => $request->user()->id, 'status' => 'active'],
            ['total_amount' => 0]
        );

        foreach ($items as $item) {
            $product = $item->product;
            $quantity = max(1, (int) $item->quantity);
            $unitPrice = (float) ($product->sale_price ?? $product->price ?? $item->saved_price ?? 0);
            $options = $item->options ?? [];
            $normalizedOptions = is_array($options) ? $options : [];
            ksort($normalizedOptions);

            $cartItem = $cart->items()
                ->where('product_id', $product->id)
                ->get()
                ->first(function ($item) use ($normalizedOptions) {
                    $itemOptions = is_array($item->options) ? $item->options : [];
                    ksort($itemOptions);
                    return $itemOptions === $normalizedOptions;
                });

            if ($cartItem) {
                $nextQuantity = $cartItem->quantity + $quantity;
                $cartItem->update([
                    'quantity' => $nextQuantity,
                    'unit_price' => $unitPrice,
                    'total_price' => round($unitPrice * $nextQuantity, 2),
                ]);
            } else {
                $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => round($unitPrice * $quantity, 2),
                    'options' => $options,
                ]);
            }
        }

        $cart->forceFill(['total_amount' => $cart->items()->sum('total_price')])->save();

        return response()->json([
            'message' => 'Wishlist items added to cart',
            'cart' => $cart->load('items.product'),
        ]);
    }

    public function share(Request $request)
    {
        $wishlist = $this->wishlistFor($request);

        if (!$wishlist->share_token) {
            $wishlist->forceFill(['share_token' => Str::random(32)])->save();
        }

        return response()->json([
            'share_token' => $wishlist->share_token,
            'share_url' => url("/api/ecommerce/wishlist/shared/{$wishlist->share_token}"),
        ]);
    }

    public function shared(string $token)
    {
        $wishlist = EcommerceWishlist::query()
            ->where('share_token', $token)
            ->firstOrFail();

        return response()->json($this->wishlistPayload($wishlist, new Request()));
    }
}
