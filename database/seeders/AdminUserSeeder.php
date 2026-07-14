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
        $adminEmail = env('SEED_ADMIN_EMAIL', 'admin@mecarvi.com');
        $adminPassword = env('SEED_ADMIN_PASSWORD', 'McCarvyAdmin2026!');

        $adminUser = User::updateOrCreate([
            'email' => $adminEmail,
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make($adminPassword),
            'email_verified_at' => now(),
            'role' => 'super_admin',
        ]);

        $adminUser->assignRole('super_admin');

        $this->command->info('Admin user created successfully!');
        $this->command->warn('Default credentials:');
        $this->command->line('Email: admin@gmail.com');
        $this->command->line('Password: 123456');
        $this->command->warn('Please change the password immediately after first login!');
    }
}
