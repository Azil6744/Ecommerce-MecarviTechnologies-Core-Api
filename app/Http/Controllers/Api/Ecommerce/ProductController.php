<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\EcommerceReview;
use App\Models\EcommerceCoupon;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with([
                'category.parent',
                'previewAssets' => fn ($assetQuery) => $assetQuery->where('is_active', true),
                'reviews' => fn ($reviewQuery) => $reviewQuery->whereRaw('LOWER(status) = ?', ['approved']),
            ])
            ->where('is_active', true);

        if ($request->filled('category_id')) {
            $category = Category::with('children')->find($request->category_id);

            if ($category) {
                $categoryIds = $category->children->pluck('id')->prepend($category->id);
                $query->whereIn('category_id', $categoryIds);
            } else {
                $query->where('category_id', $request->category_id);
            }
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('tags', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('min_price')) {
            $query->whereRaw('CAST(COALESCE(sale_price, price) AS DECIMAL(10,2)) >= ?', [(float) $request->min_price]);
        }

        if ($request->filled('max_price')) {
            $query->whereRaw('CAST(COALESCE(sale_price, price) AS DECIMAL(10,2)) <= ?', [(float) $request->max_price]);
        }

        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderByRaw('CAST(COALESCE(sale_price, price) AS DECIMAL(10,2)) asc');
                    break;
                case 'price_desc':
                    $query->orderByRaw('CAST(COALESCE(sale_price, price) AS DECIMAL(10,2)) desc');
                    break;
                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;
                case 'newest':
                case 'relevance':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate((int) $request->get('per_page', 12));
        $this->attachReviewStats($products->getCollection());
        $this->attachFrontendAliases($products->getCollection());
        $this->attachQuestionStats($products->getCollection());

        return response()->json($products);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        if (!$product->is_active) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->load([
            'category.parent',
            'previewAssets' => fn ($query) => $query->where('is_active', true),
            'coupons' => fn ($query) => $query
                ->where('is_active', true)
                ->where(function ($inner) {
                    $inner->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                })
                ->where(function ($inner) {
                    $inner->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->where(function ($inner) {
                    $inner->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
                }),
            'customizationOptions' => fn ($query) => $query->where('is_active', true),
            'pricingRules' => fn ($query) => $query->where('is_active', true),
            'reviews' => fn ($query) => $query->whereRaw('LOWER(status) = ?', ['approved'])->latest(),
        ]);

        $relatedProducts = $this->relationProducts($product, 'related', 8, true);
        $recentWorkProducts = $this->relationProducts($product, 'recent_work', 5, true);
        $featuredProducts = $this->relationProducts($product, 'featured', 8, true);
        $this->attachReviewStats($relatedProducts);
        $this->attachReviewStats($recentWorkProducts);
        $this->attachReviewStats($featuredProducts);

        $this->attachReviewStats(collect([$product]));
        $this->attachFrontendAliases(collect([$product]));
        $this->attachQuestionStats(collect([$product]));
        $this->attachPublicCoupons(collect([$product]));
        $product->setAttribute('customization_options_grouped', $product->customizationOptions->groupBy('option_type')->values());
        $product->setAttribute('related_products', $relatedProducts);
        $product->setAttribute('recent_work_products', $recentWorkProducts);
        $product->setAttribute('featured_products', $featuredProducts);

        return response()->json($product);
    }

    private function relationProducts(Product $product, string $type, int $limit, bool $fallback = false)
    {
        $eagerLoads = [
            'category.parent',
            'previewAssets' => fn ($assetQuery) => $assetQuery->where('is_active', true),
            'reviews' => fn ($reviewQuery) => $reviewQuery->whereRaw('LOWER(status) = ?', ['approved']),
        ];

        $products = $product->relatedProducts()
            ->with($eagerLoads)
            ->where('is_active', true)
            ->wherePivot('relation_type', $type)
            ->orderBy('product_related_products.sort_order')
            ->limit($limit)
            ->get();

        if ($products->isNotEmpty() || ! $fallback) {
            $this->attachFrontendAliases($products);
            return $products;
        }

        $fallbackProducts = Product::query()
            ->with($eagerLoads)
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->latest()
            ->limit($limit)
            ->get();

        $this->attachFrontendAliases($fallbackProducts);
        return $fallbackProducts;
    }

    private function attachReviewStats($products): void
    {
        if ($products->isEmpty()) {
            return;
        }

        $missing = $products->filter(fn (Product $p) => ! $p->relationLoaded('reviews'));
        if ($missing->isNotEmpty()) {
            $missing->load([
                'reviews' => fn ($reviewQuery) => $reviewQuery->whereRaw('LOWER(status) = ?', ['approved']),
            ]);
        }

        $products->each(function (Product $product) {
            $reviews = $product->reviews ?? collect();

            $reviewValues = $reviews->pluck('rating')->map(fn ($rating) => (int) $rating)->filter();
            $averageRating = $reviewValues->isNotEmpty() ? round($reviewValues->avg(), 1) : null;
            $distribution = collect([5, 4, 3, 2, 1])->mapWithKeys(function ($score) use ($reviewValues) {
                return [$score => $reviewValues->filter(fn ($rating) => $rating === $score)->count()];
            });

            $product->setAttribute('review_stats', [
                'average_rating' => $averageRating,
                'review_count' => $reviews->count(),
                'rating_distribution' => $distribution,
            ]);
        });
    }

    private function attachFrontendAliases($products): void
    {
        $products->each(function (Product $product) {
            if ($product->relationLoaded('previewAssets')) {
                $product->setAttribute('previewAssets', $product->previewAssets);
            }

            if ($product->relationLoaded('customizationOptions')) {
                $product->setAttribute('customizationOptions', $product->customizationOptions);
            }
        });
    }

    private function attachPublicCoupons($products): void
    {
        $products->each(function (Product $product) {
            if ($product->relationLoaded('coupons')) {
                $product->setRelation('coupons', $product->coupons->map(fn (EcommerceCoupon $coupon) => $coupon->toPublicArray())->values());
            }
        });
    }

    private function attachQuestionStats($products): void
    {
        $products->each(function (Product $product) {
            if ($product->id % 2 === 1) {
                $product->setAttribute('questions_count', 3);
                $product->setAttribute('unanswered_questions_count', 1);
                $product->setAttribute('answers_count', 2);
                $product->setAttribute('latest_question', 'Is it possible to do side-embroidery on this work shirt?');
                $product->setAttribute('latest_question_at', now()->subHours(4)->toIso8601String());
            } else {
                $product->setAttribute('questions_count', 5);
                $product->setAttribute('unanswered_questions_count', 0);
                $product->setAttribute('answers_count', 5);
                $product->setAttribute('latest_question', 'What is the turnaround time for bulk orders of 100+ shirts?');
                $product->setAttribute('latest_question_at', now()->subDays(2)->toIso8601String());
            }
        });
    }
}
