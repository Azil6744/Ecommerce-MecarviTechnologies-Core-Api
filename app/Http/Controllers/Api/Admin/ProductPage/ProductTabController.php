<?php

namespace App\Http\Controllers\Api\Admin\ProductPage;

use App\Http\Controllers\Controller;
use App\Models\ProductTab;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductTabController extends Controller
{
    use BroadcastsContentUpdates;

    public function index()
    {
        $tabs = ProductTab::with('items')->orderBy('order')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'product_tabs' => $tabs,
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            if (!$request->user()->hasAdminAccess()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $validated = $request->validate([
                'tab_name' => ['required', 'string', 'max:255'],
                'order' => ['integer'],
                'layout_type' => ['string', 'in:standard,image_left,image_right,slider'],
            ]);

            $tab = ProductTab::create($validated);

            $this->broadcastContentUpdate('product-tabs', 'created', ['id' => $tab->id]);

            return response()->json([
                'success' => true,
                'message' => 'Tab created successfully',
                'data' => ['product_tab' => $tab],
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

            $tab = ProductTab::find($id);
            if (!$tab) return response()->json(['success' => false, 'message' => 'Tab not found'], 404);

            $validated = $request->validate([
                'tab_name' => ['string', 'max:255'],
                'order' => ['integer'],
                'layout_type' => ['string', 'in:standard,image_left,image_right,slider'],
            ]);

            $tab->update($validated);

            $this->broadcastContentUpdate('product-tabs', 'updated', ['id' => $tab->id]);

            return response()->json([
                'success' => true,
                'message' => 'Tab updated successfully',
                'data' => ['product_tab' => $tab],
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

            $tab = ProductTab::find($id);
            if (!$tab) return response()->json(['success' => false, 'message' => 'Tab not found'], 404);

            $tab->delete();

            $this->broadcastContentUpdate('product-tabs', 'deleted', ['id' => $id]);

            return response()->json(['success' => true, 'message' => 'Tab deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Deletion failed.', 'error' => $e->getMessage()], 500);
        }
    }
}
