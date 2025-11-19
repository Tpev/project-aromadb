<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestTherapistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'therapist@test.aromamade.local', // login used in Playwright
            ],
            [
                'name'           => 'Test Therapist',
                'password'       => Hash::make('password123'), // plain password for tests
                'is_therapist'   => true,
                'license_status' => 'active',                  // important for your redirects
                // Ajoute ici d'autres colonnes obligatoires si tu en as (ex: 'role' => 'therapist')
            ]
        );
    }
}
