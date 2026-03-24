<?php

namespace App\Http\Controllers\Api\Admin\ProductPage;

use App\Http\Controllers\Controller;
use App\Models\ProductItem;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductItemController extends Controller
{
    use BroadcastsContentUpdates;

    public function store(Request $request)
    {
        try {
            if (!$request->user()->hasAdminAccess()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $validated = $request->validate([
                'product_tab_id' => ['required', 'exists:product_tabs,id'],
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:51200'],
                'image_url' => ['nullable', 'string'],
                'video_url' => ['nullable', 'string'],
                'card_title_one' => ['nullable', 'string', 'max:255'],
                'card_text_one' => ['nullable', 'string'],
                'card_title_two' => ['nullable', 'string', 'max:255'],
                'card_text_two' => ['nullable', 'string'],
                'order' => ['integer'],
            ]);

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('product-items', 'public');
                $validated['image_url'] = 'storage/' . $path;
            }
            unset($validated['image']);

            $item = ProductItem::create($validated);

            $this->broadcastContentUpdate('product-items', 'created', ['id' => $item->id]);

            return response()->json([
                'success' => true,
                'message' => 'Item created successfully',
                'data' => ['product_item' => $item],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Creation failed.', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            if (!$request->user()->hasAdminAccess()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $item = ProductItem::find($id);
            if (!$item) return response()->json(['success' => false, 'message' => 'Item not found'], 404);

            $validated = $request->validate([
                'title' => ['string', 'max:255'],
                'description' => ['nullable', 'string'],
                'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:51200'],
                'image_url' => ['nullable', 'string'],
                'video_url' => ['nullable', 'string'],
                'card_title_one' => ['nullable', 'string', 'max:255'],
                'card_text_one' => ['nullable', 'string'],
                'card_title_two' => ['nullable', 'string', 'max:255'],
                'card_text_two' => ['nullable', 'string'],
                'order' => ['integer'],
            ]);

            if ($request->hasFile('image')) {
                $oldPath = str_replace('storage/', '', $item->image_url ?? '');
                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
                $path = $request->file('image')->store('product-items', 'public');
                $validated['image_url'] = 'storage/' . $path;
            }
            unset($validated['image']);

            $item->update($validated);

            $this->broadcastContentUpdate('product-items', 'updated', ['id' => $item->id]);

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully',
                'data' => ['product_item' => $item],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Update failed.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            if (!$request->user()->hasAdminAccess()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $item = ProductItem::find($id);
            if (!$item) return response()->json(['success' => false, 'message' => 'Item not found'], 404);

            $item->delete();

            $this->broadcastContentUpdate('product-items', 'deleted', ['id' => $id]);

            return response()->json(['success' => true, 'message' => 'Item deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Deletion failed.', 'error' => $e->getMessage()], 500);
        }
    }
}
