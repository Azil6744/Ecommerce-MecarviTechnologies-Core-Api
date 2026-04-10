<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed default roles and permissions.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions grouped by category
        $permissions = [
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Role & Permission Management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'assign roles',
            'view permissions',
            'create permissions',

            // Product Management
            'view products',
            'create products',
            'edit products',
            'delete products',

            // Order Management
            'view orders',
            'create orders',
            'edit orders',
            'delete orders',
            'process orders',

            // Category Management
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',

            // Content Management
            'view content',
            'create content',
            'edit content',
            'delete content',

            // Settings
            'view settings',
            'edit settings',

            // Reports
            'view reports',
            'export reports',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        // Super Admin — gets ALL permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());

        // Admin — gets most permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions([
            'view users', 'create users', 'edit users',
            'view roles', 'assign roles',
            'view permissions',
            'view products', 'create products', 'edit products', 'delete products',
            'view orders', 'create orders', 'edit orders', 'process orders',
            'view categories', 'create categories', 'edit categories', 'delete categories',
            'view content', 'create content', 'edit content', 'delete content',
            'view settings', 'edit settings',
            'view reports', 'export reports',
        ]);

        // Editor — content and product management
        $editorRole = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $editorRole->syncPermissions([
            'view products', 'create products', 'edit products',
            'view orders', 'edit orders',
            'view categories',
            'view content', 'create content', 'edit content',
            'view reports',
        ]);

        // Customer — limited view access
        $customerRole = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        $customerRole->syncPermissions([
            'view products',
            'view orders', 'create orders',
            'view categories',
        ]);

        // Assign super_admin role to the first user (if exists)
        $firstUser = \App\Models\User::first();
        if ($firstUser && !$firstUser->hasRole('super_admin')) {
            $firstUser->assignRole('super_admin');
        }

        $this->command->info('✓ Roles and permissions seeded successfully.');
        $this->command->info('  Permissions: ' . Permission::count());
        $this->command->info('  Roles: ' . Role::count());
    }
}
