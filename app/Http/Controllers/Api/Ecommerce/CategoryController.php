<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of active categories for the storefront.
     */
    public function index(Request $request)
    {
        $categories = Category::query()
            ->with([
                'children' => function ($query) {
                    $query->where('is_active', true)
                        ->withCount([
                            'products as active_products_count' => function ($productQuery) {
                                $productQuery->where('is_active', true);
                            },
                        ])
                        ->orderBy('sort_order')
                        ->orderBy('name');
                },
            ])
            ->withCount([
                'products as active_products_count' => function ($query) {
                    $query->where('is_active', true);
                },
            ])
            ->when(
                !$request->boolean('include_children_only'),
                fn ($query) => $query->whereNull('parent_id')
            )
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function (Category $category) {
                $childProductCount = $category->children->sum('active_products_count');
                $category->setAttribute('active_products_count', $category->active_products_count + $childProductCount);

                return $category;
            });

        return response()->json($categories);
    }
}
