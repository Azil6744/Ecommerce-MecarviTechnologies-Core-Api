<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ProfileController extends Controller
{
    /**
     * Get user profile
     */
    public function show(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $request->user()->id,
            'username' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = $request->user();
        $payload = $request->only(['name', 'email', 'username', 'phone']);
        $allowed = [];

        foreach ($payload as $key => $value) {
            if (Schema::hasColumn($user->getTable(), $key)) {
                $allowed[$key] = $value;
            }
        }

        $user->update($allowed);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user->fresh(),
        ]);
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
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
        ]);
    }

    /**
     * Update user PIN
     */
    public function updatePin(Request $request)
    {
        $request->validate([
            'current_pin' => 'nullable|string|size:4|regex:/^\d+$/',
            'pin' => 'required|string|size:4|regex:/^\d+$/',
            'pin_confirmation' => 'required|same:pin',
        ]);

        $user = $request->user();
        if (!Schema::hasColumn($user->getTable(), 'pin')) {
            return response()->json([
                'success' => false,
                'message' => 'PIN support is not available on this server yet.',
            ], 422);
        }

        if ($user->pin && $request->filled('current_pin') && !Hash::check($request->current_pin, $user->pin)) {
            return response()->json([
                'success' => false,
                'message' => 'Current PIN is incorrect',
            ], 422);
        }

        $user->update(['pin' => Hash::make($request->pin)]);

        return response()->json([
            'success' => true,
            'message' => 'PIN updated successfully',
        ]);
    }
}
