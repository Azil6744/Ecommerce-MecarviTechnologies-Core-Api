<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProductController extends Controller
{
    protected function normalizePayload(Request $request): array
    {
        $data = $request->all();

        foreach (['images', 'tags', 'attributes', 'variants'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $decoded = json_decode($data[$field], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data[$field] = $decoded;
                }
            }
        }

        foreach (['is_active', 'is_featured', 'is_digital'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = filter_var($data[$field], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            }
        }

        return $data;
    }

    protected function storeUploadedImages(Request $request): array
    {
        $storedImages = [];

        if ($request->hasFile('image_files')) {
            foreach (Arr::wrap($request->file('image_files')) as $file) {
                if ($file) {
                    $storedImages[] = $file->store('products', 'public');
                }
            }
        }

        return $storedImages;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::query()
            ->select([
                'id',
                'name',
                'sku',
                'description',
                'short_description',
                'price',
                'sale_price',
                'cost_price',
                'weight',
                'dimensions',
                'images',
                'tags',
                'attributes',
                'variants',
                'download_url',
                'seo_title',
                'seo_description',
                'stock_quantity',
                'low_stock_threshold',
                'category_id',
                'is_active',
                'is_featured',
                'is_digital',
                'created_at',
            ])
            ->with([
                'category:id,name,parent_id',
                'category.parent:id,name',
            ]);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $query->where(function ($builder) use ($request) {
                $builder->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('sku', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('is_digital')) {
            $query->where('is_digital', $request->boolean('is_digital'));
        }

        if ($request->filled('product_type')) {
            $query->where('attributes->product_type', $request->get('product_type'));
        }

        if ($request->has('sort')) {
            match ($request->get('sort')) {
                'name_asc' => $query->orderBy('name', 'asc'),
                'name_desc' => $query->orderBy('name', 'desc'),
                'price_asc' => $query->orderBy('price', 'asc'),
                'price_desc' => $query->orderBy('price', 'desc'),
                'oldest' => $query->orderBy('created_at', 'asc'),
                default => $query->orderBy('created_at', 'desc'),
            };
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate((int) $request->get('per_page', 15));

        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->normalizePayload($request);

        validator($data, [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'stock_quantity' => 'integer|min:0',
            'low_stock_threshold' => 'integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|string',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_digital' => 'boolean',
            'download_url' => 'nullable|string',
            'seo_title' => 'nullable|string',
            'seo_description' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'nullable|string',
            'attributes' => 'nullable|array',
            'variants' => 'nullable|array',
        ])->validate();

        $request->validate([
            'image_files' => 'nullable|array',
            'image_files.*' => 'nullable|image|max:5120',
        ]);

        $data['images'] = array_values(array_filter(array_merge(
            Arr::wrap($data['images'] ?? []),
            $this->storeUploadedImages($request)
        )));

        $product = Product::create($data);

        return response()->json($product->load('category'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return response()->json($product->load('category.parent'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $data = $this->normalizePayload($request);

        validator($data, [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'stock_quantity' => 'integer|min:0',
            'low_stock_threshold' => 'integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|string',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_digital' => 'boolean',
            'download_url' => 'nullable|string',
            'seo_title' => 'nullable|string',
            'seo_description' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'nullable|string',
            'attributes' => 'nullable|array',
            'variants' => 'nullable|array',
        ])->validate();

        $request->validate([
            'image_files' => 'nullable|array',
            'image_files.*' => 'nullable|image|max:5120',
        ]);

        $data['images'] = array_values(array_filter(array_merge(
            Arr::wrap($data['images'] ?? $product->images ?? []),
            $this->storeUploadedImages($request)
        )));

        $product->update($data);

        return response()->json($product->load('category.parent'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
