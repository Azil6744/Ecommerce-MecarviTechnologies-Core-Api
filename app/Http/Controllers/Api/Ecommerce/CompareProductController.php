<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceCompareItem;
use App\Models\EcommerceReview;
use App\Models\Product;
use Illuminate\Http\Request;

class CompareProductController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $items = EcommerceCompareItem::query()
            ->where('user_id', $user->id)
            ->with([
                'product.category.parent',
                'product.previewAssets' => fn ($query) => $query->where('is_active', true),
                'product.reviews' => fn ($query) => $query->whereRaw('LOWER(status) = ?', ['approved']),
            ])
            ->latest()
            ->get();

        $products = $items->pluck('product')->filter()->values();
        $this->attachReviewStats($products);

        return response()->json([
            'success' => true,
            'data' => $products->map(fn (Product $product) => $this->productPayload($product))->values(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User unauthenticated.',
            ], 401);
        }

        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $count = EcommerceCompareItem::query()
            ->where('user_id', $user->id)
            ->count();

        $existing = EcommerceCompareItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $validated['product_id'])
            ->first();

        if (! $existing && $count >= 4) {
            return response()->json([
                'success' => false,
                'message' => 'You can compare up to 4 products only.',
            ], 422);
        }

        EcommerceCompareItem::query()->firstOrCreate([
            'user_id' => $user->id,
            'product_id' => $validated['product_id'],
        ]);

        return $this->index($request);
    }

    public function destroy(Request $request, Product $product)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        EcommerceCompareItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->delete();

        return $this->index($request);
    }

    public function clear(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => true,
                'message' => 'Compared products cleared successfully.',
                'data' => [],
            ]);
        }

        EcommerceCompareItem::query()
            ->where('user_id', $user->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Compared products cleared successfully.',
            'data' => [],
        ]);
    }

    private function attachReviewStats($products): void
    {
        if ($products->isEmpty()) {
            return;
        }

        $missing = $products->filter(fn (Product $p) => ! $p->relationLoaded('reviews'));
        if ($missing->isNotEmpty()) {
            $missing->load([
                'reviews' => fn ($query) => $query->whereRaw('LOWER(status) = ?', ['approved']),
            ]);
        }

        $products->each(function (Product $product) {
            $reviews = $product->reviews ?? collect();
            $reviewValues = $reviews->pluck('rating')->map(fn ($rating) => (int) $rating)->filter();
            $product->setAttribute('review_stats', [
                'average_rating' => $reviewValues->isNotEmpty() ? round($reviewValues->avg(), 1) : 0,
                'review_count' => $reviews->count(),
            ]);
        });
    }

    private function productPayload(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description ?: $product->short_description,
            'price' => $product->sale_price ?? $product->price,
            'images' => $product->images ?? [],
            'preview_assets' => $product->previewAssets?->map(fn ($asset) => [
                'id' => $asset->id,
                'image_path' => $asset->image_path,
                'side' => $asset->side,
            ])->values() ?? [],
            'review_stats' => $product->review_stats,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
            ] : null,
        ];
    }
}
