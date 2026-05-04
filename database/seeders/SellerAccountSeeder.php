<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SellerAccountSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::updateOrCreate(
            ['email' => 'seller@mecarvi.com'],
            [
                'name' => 'Seller Account',
                'password' => Hash::make('Seller@123456'),
                'email_verified_at' => now(),
                'role' => 'seller',
            ]
        );

        if (! $seller->hasRole('seller')) {
            $seller->assignRole('seller');
        }

        $this->command->info('Seller account ready!');
        $this->command->line('Email: seller@mecarvi.com');
        $this->command->line('Password: Seller@123456');
    }
}
