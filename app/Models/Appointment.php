<?php

namespace App\Models;

use App\Support\GoogleTokenFile;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\GoogleCalendar\Event as GoogleEvent;

class Appointment extends Model
{
    use HasFactory;

    /* ------------------------------------------------------------------ */
    /*  Attributs                                                         */
    /* ------------------------------------------------------------------ */

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

    protected $casts = [
        'appointment_date' => 'datetime',
    ];

    /* ------------------------------------------------------------------ */
    /*  Boot : token public + observers Google                            */
    /* ------------------------------------------------------------------ */

    protected static function boot()
    {
        parent::boot();

        // Token public (lien patient)
        static::creating(fn ($appt) => $appt->token = Str::random(64));
    }

    protected static function booted()
    {
        static::created(fn ($appt) => $appt->syncToGoogle());
        static::updated(fn ($appt) => $appt->syncToGoogle());
        static::deleted(fn ($appt) => $appt->removeFromGoogle());
    }

    /* ------------------------------------------------------------------ */
    /*  Synchronisation Google Calendar                                   */
    /* ------------------------------------------------------------------ */

 public function syncToGoogle(): void
{
    $therapist = $this->user;
    if (!$therapist || !$therapist->google_access_token) return;

    /* ---------- Préparation token / fichier pour Spatie ----------- */
    $tokenArr  = json_decode($therapist->google_access_token, true);
    $tokenPath = GoogleTokenFile::put($therapist->id, $tokenArr);

    config([
        'google-calendar.oauth_token'                        => $tokenArr,
        'google-calendar.auth_profiles.oauth.token_json'     => $tokenPath,
    ]);

    /* ---------------------- Données métier ------------------------ */
    $product   = $this->product?->name ?? 'Prestation';
    $client    = $this->clientProfile->first_name.' '.$this->clientProfile->last_name;

    // Mode de consultation
    $mode = $this->product?->getConsultationModes()[0] ?? 'Non spécifié';

    // Lieu
    $location = match ($mode) {
        'En Visio'       => 'Visio',
        'À Domicile'     => $this->clientProfile->address ?? 'Domicile client',
        'Dans le Cabinet'=> $therapist->company_address ?? 'Cabinet',
        default          => '',
    };

    // Titre & description enrichis
    $summary     = "Rdv $product – $client";
    $description = <<<TXT
Client : $client
Prestation : $product
Mode : $mode

Notes :
{$this->notes}
TXT;

    $eventData = [
        'name'          => $summary,
        'description'   => $description,
        'startDateTime' => Carbon::parse($this->appointment_date),
        'endDateTime'   => Carbon::parse($this->appointment_date)->addMinutes($this->duration),
        'location'      => $location,
    ];

    /* -------------------- Création / mise à jour ------------------ */
    if ($this->google_event_id) {
        // update
        $event = GoogleEvent::find($this->google_event_id);
        $event?->update($eventData);
    } else {
        // create
        $event = GoogleEvent::create($eventData);

        // Ajoute un lien Meet si visio
        if ($mode === 'En Visio') {
            $event->addMeetLink()->save();
        }

        $this->forceFill(['google_event_id' => $event->id])->saveQuietly();
    }

    GoogleTokenFile::forget($therapist->id);
}

    public function removeFromGoogle(): void
    {
        $therapist = $this->user;
        if (!$this->google_event_id || !$therapist?->google_access_token) {
            return;
        }

        $tokenArr  = json_decode($therapist->google_access_token, true);
        $tokenPath = GoogleTokenFile::put($therapist->id, $tokenArr);

        config([
            'google-calendar.oauth_token'                       => $tokenArr,
            'google-calendar.auth_profiles.oauth.token_json'    => $tokenPath,
        ]);

        try {
            GoogleEvent::find($this->google_event_id)?->delete();
        } finally {
            GoogleTokenFile::forget($therapist->id);
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Relations Eloquent                                                */
    /* ------------------------------------------------------------------ */

    public function product()       { return $this->belongsTo(Product::class); }
    public function user()          { return $this->belongsTo(User::class); }
    public function clientProfile() { return $this->belongsTo(ClientProfile::class); }
    public function meeting()       { return $this->hasOne(Meeting::class); }
    public function invoice()       { return $this->hasOne(Invoice::class); }
}
