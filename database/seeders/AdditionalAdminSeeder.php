<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdditionalAdminSeeder extends Seeder
{
    public function run(): void
    {
        $additionalEmail = env('SEED_ADDITIONAL_ADMIN_EMAIL', 'editor@mecarvi.com');
        $additionalPassword = env('SEED_ADDITIONAL_ADMIN_PASSWORD', 'McCarvyEditor2026!');

        $admin = User::updateOrCreate([
            'email' => $additionalEmail,
        ], [
            'name' => 'Administrator',
            'password' => Hash::make($additionalPassword),
            'email_verified_at' => now(),
            'role' => 'super_admin',
        ]);

        // Assign super_admin role
        $admin->assignRole('super_admin');

        $this->command->info('New admin user created successfully!');
        $this->command->warn('Credentials:');
        $this->command->line('Email: admin@mecarvi.com');
        $this->command->line('Password: Admin@123456');
    }
}
