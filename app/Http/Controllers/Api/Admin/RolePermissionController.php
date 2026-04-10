<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    /**
     * Check if user is super admin (by column or Spatie role).
     */
    private function isSuperAdmin($user): bool
    {
        // Check role column first
        if ($user->role === 'super_admin') {
            return true;
        }
        // Try Spatie hasRole — may fail if tables/config don't exist
        try {
            return $user->hasRole('super_admin');
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check permission with super_admin bypass and graceful fallback.
     */
    private function checkPermission(Request $request, string $permission): mixed
    {
        $user = $request->user();
        if ($this->isSuperAdmin($user)) {
            return null; // allowed
        }
        try {
            if (!$user->hasPermissionTo($permission)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized. Insufficient permissions.'], 403);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Permission not configured.'], 403);
        }
        return null;
    }

    // ─── ROLES ───────────────────────────────────────────────

    /**
     * List all roles with their permissions and user counts.
     */
    public function indexRoles(Request $request)
    {
        try {
            $denied = $this->checkPermission($request, 'view roles');
            if ($denied) return $denied;

            $roles = Role::with('permissions')->get()->map(function ($role) {
                // Manual user count to avoid guard/morph class resolution issues
                $usersCount = \DB::table('model_has_roles')
                    ->where('role_id', $role->id)
                    ->count();

                return [
                    'id'               => $role->id,
                    'name'             => $role->name,
                    'guard_name'       => $role->guard_name,
                    'permissions'      => $role->permissions->pluck('name')->toArray(),
                    'permissions_count' => $role->permissions->count(),
                    'users_count'      => $usersCount,
                    'created_at'       => $role->created_at,
                    'updated_at'       => $role->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Roles retrieved successfully',
                'data'    => ['roles' => $roles],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve roles.',
                'error'   => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Create a new role with optional permissions.
     */
    public function storeRole(Request $request)
    {
        try {
            $denied = $this->checkPermission($request, 'create roles');
            if ($denied) return $denied;

            $validated = $request->validate([
                'name'          => ['required', 'string', 'max:255', 'unique:roles,name'],
                'permissions'   => ['sometimes', 'array'],
                'permissions.*' => ['string', 'exists:permissions,name'],
            ]);

            $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);

            if (!empty($validated['permissions'])) {
                $role->syncPermissions($validated['permissions']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully',
                'data'    => [
                    'role' => [
                        'id'          => $role->id,
                        'name'        => $role->name,
                        'permissions' => $role->permissions->pluck('name')->toArray(),
                    ],
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Role creation failed.',
                'error'   => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Update a role's name and/or permissions.
     */
    public function updateRole(Request $request, $id)
    {
        try {
            $denied = $this->checkPermission($request, 'edit roles');
            if ($denied) return $denied;

            $role = Role::find($id);
            if (!$role) {
                return response()->json(['success' => false, 'message' => 'Role not found.'], 404);
            }

            $validated = $request->validate([
                'name'          => ['sometimes', 'required', 'string', 'max:255', 'unique:roles,name,' . $id],
                'permissions'   => ['sometimes', 'array'],
                'permissions.*' => ['string', 'exists:permissions,name'],
            ]);

            if (isset($validated['name'])) {
                $role->name = $validated['name'];
                $role->save();
            }

            if (isset($validated['permissions'])) {
                $role->syncPermissions($validated['permissions']);
            }

            $role->load('permissions');

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
                'data'    => [
                    'role' => [
                        'id'          => $role->id,
                        'name'        => $role->name,
                        'permissions' => $role->permissions->pluck('name')->toArray(),
                    ],
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Role update failed.',
                'error'   => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Delete a role (protect built-in roles).
     */
    public function destroyRole(Request $request, $id)
    {
        try {
            $denied = $this->checkPermission($request, 'delete roles');
            if ($denied) return $denied;

            $role = Role::find($id);
            if (!$role) {
                return response()->json(['success' => false, 'message' => 'Role not found.'], 404);
            }

            // Protect built-in roles
            $protected = ['super_admin', 'admin', 'editor', 'customer'];
            if (in_array($role->name, $protected)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete built-in role "' . $role->name . '".',
                ], 403);
            }

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Role deletion failed.',
                'error'   => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    // ─── PERMISSIONS ─────────────────────────────────────────

    /**
     * List all permissions, grouped by category.
     */
    public function indexPermissions(Request $request)
    {
        try {
            $denied = $this->checkPermission($request, 'view permissions');
            if ($denied) return $denied;

            $permissions = Permission::all();

            // Group permissions by their prefix (e.g. "view users" → "users")
            $grouped = [];
            foreach ($permissions as $perm) {
                $parts = explode(' ', $perm->name, 2);
                $group = count($parts) > 1 ? $parts[1] : 'general';
                $grouped[$group][] = [
                    'id'   => $perm->id,
                    'name' => $perm->name,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Permissions retrieved successfully',
                'data'    => [
                    'permissions' => $permissions->pluck('name')->toArray(),
                    'grouped'     => $grouped,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve permissions.',
                'error'   => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Create a new permission.
     */
    public function storePermission(Request $request)
    {
        try {
            $denied = $this->checkPermission($request, 'create permissions');
            if ($denied) return $denied;

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
            ]);

            $permission = Permission::create(['name' => $validated['name'], 'guard_name' => 'web']);

            return response()->json([
                'success' => true,
                'message' => 'Permission created successfully',
                'data'    => [
                    'permission' => [
                        'id'   => $permission->id,
                        'name' => $permission->name,
                    ],
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Permission creation failed.',
                'error'   => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }
}
