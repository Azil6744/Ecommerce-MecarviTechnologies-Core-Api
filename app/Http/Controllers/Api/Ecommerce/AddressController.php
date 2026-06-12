<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()
            ->addresses()
            ->latest('is_default')
            ->latest()
            ->get()
            ->map(fn (EcommerceAddress $address) => $this->addressPayload($address));

        return response()->json([
            'success' => true,
            'data' => $addresses,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateAddress($request);
        $payload = $this->addressAttributes($request, $validated);

        if (($payload['is_default'] ?? false) || ! $request->user()->addresses()->exists()) {
            $this->clearDefaultAddress($request);
            $payload['is_default'] = true;
        }

        $address = $request->user()->addresses()->create($this->filterAddressColumns($payload));

        return response()->json([
            'success' => true,
            'message' => 'Address created successfully.',
            'data' => $this->addressPayload($address),
        ], 201);
    }

    public function show(Request $request, EcommerceAddress $address)
    {
        $this->authorizeAddress($request, $address);

        return response()->json([
            'success' => true,
            'data' => $this->addressPayload($address),
        ]);
    }

    public function update(Request $request, EcommerceAddress $address)
    {
        $this->authorizeAddress($request, $address);

        $validated = $this->validateAddress($request);
        $payload = $this->addressAttributes($request, $validated);

        if ($payload['is_default'] ?? false) {
            $this->clearDefaultAddress($request, $address->id);
        }

        $address->update($this->filterAddressColumns($payload));

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully.',
            'data' => $this->addressPayload($address->fresh()),
        ]);
    }

    public function setDefault(Request $request, EcommerceAddress $address)
    {
        $this->authorizeAddress($request, $address);
        $this->clearDefaultAddress($request, $address->id);
        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default address updated successfully.',
            'data' => $this->addressPayload($address->fresh()),
        ]);
    }

    public function destroy(Request $request, EcommerceAddress $address)
    {
        $this->authorizeAddress($request, $address);

        $wasDefault = (bool) $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $nextAddress = $request->user()->addresses()->latest()->first();
            if ($nextAddress) {
                $nextAddress->update(['is_default' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully.',
        ]);
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'company' => 'nullable|string|max:255',
            'address' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'sometimes|required|string|max:20',
            'country' => 'sometimes|required|string|max:100',
            'isDefault' => 'sometimes|boolean',
            'is_default' => 'sometimes|boolean',
            'type' => 'sometimes|in:billing,shipping',
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'email' => 'sometimes|nullable|email',
        ]);
    }

    private function addressAttributes(Request $request, array $validated): array
    {
        $name = $validated['name']
            ?? trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? ''));
        [$firstName, $lastName] = $this->splitName($name ?: $request->user()->name);

        $streetAddress = $validated['address'] ?? $validated['address_line_1'] ?? '';
        $zipCode = $validated['zip'] ?? $validated['zip_code'] ?? $validated['postal_code'] ?? '';
        $isDefault = $validated['isDefault'] ?? $validated['is_default'] ?? false;

        return [
            'type' => $validated['type'] ?? 'shipping',
            'title' => $validated['company'] ?? null,
            'first_name' => $validated['first_name'] ?? $firstName,
            'last_name' => $validated['last_name'] ?? $lastName,
            'company' => $validated['company'] ?? null,
            'email' => $validated['email'] ?? $request->user()->email,
            'phone' => $validated['phone'] ?? $request->user()->phone ?? 'N/A',
            'address' => $streetAddress,
            'address_line_1' => $streetAddress,
            'address_line_2' => null,
            'city' => $validated['city'] ?? '',
            'state' => $validated['state'] ?? '',
            'zip_code' => $zipCode,
            'postal_code' => $zipCode,
            'country' => $validated['country'] ?? 'United States',
            'is_default' => (bool) $isDefault,
        ];
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2) ?: [];

        return [
            $parts[0] ?? 'Customer',
            $parts[1] ?? '',
        ];
    }

    private function filterAddressColumns(array $payload): array
    {
        return collect($payload)
            ->filter(fn ($value, string $key) => Schema::hasColumn('ecommerce_addresses', $key))
            ->all();
    }

    private function addressPayload(EcommerceAddress $address): array
    {
        $name = trim($address->first_name . ' ' . $address->last_name);

        return [
            'id' => $address->id,
            'name' => $name !== '' ? $name : 'Customer',
            'company' => $address->company ?? $address->title ?? '',
            'address' => $address->address ?? $address->address_line_1 ?? '',
            'city' => $address->city ?? '',
            'state' => $address->state ?? '',
            'zip' => $address->zip_code ?? $address->postal_code ?? '',
            'country' => $address->country ?? '',
            'isDefault' => (bool) $address->is_default,
            'type' => $address->type ?? 'shipping',
            'email' => $address->email,
            'phone' => $address->phone,
        ];
    }

    private function authorizeAddress(Request $request, EcommerceAddress $address): void
    {
        abort_if($address->user_id !== $request->user()->id, 403, 'Unauthorized');
    }

    private function clearDefaultAddress(Request $request, ?int $exceptId = null): void
    {
        $query = $request->user()->addresses();

        if ($exceptId) {
            $query->whereKeyNot($exceptId);
        }

        $query->update(['is_default' => false]);
    }
}
