<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingRate;
use Illuminate\Http\Request;

class ShippingRateController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->user()?->hasAdminAccess()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $rates = ShippingRate::with('zone')->get();

        return response()->json([
            'success' => true,
            'data' => $rates,
        ]);
    }

    public function store(Request $request)
    {
        if (! $request->user()?->hasAdminAccess()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'shipping_zone_id' => 'required|integer|exists:shipping_zones,id',
            'name' => 'required|string|max:255',
            'rate_type' => 'required|string|in:flat,weight_based,price_based',
            'min_value' => 'required|numeric|min:0',
            'max_value' => 'nullable|numeric|min:0',
            'rate_amount' => 'required|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $rate = ShippingRate::create($validated);

        return response()->json([
            'success' => true,
            'data' => $rate,
            'message' => 'Shipping rate created successfully.',
        ]);
    }

    public function show(Request $request, $id)
    {
        if (! $request->user()?->hasAdminAccess()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $rate = ShippingRate::with('zone')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $rate,
        ]);
    }

    public function update(Request $request, $id)
    {
        if (! $request->user()?->hasAdminAccess()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'shipping_zone_id' => 'sometimes|required|integer|exists:shipping_zones,id',
            'name' => 'sometimes|required|string|max:255',
            'rate_type' => 'sometimes|required|string|in:flat,weight_based,price_based',
            'min_value' => 'sometimes|required|numeric|min:0',
            'max_value' => 'nullable|numeric|min:0',
            'rate_amount' => 'sometimes|required|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $rate = ShippingRate::findOrFail($id);
        $rate->update($validated);

        return response()->json([
            'success' => true,
            'data' => $rate,
            'message' => 'Shipping rate updated successfully.',
        ]);
    }

    public function destroy(Request $request, $id)
    {
        if (! $request->user()?->hasAdminAccess()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $rate = ShippingRate::findOrFail($id);
        $rate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shipping rate deleted successfully.',
        ]);
    }
}
