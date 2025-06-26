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
    /*  Champs                                                            */
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
        'external',            // ← créneau issu d’un import Google
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
        'external'         => 'boolean',
    ];

    /* ------------------------------------------------------------------ */
    /*  Boot : token public + observers Google                            */
    /* ------------------------------------------------------------------ */
    protected static function boot()
    {
        parent::boot();

        // token public (lien patient)
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
        // 1. ne rien pousser si  ► créneau « Occupé » importé  OU  pas de token
        if ($this->external)                return;
        $therapist = $this->user;
        if (!$therapist?->google_access_token) return;

        // 2. prépare Spatie (token en fichier jetable)
        $tokenArr  = json_decode($therapist->google_access_token, true);
        $tokenPath = GoogleTokenFile::put($therapist->id, $tokenArr);

        config([
            'google-calendar.oauth_token'                     => $tokenArr,
            'google-calendar.auth_profiles.oauth.token_json'  => $tokenPath,
        ]);

        // 3. données métier
        $productName = optional($this->product)->name ?? 'Prestation';
        $clientName  = trim(
            optional($this->clientProfile)->first_name.' '.
            optional($this->clientProfile)->last_name
        );

        // mode de consultation (méthode du modèle Product)
        $mode = optional($this->product)
                  ->getConsultationModes()[0] ?? 'Non spécifié';

        $location = match($mode) {
            'En Visio'        => 'Visio',
            'À Domicile'      => optional($this->clientProfile)->address ?? 'Domicile client',
            'Dans le Cabinet' => $therapist->company_address ?? 'Cabinet',
            default           => '',
        };

        // tag pour éviter toute boucle lors de l’import
        $description = rtrim(($this->notes ?? '')."\n\n[AromaMade]");

        $eventData = [
            'name'          => $clientName ? "Rdv $productName – $clientName" : $productName,
            'description'   => $description,
            'startDateTime' => $this->appointment_date,
            'endDateTime'   => Carbon::parse($this->appointment_date)->addMinutes($this->duration ?? 60),
            'location'      => $location,
        ];

        // 4. create / update
        if ($this->google_event_id) {
            GoogleEvent::find($this->google_event_id)?->update($eventData);
        } else {
            $event = GoogleEvent::create($eventData);

            // ajoute Meet si visio
            if ($mode === 'En Visio') {
                $event->addMeetLink()->save();
            }

            // stocke l’id Google
            $this->forceFill(['google_event_id' => $event->id])->saveQuietly();
        }

        GoogleTokenFile::forget($therapist->id);
    }

    public function removeFromGoogle(): void
    {
        $therapist = $this->user;
        if ($this->external || !$this->google_event_id || !$therapist?->google_access_token) {
            return;
        }

        $tokenArr  = json_decode($therapist->google_access_token, true);
        $tokenPath = GoogleTokenFile::put($therapist->id, $tokenArr);

        config([
            'google-calendar.oauth_token'                    => $tokenArr,
            'google-calendar.auth_profiles.oauth.token_json' => $tokenPath,
        ]);

        try {
            GoogleEvent::find($this->google_event_id)?->delete();
        } finally {
            GoogleTokenFile::forget($therapist->id);
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Relations                                                         */
    /* ------------------------------------------------------------------ */
    public function product()       { return $this->belongsTo(Product::class); }
    public function user()          { return $this->belongsTo(User::class); }
    public function clientProfile() { return $this->belongsTo(ClientProfile::class); }
    public function meeting()       { return $this->hasOne(Meeting::class); }
    public function invoice()       { return $this->hasOne(Invoice::class); }
}
