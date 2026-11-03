<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\Product;
use App\Models\Unavailability;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CalendarColorDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Also enforces local environment, dedicated SQLite database and log-only email.
        $this->call(ReviewRequestDemoSeeder::class);
        $therapist = User::where('email', 'therapeute.avis@olithea.test')->sole();
        $products = collect([
            ['Consultation initiale', '#2563EB', 60, 75],
            ['Suivi individuel', '#0F766E', 45, 55],
            ['Massage détente', '#7C3AED', 60, 70],
            ['Séance de sophrologie', '#C2410C', 60, 65],
            ['Bilan express', '#FDE68A', 30, 35],
            ['Séance sans couleur', null, 45, 50],
        ])->map(fn (array $item) => Product::firstOrCreate([
            'user_id' => $therapist->id,
            'name' => $item[0],
        ], [
            'calendar_color' => $item[1],
            'duration' => $item[2],
            'price' => $item[3],
            'tax_rate' => 0,
            'description' => 'Prestation fictive pour tester les couleurs dans l’agenda.',
            'dans_le_cabinet' => true,
            'can_be_booked_online' => false,
            'collect_payment' => false,
            'visible_in_portal' => false,
            'price_visible_in_portal' => false,
        ]));
        $clients = ClientProfile::where('user_id', $therapist->id)->orderBy('id')->get();
        $week = CarbonImmutable::now()->startOfWeek();
        $slots = [
            [0, '09:00', 0], [0, '10:30', 1], [0, '14:00', 2], [0, '15:30', 5],
            [1, '09:00', 3], [1, '10:30', 4], [1, '14:00', 0], [1, '15:30', 1],
            [2, '09:00', 2], [2, '11:00', 1], [2, '14:00', 3], [2, '15:30', 4],
            [3, '09:00', 0], [3, '10:30', 2], [3, '14:00', 1], [3, '15:30', 5],
            [4, '09:00', 3], [4, '10:30', 0], [4, '14:00', 2], [4, '15:30', 4],
        ];

        // Demo appointments must never run calendar-sync hooks or automations.
        Appointment::withoutEvents(function () use ($week, $slots, $products, $clients, $therapist) {
            foreach ([0, 1] as $weekOffset) {
                $start = $week->addWeeks($weekOffset);
                foreach ($slots as $index => [$day, $time, $productIndex]) {
                    $date = $start->addDays($day)->setTimeFromTimeString($time);
                    $product = $products[$productIndex];
                    Appointment::firstOrCreate([
                        'user_id' => $therapist->id,
                        'notes' => 'Démo couleurs · '.$date->format('Y-m-d H:i'),
                    ], [
                        'client_profile_id' => $clients[$index % $clients->count()]->id,
                        'product_id' => $product->id,
                        'appointment_date' => $date,
                        'duration' => $product->duration,
                        'status' => $date->isPast() ? 'completed' : 'confirmed',
                        'type' => 'cabinet',
                        'token' => Str::random(64),
                        'requires_emargement' => false,
                        'emargement_sent' => false,
                    ]);
                }

                Appointment::firstOrCreate([
                    'user_id' => $therapist->id,
                    'notes' => 'Occupé · Google (démo '.$start->format('d/m').')',
                ], [
                    'appointment_date' => $start->addDays(1)->setTime(12, 0),
                    'duration' => 60,
                    'external' => true,
                    'status' => 'confirmed',
                    'token' => Str::random(64),
                    'requires_emargement' => false,
                    'emargement_sent' => false,
                ]);

                Appointment::firstOrCreate([
                    'user_id' => $therapist->id,
                    'notes' => 'Annulé · Démo couleurs '.$start->format('Y-m-d'),
                ], [
                    'client_profile_id' => $clients[0]->id,
                    'product_id' => $products[0]->id,
                    'appointment_date' => $start->addDays(3)->setTime(12, 0),
                    'duration' => 60,
                    'status' => 'cancelled',
                    'type' => 'cabinet',
                    'token' => Str::random(64),
                    'requires_emargement' => false,
                    'emargement_sent' => false,
                ]);

                Unavailability::firstOrCreate([
                    'user_id' => $therapist->id,
                    'reason' => 'Pause · Démo couleurs '.$start->format('d/m'),
                ], [
                    'start_date' => $start->addDays(2)->setTime(12, 0),
                    'end_date' => $start->addDays(2)->setTime(13, 30),
                ]);
            }
        });

        $this->command?->info('Agenda démo : '.$week->format('d/m/Y').' et semaine suivante. Cliquez sur Semaine.');
        $this->command?->line(route('appointments.index'));
        foreach ($products as $product) {
            $this->command?->line($product->name.' : '.route('products.edit', $product));
        }
    }
}
