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
     * Create a new user (Admin only).
     *
     * Allows admin users to create users with specific roles.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $authUser = $request->user();
            $isSuperAdmin = $authUser->role === 'super_admin';
            try { $isSuperAdmin = $isSuperAdmin || $authUser->hasRole('super_admin'); } catch (\Exception $e) {}

            if (!$isSuperAdmin) {
                try {
                    if (!$authUser->hasPermissionTo('create users')) {
                        return response()->json(['success' => false, 'message' => 'Unauthorized. Insufficient permissions.'], 403);
                    }
                } catch (\Exception $e) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized. Permission not configured.'], 403);
                }
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', Password::defaults()],
                'roles' => ['sometimes', 'array'],
                'roles.*' => ['string', 'exists:roles,name'],
            ]);

            // Create the new user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Assign roles if provided
            if (isset($validated['roles']) && is_array($validated['roles'])) {
                $user->assignRole($validated['roles']);
            }

            // Return success response with user data
            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'roles' => $user->roles->pluck('name')->toArray(),
                        'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
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
     * Update an existing user (Admin only).
     *
     * Allows admin users to update user information including roles.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $authUser = $request->user();
            $isSuperAdmin = $authUser->role === 'super_admin';
            try { $isSuperAdmin = $isSuperAdmin || $authUser->hasRole('super_admin'); } catch (\Exception $e) {}

            if (!$isSuperAdmin) {
                try {
                    if (!$authUser->hasPermissionTo('edit users')) {
                        return response()->json(['success' => false, 'message' => 'Unauthorized. Insufficient permissions.'], 403);
                    }
                } catch (\Exception $e) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized. Permission not configured.'], 403);
                }
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
                'roles' => ['sometimes', 'array'],
                'roles.*' => ['string', 'exists:roles,name'],
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

            // Update roles if provided
            if (isset($validated['roles'])) {
                $user->syncRoles($validated['roles']);
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
                        'roles' => $user->roles->pluck('name')->toArray(),
                        'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
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
     * Delete a user (Admin only).
     *
     * Allows admin users to delete users from the system.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            $authUser = $request->user();
            $isSuperAdmin = $authUser->role === 'super_admin';
            try { $isSuperAdmin = $isSuperAdmin || $authUser->hasRole('super_admin'); } catch (\Exception $e) {}

            if (!$isSuperAdmin) {
                try {
                    if (!$authUser->hasPermissionTo('delete users')) {
                        return response()->json(['success' => false, 'message' => 'Unauthorized. Insufficient permissions.'], 403);
                    }
                } catch (\Exception $e) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized. Permission not configured.'], 403);
                }
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
            if ($user->id === $request->user()->id) {
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

    /**
     * Get all users (Admin only).
     *
     * Returns a paginated list of all users with their roles and permissions.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $authUser = $request->user();

            // Allow super_admin by role column OR Spatie role, else check permission
            $isSuperAdmin = $authUser->role === 'super_admin';
            try { $isSuperAdmin = $isSuperAdmin || $authUser->hasRole('super_admin'); } catch (\Exception $e) {}

            if (!$isSuperAdmin) {
                try {
                    if (!$authUser->hasPermissionTo('view users')) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Unauthorized. Insufficient permissions.',
                        ], 403);
                    }
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized. Permission not configured.',
                    ], 403);
                }
            }

            $perPage = $request->get('per_page', 50);
            $users = User::with(['roles', 'permissions'])->paginate($perPage);

            // Transform the users data
            $users->getCollection()->transform(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')->toArray(),
                    'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Users retrieved successfully',
                'data' => [
                    'users' => $users,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Get a specific user (Admin only).
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        try {
            $authUser = $request->user();
            $isSuperAdmin = $authUser->role === 'super_admin';
            try { $isSuperAdmin = $isSuperAdmin || $authUser->hasRole('super_admin'); } catch (\Exception $e) {}

            if (!$isSuperAdmin) {
                try {
                    if (!$authUser->hasPermissionTo('view users')) {
                        return response()->json(['success' => false, 'message' => 'Unauthorized. Insufficient permissions.'], 403);
                    }
                } catch (\Exception $e) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized. Permission not configured.'], 403);
                }
            }

            $user = User::with(['roles', 'permissions'])->find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'User retrieved successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'roles' => $user->roles->pluck('name')->toArray(),
                        'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Assign roles to a user (Admin only).
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignRoles(Request $request, $id)
    {
        try {
            // Check if user has permission to assign roles
            if (!$request->user()->hasPermissionTo('assign roles')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Insufficient permissions.',
                ], 403);
            }

            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            $validated = $request->validate([
                'roles' => ['required', 'array'],
                'roles.*' => ['string', 'exists:roles,name'],
            ]);

            $user->syncRoles($validated['roles']);

            return response()->json([
                'success' => true,
                'message' => 'Roles assigned successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'roles' => $user->roles->pluck('name')->toArray(),
                        'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
                    ],
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign roles.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Remove roles from a user (Admin only).
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeRoles(Request $request, $id)
    {
        try {
            // Check if user has permission to assign roles
            if (!$request->user()->hasPermissionTo('assign roles')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Insufficient permissions.',
                ], 403);
            }

            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            $validated = $request->validate([
                'roles' => ['required', 'array'],
                'roles.*' => ['string', 'exists:roles,name'],
            ]);

            $user->removeRole($validated['roles']);

            return response()->json([
                'success' => true,
                'message' => 'Roles removed successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'roles' => $user->roles->pluck('name')->toArray(),
                        'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
                    ],
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove roles.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }
}
