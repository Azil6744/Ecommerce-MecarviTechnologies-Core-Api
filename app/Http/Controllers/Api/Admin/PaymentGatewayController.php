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
            if ($gateways->isEmpty()) {
                $defaultGateways = [
                    ['name' => 'Wallet', 'display_label' => 'Wallet Balance', 'provider' => 'wallet', 'description' => 'Pay using your Mecarvi Wallet balance.', 'is_active' => true, 'sort_order' => 1],
                    ['name' => 'PayPal', 'display_label' => 'PayPal', 'provider' => 'paypal', 'description' => 'Pay securely with your PayPal account.', 'is_active' => true, 'sort_order' => 2],
                    ['name' => 'Cash App Pay', 'display_label' => 'Cash App', 'provider' => 'cashapp', 'description' => 'Pay instantly with Cash App.', 'is_active' => true, 'sort_order' => 3],
                    ['name' => 'Voucher', 'display_label' => 'Store Voucher', 'provider' => 'voucher', 'description' => 'Use store voucher or gift voucher.', 'is_active' => true, 'sort_order' => 4],
                    ['name' => 'Gift Cards', 'display_label' => 'Gift Card', 'provider' => 'giftcard', 'description' => 'Use your Mecarvi Gift Card balance.', 'is_active' => true, 'sort_order' => 5],
                    ['name' => 'Financing / Installments', 'display_label' => 'Financing', 'provider' => 'financing', 'description' => 'Easy monthly payments with flexible options.', 'is_active' => true, 'sort_order' => 6],
                ];
                foreach ($defaultGateways as $gw) {
                    PaymentGateway::create($gw);
                }
                $gateways = PaymentGateway::orderBy('sort_order')->get();
            }
            return response()->json([
                'success' => true,
                'data' => ['gateways' => $gateways],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch gateways.', 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function publicIndex()
    {
        try {
            $gateways = PaymentGateway::where('is_active', true)->orderBy('sort_order')->get();
            if ($gateways->isEmpty()) {
                $defaultGateways = [
                    ['name' => 'Wallet', 'display_label' => 'Wallet Balance', 'provider' => 'wallet', 'description' => 'Pay using your Mecarvi Wallet balance.', 'is_active' => true, 'sort_order' => 1],
                    ['name' => 'PayPal', 'display_label' => 'PayPal', 'provider' => 'paypal', 'description' => 'Pay securely with your PayPal account.', 'is_active' => true, 'sort_order' => 2],
                    ['name' => 'Cash App Pay', 'display_label' => 'Cash App', 'provider' => 'cashapp', 'description' => 'Pay instantly with Cash App.', 'is_active' => true, 'sort_order' => 3],
                    ['name' => 'Voucher', 'display_label' => 'Store Voucher', 'provider' => 'voucher', 'description' => 'Use store voucher or gift voucher.', 'is_active' => true, 'sort_order' => 4],
                    ['name' => 'Gift Cards', 'display_label' => 'Gift Card', 'provider' => 'giftcard', 'description' => 'Use your Mecarvi Gift Card balance.', 'is_active' => true, 'sort_order' => 5],
                    ['name' => 'Financing / Installments', 'display_label' => 'Financing', 'provider' => 'financing', 'description' => 'Easy monthly payments with flexible options.', 'is_active' => true, 'sort_order' => 6],
                ];
                foreach ($defaultGateways as $gw) {
                    PaymentGateway::create($gw);
                }
                $gateways = PaymentGateway::where('is_active', true)->orderBy('sort_order')->get();
            }

            return response()->json([
                'success' => true,
                'data' => ['gateways' => $gateways],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch payment gateways.', 'error' => config('app.debug') ? $e->getMessage() : null], 500);
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
