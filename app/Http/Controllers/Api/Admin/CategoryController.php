<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $hasParentId = Schema::hasColumn('categories', 'parent_id');
        $hasSortOrder = Schema::hasColumn('categories', 'sort_order');
        $columns = collect([
            'id',
            'name',
            'slug',
            'description',
            'image',
            'parent_id',
            'is_active',
            'sort_order',
            'created_at',
            'updated_at',
        ])->filter(fn ($column) => Schema::hasColumn('categories', $column))->values()->all();

        $rows = DB::table('categories')
            ->select($columns)
            ->when($hasSortOrder, fn ($query) => $query->orderBy('sort_order'))
            ->orderBy('name')
            ->get()
            ->map(function ($category) use ($hasParentId) {
                $category = (array) $category;
                $category['parent_id'] = $hasParentId ? ($category['parent_id'] ?? null) : null;
                $category['children'] = [];

                return $category;
            });

        if (! $hasParentId) {
            return response()->json($rows->values());
        }

        $childrenByParent = $rows->groupBy('parent_id');
        $categories = $rows
            ->whereNull('parent_id')
            ->map(function (array $category) use ($childrenByParent) {
                $category['children'] = $childrenByParent->get($category['id'], collect())->values();

                return $category;
            })
            ->values();

        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'image' => $request->image,
            'parent_id' => $request->parent_id,
            'is_active' => $request->is_active ?? true,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json($category, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return response()->json($category->load('children'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'image' => $request->image,
            'parent_id' => $request->parent_id,
            'is_active' => $request->is_active ?? $category->is_active,
            'sort_order' => $request->sort_order ?? $category->sort_order,
        ]);

        return response()->json($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }
}
