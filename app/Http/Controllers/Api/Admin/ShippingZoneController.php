<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingZone;
use Illuminate\Http\Request;

class ShippingZoneController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->user()?->hasAdminAccess()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $zones = ShippingZone::with('rates')->get();

        return response()->json([
            'success' => true,
            'data' => $zones,
        ]);
    }

    public function store(Request $request)
    {
        if (! $request->user()?->hasAdminAccess()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'regions' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $zone = ShippingZone::create($validated);

        return response()->json([
            'success' => true,
            'data' => $zone,
            'message' => 'Shipping zone created successfully.',
        ]);
    }

    public function show(Request $request, $id)
    {
        if (! $request->user()?->hasAdminAccess()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $zone = ShippingZone::with('rates')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $zone,
        ]);
    }

    public function update(Request $request, $id)
    {
        if (! $request->user()?->hasAdminAccess()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'regions' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $zone = ShippingZone::findOrFail($id);
        $zone->update($validated);

        return response()->json([
            'success' => true,
            'data' => $zone,
            'message' => 'Shipping zone updated successfully.',
        ]);
    }

    public function destroy(Request $request, $id)
    {
        if (! $request->user()?->hasAdminAccess()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $zone = ShippingZone::findOrFail($id);
        $zone->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shipping zone deleted successfully.',
        ]);
    }
}
