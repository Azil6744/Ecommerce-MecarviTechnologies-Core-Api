<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

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
                ->all()
        );
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        $brand = new ProductBrand();
        $this->fillBrand($brand, $validated, $request);
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
        $this->fillBrand($brand, $validated, $request);
        $brand->save();

        return response()->json($this->transformBrand($brand));
    }

    public function destroy(ProductBrand $brand)
    {
        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }
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
            'logo' => ['nullable', 'string'], // Accept base64 string or file path
            'seo_meta_title' => ['nullable', 'string', 'max:255'],
            'seo_meta_description' => ['nullable', 'string'],
        ]);
    }

    private function resolveLogo(Request $request, ?string $oldPath = null): ?string
    {
        if ($request->hasFile('logo')) {
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
            return $request->file('logo')->store('brands', 'public');
        }

        if ($request->has('logo') && is_string($request->input('logo'))) {
            $logoString = $request->input('logo');

            if (preg_match('/^data:image\/(\w+);base64,/', $logoString, $matches)) {
                $imageType = $matches[1];
                $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $logoString));

                if ($imageData !== false) {
                    if ($oldPath) {
                        Storage::disk('public')->delete($oldPath);
                    }
                    $filename = 'brand_' . time() . '_' . Str::random(6) . '.' . $imageType;
                    $logoPath = 'brands/' . $filename;
                    Storage::disk('public')->put($logoPath, $imageData);
                    return $logoPath;
                }
            }

            // If it's a relative path to storage, keep it as-is
            if (!str_starts_with($logoString, 'data:')) {
                return $logoString ?: null;
            }
        }

        return $oldPath; // Keep existing if not changed/not provided
    }

    private function fillBrand(ProductBrand $brand, array $validated, Request $request): void
    {
        $brand->name = trim($validated['name']);
        $brand->slug = Str::slug($validated['name']);
        $brand->website_url = isset($validated['website_url']) ? trim($validated['website_url']) : null;
        $brand->priority = $validated['priority'] ?? ($brand->priority ?: 1);
        $brand->description = isset($validated['description']) ? trim($validated['description']) : null;
        $brand->is_active = (bool) ($validated['is_active'] ?? $brand->is_active ?? true);
        $brand->logo = $this->resolveLogo($request, $brand->logo);
        $brand->seo_meta_title = isset($validated['seo_meta_title']) ? trim($validated['seo_meta_title']) : null;
        $brand->seo_meta_description = isset($validated['seo_meta_description']) ? trim($validated['seo_meta_description']) : null;
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
            'logo' => $brand->logo,
            'seo_meta_title' => $brand->seo_meta_title,
            'seo_meta_description' => $brand->seo_meta_description,
            'status' => $brand->is_active ? 'Active' : 'Draft',
            'created_at' => optional($brand->created_at)->toISOString(),
            'updated_at' => optional($brand->updated_at)->toISOString(),
        ];
    }
}
