<?php
// app/Console/Commands/ImportGoogleEvents.php

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
    protected $description = 'Synchronise les événements Google Agenda → table appointments';

    /** Fuseau cible (Europe/Paris par défaut) */
    private string $tz;

    public function __construct()
    {
        parent::__construct();
        $this->tz = config('app.timezone', 'Europe/Paris');
    }

    /* --------------------------------------------------------------------- */
    /* 1. Boucle principale                                                  */
    /* --------------------------------------------------------------------- */
    public function handle(): int
    {
        $from = Carbon::now();               // aujourd’hui
        $to   = $from->copy()->addYear();    // + 1 an

        User::whereNotNull('google_access_token')
            ->chunkById(50, function ($users) use ($from, $to) {
                foreach ($users as $user) {
                    $this->syncForUser($user, $from, $to);
                }
            });

        return Command::SUCCESS;
    }

    /* --------------------------------------------------------------------- */
    /* 2. Import pour un utilisateur                                         */
    /* --------------------------------------------------------------------- */
    private function syncForUser(User $user, Carbon $from, Carbon $to): void
    {
        /* --- 2 a) Prépare Spatie --------------------------------------- */
        $tokenArr  = json_decode($user->google_access_token, true);
        $tokenPath = GoogleTokenFile::put($user->id, $tokenArr);

        config([
            'google-calendar.oauth_token'                    => $tokenArr,
            'google-calendar.auth_profiles.oauth.token_json' => $tokenPath,
        ]);

        /* --- 2 b) Récupère les événements ----------------------------- */
        $events = Event::get($from, $to, ['singleEvents' => true]);

        foreach ($events as $ev) {

            /* Ignorer :
               – événements déjà poussés par AromaMade (tag)
               – événements déjà importés (google_event_id)           */
            if (
                str_contains($ev->description ?? '', '[AromaMade]') ||
                Appointment::where('google_event_id', $ev->id)->exists()
            ) {
                continue;
            }

            /* --- 2 c) Conversion fiable des dates ------------------- */
            $start = $this->toAppTz($ev->startDateTime ?? $ev->startDate);
            $end   = $this->toAppTz($ev->endDateTime   ?? $ev->endDate);

            Appointment::create([
                'user_id'          => $user->id,
                'client_profile_id'=> null,
                'appointment_date' => $start,
                'duration'         => $start->diffInMinutes($end),
                'status'           => 'busy',
                'notes'            => $ev->summary,           // titre de l’event
                'google_event_id'  => $ev->id,
                'external'         => true,
                'type'             => 'external',
                'token'            => Str::random(64),        // lien public neutre
            ]);
        }

        GoogleTokenFile::forget($user->id);                   // ménage
    }

    /* --------------------------------------------------------------------- */
    /* 3. Helper : force le fuseau voulu                                     */
    /* --------------------------------------------------------------------- */
    private function toAppTz($value): Carbon
    {
        // $value est déjà un Carbon via Spatie ; sinon on parse.
        $c = $value instanceof Carbon ? $value : Carbon::parse($value);
        return $c->copy()->setTimezone($this->tz);
    }
}
