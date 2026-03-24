<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles (for documentation/reference)
        $this->call([
            RoleSeeder::class,
        ]);

        // Seed admin user
        $this->call([
            AdminUserSeeder::class,
        ]);

        // Uncomment to create additional test users
        // \App\Models\User::factory(10)->create();
    }
}
