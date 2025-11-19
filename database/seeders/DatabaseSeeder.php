<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Add any other seeders you have here…

        $this->call([
            TestTherapistSeeder::class,
        ]);
    }
}
