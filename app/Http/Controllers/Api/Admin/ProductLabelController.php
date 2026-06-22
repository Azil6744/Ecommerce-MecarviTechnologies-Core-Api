<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductLabel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductLabelController extends Controller
{
    public function index()
    {
        return response()->json(
            ProductLabel::query()
                ->orderBy('display_order')
                ->orderBy('name')
                ->get()
                ->map(fn (ProductLabel $label) => $this->transformLabel($label))
                ->values()
        );
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        $label = new ProductLabel();
        $this->fillLabel($label, $validated);
        $label->save();

        return response()->json($this->transformLabel($label), 201);
    }

    public function show(ProductLabel $productLabel)
    {
        return response()->json($this->transformLabel($productLabel));
    }

    public function update(Request $request, ProductLabel $productLabel)
    {
        $validated = $this->validatePayload($request, $productLabel->id);
        $this->fillLabel($productLabel, $validated);
        $productLabel->save();

        return response()->json($this->transformLabel($productLabel));
    }

    public function destroy(ProductLabel $productLabel)
    {
        $productLabel->delete();

        return response()->json(['message' => 'Product label deleted successfully']);
    }

    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('product_labels', 'name')->ignore($ignoreId)],
            'color_code' => ['required', 'string', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'display_order' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function fillLabel(ProductLabel $label, array $validated): void
    {
        $label->name = trim($validated['name']);
        $label->slug = Str::slug($validated['name']);
        $label->color_code = strtoupper($validated['color_code']);
        $label->display_order = $validated['display_order'] ?? ($label->display_order ?: 1);
        $label->description = isset($validated['description']) ? trim($validated['description']) : null;
        $label->is_active = (bool) ($validated['is_active'] ?? $label->is_active ?? true);
    }

    private function transformLabel(ProductLabel $label): array
    {
        return [
            'id' => $label->id,
            'name' => $label->name,
            'slug' => $label->slug,
            'color_code' => strtoupper($label->color_code),
            'display_order' => (int) $label->display_order,
            'description' => $label->description,
            'is_active' => (bool) $label->is_active,
            'status' => $label->is_active ? 'Active' : 'Inactive',
            'created_at' => optional($label->created_at)->toISOString(),
            'updated_at' => optional($label->updated_at)->toISOString(),
        ];
    }
}
