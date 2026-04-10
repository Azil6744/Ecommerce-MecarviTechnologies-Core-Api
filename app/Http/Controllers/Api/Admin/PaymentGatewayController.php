<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentGatewayController extends Controller
{
    public function index()
    {
        try {
            $gateways = PaymentGateway::orderBy('sort_order')->get();
            return response()->json([
                'success' => true,
                'data' => ['gateways' => $gateways],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch gateways.', 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'display_label' => 'nullable|string|max:255',
                'provider' => 'required|string|max:50',
                'description' => 'nullable|string',
                'public_key' => 'nullable|string',
                'secret_key' => 'nullable|string',
                'webhook_url' => 'nullable|string',
                'is_active' => 'boolean',
                'is_test_mode' => 'boolean',
                'settings' => 'nullable|array',
            ]);

            $gateway = PaymentGateway::create($validated);
            return response()->json(['success' => true, 'message' => 'Gateway created.', 'data' => ['gateway' => $gateway]], 201);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to create gateway.', 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function show($id)
    {
        try {
            $gateway = PaymentGateway::findOrFail($id);
            return response()->json(['success' => true, 'data' => ['gateway' => $gateway]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gateway not found.'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $gateway = PaymentGateway::findOrFail($id);
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'display_label' => 'nullable|string|max:255',
                'provider' => 'sometimes|string|max:50',
                'description' => 'nullable|string',
                'public_key' => 'nullable|string',
                'secret_key' => 'nullable|string',
                'webhook_url' => 'nullable|string',
                'is_active' => 'boolean',
                'is_test_mode' => 'boolean',
                'settings' => 'nullable|array',
            ]);

            $gateway->update($validated);
            return response()->json(['success' => true, 'message' => 'Gateway updated.', 'data' => ['gateway' => $gateway]]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update gateway.', 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function destroy($id)
    {
        try {
            PaymentGateway::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Gateway deleted.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete gateway.'], 500);
        }
    }
}
