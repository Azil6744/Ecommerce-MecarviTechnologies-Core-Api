<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceAddress;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * Get all user addresses
     */
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->get();
        return response()->json($addresses);
    }

    /**
     * Create new address
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:billing,shipping',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email',
            'country' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'zip_code' => 'required|string|max:20',
            'is_default' => 'boolean',
        ]);

        $address = $request->user()->addresses()->create($request->all());

        return response()->json($address, 201);
    }

    /**
     * Get single address
     */
    public function show(Request $request, EcommerceAddress $address)
    {
        // Verify ownership
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($address);
    }

    /**
     * Update address
     */
    public function update(Request $request, EcommerceAddress $address)
    {
        // Verify ownership
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'type' => 'required|in:billing,shipping',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email',
            'country' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'zip_code' => 'required|string|max:20',
            'is_default' => 'boolean',
        ]);

        $address->update($request->all());

        return response()->json($address);
    }

    /**
     * Delete address
     */
    public function destroy(Request $request, EcommerceAddress $address)
    {
        // Verify ownership
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $address->delete();

        return response()->json(['message' => 'Address deleted successfully']);
    }
}
