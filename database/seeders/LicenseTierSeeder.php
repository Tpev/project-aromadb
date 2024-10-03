<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LicenseTierSeeder extends Seeder
{
    public function run()
    {


        // Seed the license tiers
        DB::table('license_tiers')->insert([
            [
                'name' => 'Trial',
                'duration_days' => 15,
                'is_trial' => true,
                'trial_duration_days' => 15,
                'price' => 0.00,
                'features' => json_encode([
                    'access' => 'Limited access',
                    'support' => 'No support',
                    'users' => 1
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Starter-mensuelle',
                'duration_days' => 30,
                'is_trial' => false,
                'trial_duration_days' => null,
                'price' => 9.90,
                'features' => json_encode([
                    'access' => 'Full access',
                    'support' => 'Basic support',
                    'users' => 1
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Starter-annuelle',
                'duration_days' => 365,
                'is_trial' => false,
                'trial_duration_days' => null,
                'price' => 108.90,
                'features' => json_encode([
                    'access' => 'Full access',
                    'support' => 'Basic support',
                    'users' => 1
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Pro-mensuelle',
                'duration_days' => 30,
                'is_trial' => false,
                'trial_duration_days' => null,
                'price' => 29.90,
                'features' => json_encode([
                    'access' => 'Full access',
                    'support' => 'Priority support',
                    'users' => 5
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Pro-annuelle',
                'duration_days' => 365,
                'is_trial' => false,
                'trial_duration_days' => null,
                'price' => 328.90,
                'features' => json_encode([
                    'access' => 'Full access',
                    'support' => 'Priority support',
                    'users' => 5
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
