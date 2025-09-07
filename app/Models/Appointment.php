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
    /*  Fields                                                            */
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
        'external',                // imported busy slot from Google
        'practice_location_id',    // ← SELECTED cabinet location (if cabinet)
        'address',                 // ← optional, for domicile override
        'token',                   // allow mass-assign only if you want; it is auto-set in creating()
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
        'external'         => 'boolean',
        'duration'         => 'integer',
    ];

    /* ------------------------------------------------------------------ */
    /*  Boot: public token + Google observers                             */
    /* ------------------------------------------------------------------ */
    protected static function boot()
    {
        parent::boot();

        // Generate public token (patient link)
        static::creating(function ($appt) {
            if (empty($appt->token)) {
                $appt->token = Str::random(64);
            }
        });
    }

    protected static function booted()
    {
        static::created(fn ($appt) => $appt->syncToGoogle());
        static::updated(fn ($appt) => $appt->syncToGoogle());
        static::deleted(fn ($appt) => $appt->removeFromGoogle());
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers: Determine mode & location                                */
    /* ------------------------------------------------------------------ */

    /**
     * Returns one of: 'cabinet' | 'visio' | 'domicile'
     */
    public function getResolvedMode(): string
    {
        if ($this->practice_location_id) {
            return 'cabinet';
        }

        // Fallback from product flags
        $product = $this->product;
        if ($product?->visio || $product?->en_visio) {
            return 'visio';
        }
        if ($product?->adomicile) {
            return 'domicile';
        }

        // Default
        return 'cabinet';
    }

    /**
     * Human label for mode.
     */
    public function getResolvedModeLabel(): string
    {
        return [
            'cabinet'  => __('Dans le Cabinet'),
            'visio'    => __('En Visio'),
            'domicile' => __('À Domicile'),
        ][$this->getResolvedMode()] ?? __('Non spécifié');
    }

    /**
     * Returns the best address string for Google event "location"
     * depending on the resolved mode.
     */
    public function getResolvedLocationString(): string
    {
        $mode = $this->getResolvedMode();

        if ($mode === 'visio') {
            // Usually Google Meet appended; keep a simple marker.
            return 'Visio';
        }

        if ($mode === 'domicile') {
            // Prefer explicit appointment.address (if saved), else client profile address.
            return $this->address
                ?: ($this->clientProfile?->address ?: 'Domicile client');
        }

        // mode === 'cabinet'
        if ($this->practiceLocation) {
            // Use accessor full_address from PracticeLocation
            // (getFullAddressAttribute in your model exposes "full_address")
            $pieces = array_filter([
                $this->practiceLocation->label,
                $this->practiceLocation->full_address,
            ]);
            return implode(' - ', $pieces);
        }

        // Fallback to therapist company address if no practice location on record
        return $this->user?->company_address ?: 'Cabinet';
    }

    /* ------------------------------------------------------------------ */
    /*  Google Calendar Sync                                              */
    /* ------------------------------------------------------------------ */
    public function syncToGoogle(): void
    {
        // 1) Skip if: imported busy slot OR therapist has no Google token
        if ($this->external) return;
        $therapist = $this->user;
        if (!$therapist?->google_access_token) return;

        // 2) Prepare Spatie (token via temp file)
        $tokenArr  = json_decode($therapist->google_access_token, true);
        $tokenPath = GoogleTokenFile::put($therapist->id, $tokenArr);

        config([
            'google-calendar.oauth_token'                     => $tokenArr,
            'google-calendar.auth_profiles.oauth.token_json'  => $tokenPath,
        ]);

        // 3) Business data
        $productName = optional($this->product)->name ?? 'Prestation';
        $clientName  = trim(
            optional($this->clientProfile)->first_name.' '.
            optional($this->clientProfile)->last_name
        );

        $mode        = $this->getResolvedMode();               // 'cabinet' | 'visio' | 'domicile'
        $location    = $this->getResolvedLocationString();

        // tag to prevent import loops
        $description = rtrim(($this->notes ?? '')."\n\n[AromaMade]");

        $eventData = [
            'name'          => $clientName ? "Rdv $productName – $clientName" : $productName,
            'description'   => $description,
            'startDateTime' => $this->appointment_date,
            'endDateTime'   => Carbon::parse($this->appointment_date)->addMinutes($this->duration ?? 60),
            'location'      => $location,
        ];

        // 4) Create / Update
        if ($this->google_event_id) {
            GoogleEvent::find($this->google_event_id)?->update($eventData);
        } else {
            $event = GoogleEvent::create($eventData);

            // Add Google Meet if visio
            if ($mode === 'visio') {
                $event->addMeetLink()->save();
            }

            // Save Google event id silently
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
    public function practiceLocation()
    {
        return $this->belongsTo(\App\Models\PracticeLocation::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class); // therapist
    }

    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function meeting()
    {
        return $this->hasOne(Meeting::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
