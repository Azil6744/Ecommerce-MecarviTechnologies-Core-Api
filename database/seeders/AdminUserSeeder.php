<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the admin user.
     * 
     * Creates a default super admin user for initial access to the system.
     * 
     * Default credentials:
     * Email: admin@gmail.com
     * Password: 123456 (should be changed immediately)
     */
    public function run(): void
    {
        // Check if admin user already exists
        if (User::where('email', 'admin@gmail.com')->exists()) {
            $this->command->info('Admin user already exists. Skipping...');
            return;
        }

        // Create super admin user
        $adminUser = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456'),
            'email_verified_at' => now(),
        ]);

        // Assign super_admin role
        $adminUser->assignRole('super_admin');

        $this->command->info('Admin user created successfully!');
        $this->command->warn('Default credentials:');
        $this->command->line('Email: admin@gmail.com');
        $this->command->line('Password: 123456');
        $this->command->warn('Please change the password immediately after first login!');
    }
}
