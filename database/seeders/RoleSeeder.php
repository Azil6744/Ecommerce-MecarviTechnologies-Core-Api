<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Seed the roles and permissions data using Spatie Laravel Permission.
     *
     * Roles:
     * - super_admin: Full system access, can manage users, roles, and permissions
     * - admin: Administrative access, can manage content and orders
    * - editor: Can create and edit content
    * - customer: Regular customer with basic access
    * - seller: Vendor account with product management access
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',
            'assign roles',

            // Role management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',

            // Permission management
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',

            // Product management
            'view products',
            'create products',
            'edit products',
            'delete products',

            // Order management
            'view orders',
            'create orders',
            'edit orders',
            'delete orders',
            'process payments',

            // Content management
            'view content',
            'create content',
            'edit content',
            'delete content',

            // System settings
            'view settings',
            'edit settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdminRole->syncPermissions($permissions); // Super admin gets all permissions

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions([
            'view users', 'create users', 'edit users',
            'view roles', 'view permissions',
            'view products', 'create products', 'edit products', 'delete products',
            'view orders', 'edit orders', 'process payments',
            'view content', 'create content', 'edit content', 'delete content',
            'view settings', 'edit settings',
        ]);

        $editorRole = Role::firstOrCreate(['name' => 'editor']);
        $editorRole->syncPermissions([
            'view products', 'create products', 'edit products',
            'view content', 'create content', 'edit content',
        ]);

        $customerRole = Role::firstOrCreate(['name' => 'customer']);
        $customerRole->syncPermissions([
            'view products',
            'create orders',
            'view orders',
        ]);

        $sellerRole = Role::firstOrCreate(['name' => 'seller']);
        $sellerRole->syncPermissions([
            'view products',
            'create products',
            'edit products',
            'view orders',
            'edit orders',
        ]);

        $staffRole = Role::firstOrCreate(['name' => 'staff']);
        $staffRole->syncPermissions([
            'view products',
            'view orders',
            'edit orders',
            'view content',
        ]);

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
