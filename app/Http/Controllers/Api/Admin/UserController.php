<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Create a new user (Super Admin only).
     * 
     * Allows super admin to create users with specific roles.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Check if user is super admin
            $currentUser = $request->user();
            
            if (!$currentUser->isSuperAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only super admin can create users.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', Password::defaults()],
                'role' => ['required', 'string', 'in:super_admin,editor,viewer'],
            ]);

            // Create the new user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
            ]);

            // Return success response with user data
            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at,
                    ],
                ],
            ], 201);
        } catch (ValidationException $e) {
            // Return validation errors
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Return general error response
            return response()->json([
                'success' => false,
                'message' => 'User creation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during user creation.',
            ], 500);
        }
    }

    /**
     * Update an existing user (Super Admin only).
     * 
     * Allows super admin to update user information including role.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            // Check if user is super admin
            $currentUser = $request->user();
            
            if (!$currentUser->isSuperAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only super admin can update users.',
                ], 403);
            }

            // Find the user to update
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'name' => ['sometimes', 'required', 'string', 'max:255'],
                'email' => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
                'password' => ['sometimes', 'nullable', 'string', Password::defaults()],
                'role' => ['sometimes', 'required', 'string', 'in:super_admin,editor,viewer'],
            ]);

            // Update user fields
            if (isset($validated['name'])) {
                $user->name = $validated['name'];
            }
            if (isset($validated['email'])) {
                $user->email = $validated['email'];
            }
            if (isset($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }
            if (isset($validated['role'])) {
                $user->role = $validated['role'];
            }

            $user->save();

            // Return success response with updated user data
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'email_verified_at' => $user->email_verified_at,
                        'updated_at' => $user->updated_at,
                    ],
                ],
            ], 200);
        } catch (ValidationException $e) {
            // Return validation errors
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Return general error response
            return response()->json([
                'success' => false,
                'message' => 'User update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during user update.',
            ], 500);
        }
    }

    /**
     * Delete a user (Super Admin only).
     * 
     * Allows super admin to delete users from the system.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            // Check if user is super admin
            $currentUser = $request->user();
            
            if (!$currentUser->isSuperAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only super admin can delete users.',
                ], 403);
            }

            // Find the user to delete
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            // Prevent deleting yourself
            if ($user->id === $currentUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account.',
                ], 403);
            }

            // Delete the user
            $user->delete();

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            // Return general error response
            return response()->json([
                'success' => false,
                'message' => 'User deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during user deletion.',
            ], 500);
        }
    }
}
