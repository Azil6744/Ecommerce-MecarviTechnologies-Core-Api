<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\GlobalAttribute;
use App\Models\GlobalAttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAttributeController extends Controller
{
    public function index(Request $request)
    {
        $query = GlobalAttribute::with('values');

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $attributes = $query->orderBy('sort_order')->orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $attributes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'pricing_mode' => ['required', 'string', 'max:50'],
            'status' => ['required', 'string', 'max:50'],
            'values' => ['nullable', 'array'],
            'values.*.name' => ['required', 'string', 'max:255'],
            'values.*.image_path' => ['nullable', 'string', 'max:255'],
            'values.*.description' => ['nullable', 'string'],
            'values.*.price' => ['required', 'numeric', 'min:0'],
            'values.*.pricing_mode' => ['required', 'string'],
            'values.*.status' => ['required', 'string'],
            'values.*.sort_order' => ['nullable', 'integer'],
        ]);

        $attribute = DB::transaction(function () use ($validated) {
            $attribute = GlobalAttribute::create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'description' => $validated['description'] ?? null,
                'pricing_mode' => $validated['pricing_mode'],
                'status' => $validated['status'],
                'sort_order' => 0,
            ]);

            if (!empty($validated['values'])) {
                foreach ($validated['values'] as $index => $val) {
                    $attribute->values()->create([
                        'name' => $val['name'],
                        'image_path' => $val['image_path'] ?? null,
                        'description' => $val['description'] ?? null,
                        'price' => $val['price'],
                        'pricing_mode' => $val['pricing_mode'],
                        'status' => $val['status'],
                        'sort_order' => $val['sort_order'] ?? $index,
                    ]);
                }
            }

            return $attribute;
        });

        return response()->json([
            'success' => true,
            'message' => 'Attribute created successfully.',
            'data' => $attribute->load('values'),
        ], 201);
    }

    public function show(GlobalAttribute $attribute)
    {
        return response()->json([
            'success' => true,
            'data' => $attribute->load('values'),
        ]);
    }

    public function update(Request $request, GlobalAttribute $attribute)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'pricing_mode' => ['required', 'string', 'max:50'],
            'status' => ['required', 'string', 'max:50'],
            'values' => ['nullable', 'array'],
            'values.*.id' => ['nullable', 'integer'],
            'values.*.name' => ['required', 'string', 'max:255'],
            'values.*.image_path' => ['nullable', 'string', 'max:255'],
            'values.*.description' => ['nullable', 'string'],
            'values.*.price' => ['required', 'numeric', 'min:0'],
            'values.*.pricing_mode' => ['required', 'string'],
            'values.*.status' => ['required', 'string'],
            'values.*.sort_order' => ['nullable', 'integer'],
        ]);

        DB::transaction(function () use ($validated, $attribute) {
            $attribute->update([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'description' => $validated['description'] ?? null,
                'pricing_mode' => $validated['pricing_mode'],
                'status' => $validated['status'],
            ]);

            $incomingIds = [];
            if (!empty($validated['values'])) {
                foreach ($validated['values'] as $index => $val) {
                    if (!empty($val['id'])) {
                        // Update existing value
                        $attributeValue = GlobalAttributeValue::findOrFail($val['id']);
                        $attributeValue->update([
                            'name' => $val['name'],
                            'image_path' => $val['image_path'] ?? null,
                            'description' => $val['description'] ?? null,
                            'price' => $val['price'],
                            'pricing_mode' => $val['pricing_mode'],
                            'status' => $val['status'],
                            'sort_order' => $val['sort_order'] ?? $index,
                        ]);
                        $incomingIds[] = $val['id'];
                    } else {
                        // Create new value
                        $newVal = $attribute->values()->create([
                            'name' => $val['name'],
                            'image_path' => $val['image_path'] ?? null,
                            'description' => $val['description'] ?? null,
                            'price' => $val['price'],
                            'pricing_mode' => $val['pricing_mode'],
                            'status' => $val['status'],
                            'sort_order' => $val['sort_order'] ?? $index,
                        ]);
                        $incomingIds[] = $newVal->id;
                    }
                }
            }

            // Delete values not present in incoming list
            $attribute->values()->whereNotIn('id', $incomingIds)->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Attribute updated successfully.',
            'data' => $attribute->fresh()->load('values'),
        ]);
    }

    public function destroy(GlobalAttribute $attribute)
    {
        $attribute->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attribute deleted successfully.',
        ]);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'max:2048'],
        ]);

        $path = $request->file('image')->store('attribute-values', 'public');

        return response()->json([
            'success' => true,
            'image_path' => $path,
            'url' => asset('storage/' . $path),
        ]);
    }
}

