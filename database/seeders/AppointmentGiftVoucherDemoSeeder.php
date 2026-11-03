<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\GiftVoucher;
use App\Models\Product;
use App\Models\User;
use App\Services\AppointmentGiftVoucherService;
use Illuminate\Database\Seeder;

class AppointmentGiftVoucherDemoSeeder extends Seeder
{
    public function run(): void
    {
        // This seeder refuses any database except the dedicated local preview.
        $this->call(ReviewRequestDemoSeeder::class);
        $user = User::where('email', 'therapeute.avis@olithea.test')->sole();
        $product = Product::firstOrCreate(['user_id' => $user->id, 'name' => 'Séance bien-être · démo bon cadeau'], [
            'price' => 80, 'tax_rate' => 0, 'duration' => 60, 'dans_le_cabinet' => true,
            'calendar_color' => '#52796F', 'visible_in_portal' => false, 'can_be_booked_online' => false,
        ]);
        foreach ([
            ['Alice', 'FULL', 8000, true, 9],
            ['Lucie', 'PARTIAL', 5000, true, 11],
            ['Nora', 'TRY', 10000, false, 14],
            ['Emma', 'CHECK', 10000, false, 16],
        ] as [$firstName, $scenario, $balance, $apply, $hour]) {
            $client = $user->clientProfiles()->where('first_name', $firstName)->firstOrFail();
            $appointment = Appointment::withoutEvents(fn () => Appointment::firstOrCreate([
                'user_id' => $user->id, 'notes' => 'Démo locale bon cadeau · '.$scenario,
            ], [
                'client_profile_id' => $client->id, 'product_id' => $product->id,
                'appointment_date' => now()->addDay()->setTime($hour, 0), 'status' => 'confirmed',
                'type' => 'cabinet', 'duration' => 60, 'token' => \Illuminate\Support\Str::random(64),
            ]));
            $voucher = GiftVoucher::firstOrCreate(['user_id' => $user->id, 'code' => 'AM-DEMO-RDV-'.$scenario], [
                'original_amount_cents' => $balance, 'remaining_amount_cents' => $balance,
                'currency' => 'EUR', 'is_active' => true, 'source' => 'manual',
                'sale_channel' => 'offline_manual', 'sale_status' => 'paid', 'recipient_name' => $firstName.' Démo',
                'expires_at' => now()->addYear()->endOfDay(),
            ]);
            if ($apply && $appointment->giftVoucherRedemptions()->doesntExist()) {
                app(AppointmentGiftVoucherService::class)->apply($appointment, $voucher->code, min($balance, 8000), 8000);
            }
            $this->command?->line($scenario.' : '.route('appointments.show', $appointment).' · '.$voucher->code);
        }
    }
}
