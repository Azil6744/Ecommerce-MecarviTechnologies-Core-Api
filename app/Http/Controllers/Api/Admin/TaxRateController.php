<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxRate;
use Illuminate\Http\Request;

class TaxRateController extends Controller
{
    /**
     * Display a listing of the tax rates.
     */
    public function index()
    {
        try {
            $rates = TaxRate::orderBy('created_at', 'desc')->get();
            return response()->json([
                'success' => true,
                'data' => $rates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tax rates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created tax rate.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'label' => 'required|string|max:255',
                'rate' => 'required|numeric|min:0',
                'state' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
                'is_active' => 'sometimes|boolean',
            ]);

            $rate = TaxRate::create($validated);

            return response()->json([
                'success' => true,
                'data' => $rate,
                'message' => 'Tax rate created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tax rate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified tax rate.
     */
    public function update(Request $request, $id)
    {
        try {
            $rate = TaxRate::findOrFail($id);

            $validated = $request->validate([
                'label' => 'sometimes|required|string|max:255',
                'rate' => 'sometimes|required|numeric|min:0',
                'state' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
                'is_active' => 'sometimes|boolean',
            ]);

            $rate->update($validated);

            return response()->json([
                'success' => true,
                'data' => $rate,
                'message' => 'Tax rate updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tax rate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified tax rate.
     */
    public function destroy($id)
    {
        try {
            $rate = TaxRate::findOrFail($id);
            $rate->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tax rate deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tax rate',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
