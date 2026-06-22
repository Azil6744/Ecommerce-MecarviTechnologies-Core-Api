<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductBrandController extends Controller
{
    public function index()
    {
        return response()->json(
            ProductBrand::query()
                ->orderBy('priority')
                ->orderBy('name')
                ->get()
                ->map(fn (ProductBrand $brand) => $this->transformBrand($brand))
                ->values()
        );
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        $brand = new ProductBrand();
        $this->fillBrand($brand, $validated);
        $brand->save();

        return response()->json($this->transformBrand($brand), 201);
    }

    public function show(ProductBrand $brand)
    {
        return response()->json($this->transformBrand($brand));
    }

    public function update(Request $request, ProductBrand $brand)
    {
        $validated = $this->validatePayload($request, $brand->id);
        $this->fillBrand($brand, $validated);
        $brand->save();

        return response()->json($this->transformBrand($brand));
    }

    public function destroy(ProductBrand $brand)
    {
        $brand->delete();

        return response()->json(['message' => 'Product brand deleted successfully']);
    }

    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('product_brands', 'name')->ignore($ignoreId)],
            'website_url' => ['nullable', 'url', 'max:255'],
            'priority' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function fillBrand(ProductBrand $brand, array $validated): void
    {
        $brand->name = trim($validated['name']);
        $brand->slug = Str::slug($validated['name']);
        $brand->website_url = isset($validated['website_url']) ? trim($validated['website_url']) : null;
        $brand->priority = $validated['priority'] ?? ($brand->priority ?: 1);
        $brand->description = isset($validated['description']) ? trim($validated['description']) : null;
        $brand->is_active = (bool) ($validated['is_active'] ?? $brand->is_active ?? true);
    }

    private function transformBrand(ProductBrand $brand): array
    {
        return [
            'id' => $brand->id,
            'name' => $brand->name,
            'slug' => $brand->slug,
            'website_url' => $brand->website_url,
            'priority' => (int) $brand->priority,
            'description' => $brand->description,
            'is_active' => (bool) $brand->is_active,
            'status' => $brand->is_active ? 'Active' : 'Draft',
            'created_at' => optional($brand->created_at)->toISOString(),
            'updated_at' => optional($brand->updated_at)->toISOString(),
        ];
    }
}
