<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventInvoicingDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Verifies the dedicated local SQLite database and log-only mailer before any writes.
        $this->call(ReviewRequestDemoSeeder::class);
        $therapist = User::where('email', 'therapeute.avis@olithea.test')->sole();
        $event = Event::firstOrCreate([
            'user_id' => $therapist->id, 'name' => 'Atelier respiration · démo facturation',
        ], [
            'description' => 'Événement fictif pour vérifier la facturation individuelle des participants.',
            'start_date_time' => now()->addDays(7)->setTime(10, 0), 'duration' => 90,
            'booking_required' => true, 'limited_spot' => true, 'number_of_spot' => 12,
            'showOnPortail' => false, 'location' => 'Cabinet Démo · salle des ateliers',
            'event_type' => 'in_person', 'price' => 50, 'tax_rate' => 20, 'collect_payment' => false,
        ]);
        foreach ([
            ['Alice Martin', 'alice.demo@example.test', 'confirmed'],
            ['Lucie Bernard', 'lucie.demo@example.test', 'paid'],
            ['Jules Moreau', 'jules.event.demo@example.test', 'confirmed'],
            ['Nora Petit', 'nora.demo@example.test', 'pending_payment'],
        ] as [$name, $email, $status]) {
            $event->reservations()->firstOrCreate(['email' => $email], [
                'full_name' => $name, 'status' => $status,
                'amount_ttc' => $status === 'paid' ? 60 : null, 'currency' => 'eur',
                'stripe_payment_intent_id' => $status === 'paid' ? 'pi_local_demo_event_'.$event->id : null,
                'payment_confirmation_requested_at' => $status === 'paid' ? now()->subDay() : null,
            ]);
        }
        $this->command?->line('Facturation par participant : '.route('events.show', $event));
    }
}
