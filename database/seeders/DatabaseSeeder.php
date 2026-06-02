<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Administrateur',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        User::factory()->create([
            'name' => 'Pharmacien',
            'email' => 'pharmacien@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PHARMACIEN,
        ]);

        User::factory()->create([
            'name' => 'Caissier',
            'email' => 'caissier@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CAISSIER,
        ]);

        // Appeler le seeder de données de démonstration
        $this->call(DemoDataSeeder::class);
    }
}
