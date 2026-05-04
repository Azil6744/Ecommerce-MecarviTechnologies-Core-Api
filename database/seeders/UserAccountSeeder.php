<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserAccountSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'user@mecarvi.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('User@123456'),
                'email_verified_at' => now(),
                'role' => 'customer',
            ]
        );

        if (! $user->hasRole('customer')) {
            $user->assignRole('customer');
        }

        $this->command->info('User account ready!');
        $this->command->line('Email: user@mecarvi.com');
        $this->command->line('Password: User@123456');
    }
}
