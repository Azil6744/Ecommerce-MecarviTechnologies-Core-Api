<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\EcommerceReview;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category.parent'])
            ->where('is_active', true);

        if ($request->has('category_id')) {
            $category = Category::with('children')->find($request->category_id);

            if ($category) {
                $categoryIds = $category->children->pluck('id')->prepend($category->id);
                $query->whereIn('category_id', $categoryIds);
            } else {
                $query->where('category_id', $request->category_id);
            }
        }

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('tags', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('min_price')) {
            $query->whereRaw('CAST(COALESCE(sale_price, price) AS REAL) >= ?', [(float) $request->min_price]);
        }

        if ($request->has('max_price')) {
            $query->whereRaw('CAST(COALESCE(sale_price, price) AS REAL) <= ?', [(float) $request->max_price]);
        }

        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderByRaw('CAST(COALESCE(sale_price, price) AS REAL) asc');
                    break;
                case 'price_desc':
                    $query->orderByRaw('CAST(COALESCE(sale_price, price) AS REAL) desc');
                    break;
                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;
                case 'newest':
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

        $product->load('category.parent');
        $product->setRelation('reviews', EcommerceReview::query()
            ->where('product_id', (string) $product->id)
            ->whereIn('status', ['approved', 'Approved'])
            ->latest()
            ->get());

        return response()->json($product);
    }
}
