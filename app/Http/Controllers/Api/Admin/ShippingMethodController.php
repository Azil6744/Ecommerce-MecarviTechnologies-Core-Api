<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ShippingMethodController extends Controller
{
    public function index()
    {
        try {
            $methods = ShippingMethod::orderBy('sort_order')->get();
            return response()->json([
                'success' => true,
                'data' => [
                    'methods' => $methods,
                    'shipping_methods' => $methods,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch shipping methods.'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:shipping_methods,code',
                'description' => 'nullable|string',
                'base_rate' => 'required|numeric|min:0',
                'estimated_days' => 'nullable|string|max:50',
                'coverage' => 'nullable|string|max:100',
                'is_active' => 'boolean',
                'free_shipping_threshold' => 'nullable|numeric|min:0',
            ]);
            $method = ShippingMethod::create($validated);
            return response()->json(['success' => true, 'message' => 'Shipping method created.', 'data' => ['method' => $method]], 201);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to create shipping method.', 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $method = ShippingMethod::findOrFail($id);
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'code' => 'sometimes|string|max:50|unique:shipping_methods,code,' . $id,
                'description' => 'nullable|string',
                'base_rate' => 'sometimes|numeric|min:0',
                'estimated_days' => 'nullable|string|max:50',
                'coverage' => 'nullable|string|max:100',
                'is_active' => 'boolean',
                'free_shipping_threshold' => 'nullable|numeric|min:0',
            ]);
            $method->update($validated);
            return response()->json(['success' => true, 'message' => 'Shipping method updated.', 'data' => ['method' => $method]]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update.', 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function destroy($id)
    {
        try {
            ShippingMethod::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Shipping method deleted.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete.'], 500);
        }
    }
}
