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
    protected $description = 'Synchronise Google Agenda → appointments (create, delete)';

    public function handle(): int
    {
        // plage scrutée : aujourd’hui → +1 an
        $from = Carbon::now();
        $to   = $from->copy()->addYear();

        User::whereNotNull('google_access_token')
            ->chunkById(50, function ($users) use ($from, $to) {
                foreach ($users as $user) {
                    $this->syncForUser($user, $from, $to);
                }
            });

        return self::SUCCESS;
    }

    /* --------------------------------------------------------------------- */
    /*  Synchronise un seul thérapeute                                       */
    /* --------------------------------------------------------------------- */
    private function syncForUser(User $user, Carbon $from, Carbon $to): void
    {
        /* ---------- 1. Prépare Spatie (token jetable) ---------- */
        $tokenArr  = json_decode($user->google_access_token, true);
        $tokenPath = GoogleTokenFile::put($user->id, $tokenArr);

        config([
            'google-calendar.oauth_token'                    => $tokenArr,
            'google-calendar.auth_profiles.oauth.token_json' => $tokenPath,
        ]);

        /* ---------- 2. Récupère les évènements restants ---------- */
        /** @var \Illuminate\Support\Collection $events */
        $events = Event::get($from, $to, ['singleEvents' => true]);

        // Liste des IDs encore présents
        $stillThere = [];

        foreach ($events as $ev) {
            // a)  écarter nos propres slots poussés ([AromaMade])
            if (str_contains($ev->description ?? '', '[AromaMade]')) {
                $stillThere[] = $ev->id;          // on l’ignore mais on note l’ID
                continue;
            }

            // b) déjà importé ?
            $exists = Appointment::where('google_event_id', $ev->id)->first();
            if ($exists) {
                $stillThere[] = $ev->id;          // il existe toujours côté Google
                continue;                          // => rien à faire
            }

            // c) nouveau créneau « Occupé » --------------------
            Appointment::create([
                'user_id'           => $user->id,
                'client_profile_id' => null,
                'appointment_date'  => $this->parseDate($ev->startDateTime ?? $ev->startDate),
                'duration'          => $this->durationMinutes($ev),
                'status'            => 'busy',
                'notes'             => $ev->summary,
                'google_event_id'   => $ev->id,
                'external'          => true,
                'type'              => 'external',
                'token'             => Str::random(64),
            ]);

            $stillThere[] = $ev->id;              // ajouté à la liste présente
        }

        /* ---------- 3. Supprimer les créneaux disparus ---------- */
        Appointment::where('user_id', $user->id)
            ->where('external', true)
            ->whereBetween('appointment_date', [$from, $to])
            ->whereNotIn('google_event_id', $stillThere)
            ->each(fn ($appt) => $appt->delete());   // soft delete ou forceDelete()

        GoogleTokenFile::forget($user->id);
    }

    /* --------------------------------------------------------------------- */
    /*  Helpers                                                              */
    /* --------------------------------------------------------------------- */
    private function durationMinutes(Event $ev): int
    {
        // startDate / startDateTime sont déjà des Carbon (Spatie 3.x)
        $start = $this->parseDate($ev->startDateTime ?? $ev->startDate);
        $end   = $this->parseDate($ev->endDateTime   ?? $ev->endDate);

        return $start->diffInMinutes($end);
    }

    private function parseDate($googleDate): Carbon
    {
        // • $googleDate peut être un Carbon, DateTime ou string RFC3339
        // • On force dans le timezone applicatif (config/app.php - « timezone »)
        return Carbon::parse($googleDate)->setTimezone(config('app.timezone', 'Europe/Paris'));
    }
}
