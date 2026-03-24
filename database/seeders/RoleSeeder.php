<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Seed the roles data.
     * 
     * This seeder doesn't create a roles table, but ensures
     * that we have documentation of the three roles:
     * - super_admin: Full system access
     * - editor: Can create and edit content
     * - viewer: Read-only access
     */
    public function run(): void
    {
        // Roles are stored as enum in the users table
        // This seeder is mainly for documentation purposes
        // The actual roles are: super_admin, editor, viewer
    }
}
