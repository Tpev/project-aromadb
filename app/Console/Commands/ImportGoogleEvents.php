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
    protected $description = 'Synchronise Google Agenda → appointments (create, update, delete)';

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
            // Toujours noter l'ID comme "présent côté Google"
            $stillThere[] = $ev->id;

            // a) écarter nos propres slots poussés ([AromaMade])
            if (str_contains($ev->description ?? '', '[AromaMade]')) {
                continue;
            }

            $newStart    = $this->parseDate($ev->startDateTime ?? $ev->startDate);
            $newDuration = $this->durationMinutes($ev);
            $newNotes    = $ev->summary ?? null;

            // b) déjà importé ? => UPDATE si nécessaire
            $exists = Appointment::query()
                ->where('user_id', $user->id)
                ->where('google_event_id', $ev->id)
                ->where('external', true)
                ->first();

            if ($exists) {
                // Compare proprement (minute-level, car DB peut tronquer les secondes)
                $currentStart = $exists->appointment_date
                    ? Carbon::parse($exists->appointment_date)->setTimezone(config('app.timezone', 'Europe/Paris'))
                    : null;

                $startChanged = !$currentStart || $currentStart->format('Y-m-d H:i') !== $newStart->format('Y-m-d H:i');
                $durationChanged = (int) $exists->duration !== (int) $newDuration;
                $notesChanged = (string) ($exists->notes ?? '') !== (string) ($newNotes ?? '');

                if ($startChanged || $durationChanged || $notesChanged) {
                    $exists->appointment_date = $newStart;
                    $exists->duration         = $newDuration;
                    $exists->notes            = $newNotes;
                    $exists->status           = 'busy';    // on garde le blocage
                    $exists->type             = 'external';
                    $exists->external         = true;

                    $exists->save();
                }

                continue;
            }

            // c) nouveau créneau « Occupé » => CREATE
            Appointment::create([
                'user_id'           => $user->id,
                'client_profile_id' => null,
                'appointment_date'  => $newStart,
                'duration'          => $newDuration,
                'status'            => 'busy',
                'notes'             => $newNotes,
                'google_event_id'   => $ev->id,
                'external'          => true,
                'type'              => 'external',
                'token'             => Str::random(64),
            ]);
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
        $start = $this->parseDate($ev->startDateTime ?? $ev->startDate);
        $end   = $this->parseDate($ev->endDateTime   ?? $ev->endDate);

        return $start->diffInMinutes($end);
    }

    private function parseDate($googleDate): Carbon
    {
        return Carbon::parse($googleDate)->setTimezone(config('app.timezone', 'Europe/Paris'));
    }
}
