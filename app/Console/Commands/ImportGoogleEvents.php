<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\User;
use App\Support\GoogleTokenFile;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Spatie\GoogleCalendar\Event;

class ImportGoogleEvents extends Command
{
    protected $signature   = 'google:import-events';
    protected $description = 'Sync des events Google Agenda → table appointments';

    public function handle(): int
    {
        // plage d’un an
        $from = Carbon::now();
        $to   = $from->copy()->addYear();

        User::whereNotNull('google_access_token')
            ->chunkById(50, function ($users) use ($from, $to) {
                foreach ($users as $user) {
                    $this->syncForUser($user, $from, $to);
                }
            });

        return Command::SUCCESS;
    }

    private function syncForUser(User $user, Carbon $from, Carbon $to): void
    {
        // --- prépare Spatie (token jetable) -------------------------
        $tokenArr  = json_decode($user->google_access_token, true);
        $tokenPath = GoogleTokenFile::put($user->id, $tokenArr);

        config([
            'google-calendar.oauth_token'                    => $tokenArr,
            'google-calendar.auth_profiles.oauth.token_json' => $tokenPath,
        ]);

        // --- récupère les events ------------------------------------
        $events = Event::get($from, $to, ['singleEvents' => true]);

        foreach ($events as $ev) {
            // a) nos propres créneaux ? → skip
            if (str_contains($ev->description ?? '', '[AromaMade]')) continue;

            // b) déjà importé ? → skip
            if (Appointment::where('google_event_id', $ev->id)->exists()) continue;

            // c) créer le créneau "Occupé"
            Appointment::create([
                'user_id'          => $user->id,
                'client_profile_id'=> null,
                'appointment_date' => $ev->startDateTime ?? $ev->startDate,
                'duration'         => $this->durationMinutes($ev),
                'status'           => 'busy',
                'notes'            => $ev->summary,
                'google_event_id'  => $ev->id,
                'external'         => true,
                'type'             => 'external',
                // token public pour consultation :
                'token'            => Str::random(64),
            ]);
        }

        GoogleTokenFile::forget($user->id);
    }

    private function durationMinutes(Event $ev): int
    {
        $start = $ev->startDateTime ?? $ev->startDate;
        $end   = $ev->endDateTime   ?? $ev->endDate;   // all-day => 00:00 → 00:00
        return Carbon::parse($start)->diffInMinutes(Carbon::parse($end));
    }
}
