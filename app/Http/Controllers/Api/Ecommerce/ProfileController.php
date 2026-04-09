<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Get user profile
     */
    public function show(Request $request)
    {
        $user = $request->user();
        return response()->json($user);
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $request->user()->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'profile_picture' => 'nullable|string',
        ]);

        $user = $request->user();
        $user->update($request->only([
            'name',
            'email',
            'phone',
            'address',
            'city',
            'state',
            'zip',
            'country',
            'profile_picture',
        ]));

        return response()->json($user);
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect',
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['message' => 'Password updated successfully']);
    }

    /**
     * Update user PIN
     */
    public function updatePin(Request $request)
    {
        $request->validate([
            'pin' => 'required|string|size:4|regex:/^\d+$/',
            'pin_confirmation' => 'required|same:pin',
        ]);

        $user = $request->user();
        $user->update(['pin' => Hash::make($request->pin)]);

        return response()->json(['message' => 'PIN updated successfully']);
    }
}
