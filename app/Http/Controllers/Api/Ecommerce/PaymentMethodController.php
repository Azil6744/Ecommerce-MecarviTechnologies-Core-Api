<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceAddress;
use App\Models\EcommercePaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        $methods = $request->user()
            ->paymentMethods()
            ->with('billingAddress')
            ->latest('is_default')
            ->latest()
            ->get()
            ->map(fn (EcommercePaymentMethod $method) => $this->paymentMethodPayload($method));

        return response()->json([
            'success' => true,
            'data' => $methods,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'sometimes|string|max:50',
            'provider_customer_id' => 'nullable|string|max:255',
            'provider_payment_method_id' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:50',
            'last4' => 'required|string|size:4',
            'exp_month' => 'nullable|integer|min:1|max:12',
            'exp_year' => 'nullable|integer|min:2024|max:2100',
            'cardholder_name' => 'nullable|string|max:255',
            'billing_address_id' => 'nullable|integer',
            'is_default' => 'sometimes|boolean',
            'isDefault' => 'sometimes|boolean',
        ]);

        if (! empty($validated['billing_address_id'])) {
            $this->authorizeAddress($request, (int) $validated['billing_address_id']);
        }

        $payload = [
            'provider' => $validated['provider'] ?? 'manual',
            'provider_customer_id' => $validated['provider_customer_id'] ?? null,
            'provider_payment_method_id' => $validated['provider_payment_method_id'] ?? null,
            'brand' => $validated['brand'] ?? null,
            'last4' => $validated['last4'],
            'exp_month' => $validated['exp_month'] ?? null,
            'exp_year' => $validated['exp_year'] ?? null,
            'cardholder_name' => $validated['cardholder_name'] ?? $request->user()->name,
            'billing_address_id' => $validated['billing_address_id'] ?? null,
            'is_default' => (bool) ($validated['isDefault'] ?? $validated['is_default'] ?? false),
        ];

        if ($payload['is_default'] || ! $request->user()->paymentMethods()->exists()) {
            $this->clearDefaultPaymentMethod($request);
            $payload['is_default'] = true;
        }

        $method = $request->user()->paymentMethods()->create($payload);

        return response()->json([
            'success' => true,
            'message' => 'Payment method saved successfully.',
            'data' => $this->paymentMethodPayload($method->load('billingAddress')),
        ], 201);
    }

    public function setDefault(Request $request, EcommercePaymentMethod $paymentMethod)
    {
        $this->authorizePaymentMethod($request, $paymentMethod);
        $this->clearDefaultPaymentMethod($request, $paymentMethod->id);
        $paymentMethod->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default payment method updated successfully.',
            'data' => $this->paymentMethodPayload($paymentMethod->fresh()->load('billingAddress')),
        ]);
    }

    public function destroy(Request $request, EcommercePaymentMethod $paymentMethod)
    {
        $this->authorizePaymentMethod($request, $paymentMethod);

        $wasDefault = (bool) $paymentMethod->is_default;
        $paymentMethod->delete();

        if ($wasDefault) {
            $nextMethod = $request->user()->paymentMethods()->latest()->first();
            if ($nextMethod) {
                $nextMethod->update(['is_default' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment method deleted successfully.',
        ]);
    }

    private function paymentMethodPayload(EcommercePaymentMethod $method): array
    {
        return [
            'id' => $method->id,
            'provider' => $method->provider,
            'brand' => $method->brand ?: 'Card',
            'last4' => $method->last4,
            'expires' => $this->formattedExpiry($method),
            'exp_month' => $method->exp_month,
            'exp_year' => $method->exp_year,
            'holder' => $method->cardholder_name ?: $method->user?->name,
            'isDefault' => (bool) $method->is_default,
            'billingAddress' => $this->billingAddressPayload($method->billingAddress),
        ];
    }

    private function billingAddressPayload(?EcommerceAddress $address): array
    {
        if (! $address) {
            return [];
        }

        return array_values(array_filter([
            $address->address ?? $address->address_line_1 ?? null,
            trim(($address->city ?? '') . ', ' . ($address->state ?? '') . ' ' . ($address->zip_code ?? $address->postal_code ?? '')),
            $address->country,
        ]));
    }

    private function formattedExpiry(EcommercePaymentMethod $method): string
    {
        if (! $method->exp_month || ! $method->exp_year) {
            return '';
        }

        return str_pad((string) $method->exp_month, 2, '0', STR_PAD_LEFT) . '/' . $method->exp_year;
    }

    private function authorizePaymentMethod(Request $request, EcommercePaymentMethod $paymentMethod): void
    {
        abort_if($paymentMethod->user_id !== $request->user()->id, 403, 'Unauthorized');
    }

    private function authorizeAddress(Request $request, int $addressId): void
    {
        abort_if(
            ! $request->user()->addresses()->whereKey($addressId)->exists(),
            403,
            'Billing address does not belong to this user.'
        );
    }

    private function clearDefaultPaymentMethod(Request $request, ?int $exceptId = null): void
    {
        $query = $request->user()->paymentMethods();

        if ($exceptId) {
            $query->whereKeyNot($exceptId);
        }

        $query->update(['is_default' => false]);
    }
}
