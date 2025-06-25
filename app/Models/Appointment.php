<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Spatie\GoogleCalendar\Event as GoogleEvent;

class Appointment extends Model
{
    use HasFactory;

    /* --- Attributs remplissables ---------------------------------------- */
    protected $fillable = [
        'client_profile_id',
        'user_id',
        'appointment_date',
        'status',
        'notes',
        'type',
        'duration',
        'product_id',
        'stripe_session_id',
        'google_event_id',
    ];

    /* --- Casts ----------------------------------------------------------- */
    protected $casts = [
        'appointment_date' => 'datetime',
    ];

    /* --- Événement boot() : génération du token public ------------------ */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($appointment) {
            $appointment->token = Str::random(64);
        });
    }

    /* --- Observers pour la synchro Google ------------------------------- */
    protected static function booted()
    {
        static::created(fn ($appt) => $appt->syncToGoogle());
        static::updated(fn ($appt) => $appt->syncToGoogle());
        static::deleted(fn ($appt) => $appt->removeFromGoogle());
    }

    /* -------------------------------------------------------------------- */
    /*  Synchronisation Google Calendar                                    */
    /* -------------------------------------------------------------------- */

    public function syncToGoogle(): void
    {
        $therapist = $this->user;

        if (!$therapist || !$therapist->google_access_token) {
            return;                       // le thérapeute n’a pas connecté Google
        }

        // Injecte le token (json) directement dans la config Spatie
        config(['google-calendar.oauth_token' => json_decode($therapist->google_access_token, true)]);

        $eventData = [
            'name'          => 'RDV – ' . $this->clientProfile->first_name,
            'startDateTime' => Carbon::parse($this->appointment_date),
            'endDateTime'   => Carbon::parse($this->appointment_date)->addMinutes($this->duration),
            'description'   => $this->notes,
        ];

        if ($this->google_event_id) {
            // mise à jour
            GoogleEvent::find($this->google_event_id)?->update($eventData);
        } else {
            // création
            $event = GoogleEvent::create($eventData);
            $this->fill(['google_event_id' => $event->id])->saveQuietly();
        }
    }

    public function removeFromGoogle(): void
    {
        if (!$this->google_event_id || !$this->user?->google_access_token) {
            return;
        }

        config(['google-calendar.oauth_token' => json_decode($this->user->google_access_token, true)]);
        GoogleEvent::find($this->google_event_id)?->delete();
    }

    /* -------------------------------------------------------------------- */
    /*  Relations Eloquent                                                  */
    /* -------------------------------------------------------------------- */

    public function product()        { return $this->belongsTo(Product::class); }
    public function user()           { return $this->belongsTo(User::class); }
    public function clientProfile()  { return $this->belongsTo(ClientProfile::class); }
    public function meeting()        { return $this->hasOne(Meeting::class); }
    public function invoice()        { return $this->hasOne(Invoice::class); }
}
