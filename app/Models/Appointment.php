<?php

namespace App\Models;

use App\Support\GoogleTokenFile;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\GoogleCalendar\Event as GoogleEvent;

class Appointment extends Model
{
    use HasFactory;

    public const CANCELLED_STATUSES = [
        'cancelled',
        'canceled',
        'Annulée',
        'Annulee',
    ];

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
        'gift_voucher_id',
        'gift_voucher_amount_cents',
        'google_event_id',
        'external',                // imported busy slot from Google
        'practice_location_id',    // ← SELECTED cabinet location (if cabinet)
        'address',                 // ← optional, for domicile override
        'token',                   // allow mass-assign only if you want; it is auto-set in creating()

        // NEW
        'requires_emargement',
        'emargement_sent',
    ];

    protected $casts = [
        'appointment_date'     => 'datetime',
        'external'             => 'boolean',
        'duration'             => 'integer',
        'gift_voucher_amount_cents' => 'integer',

        // NEW
        'requires_emargement'  => 'boolean',
        'emargement_sent'      => 'boolean',
    ];

    public function scopeNotCancelled($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('status')
                ->orWhereNotIn('status', self::CANCELLED_STATUSES);
        });
    }

    public function isCancelled(): bool
    {
        return in_array($this->status, self::CANCELLED_STATUSES, true);
    }

    /* ------------------------------------------------------------------ */
    /*  Boot: public token + Google observers + Emargement init           */
    /* ------------------------------------------------------------------ */
    protected static function boot()
    {
        parent::boot();

        // Generate public token (patient link)
        static::creating(function ($appt) {
            if (empty($appt->token)) {
                $appt->token = Str::random(64);
            }

            // Initialize emargement flags based on the linked product.
            // If product_id exists and the product requires emargement,
            // mark the appointment accordingly.
            if (is_null($appt->requires_emargement)) {
                $product = $appt->relationLoaded('product')
                    ? $appt->product
                    : ($appt->product_id ? \App\Models\Product::find($appt->product_id) : null);

                $appt->requires_emargement = (bool) optional($product)->requires_emargement;
            }

            // Never sent by default
            if (is_null($appt->emargement_sent)) {
                $appt->emargement_sent = false;
            }
        });
    }

    protected static function booted()
    {
        // External calendar sync must never block appointment CRUD.
        static::created(fn ($appt) => $appt->syncToGoogleSafely('created'));
        static::updated(fn ($appt) => $appt->syncToGoogleSafely('updated'));
        static::deleted(fn ($appt) => $appt->removeFromGoogleSafely());
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers: Determine mode & location                                */
    /* ------------------------------------------------------------------ */

    /**
     * Returns one of: 'cabinet' | 'visio' | 'domicile' | 'entreprise'
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
        if (!empty($product?->en_entreprise)) {
            return 'entreprise';
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
            'domicile'   => __('À Domicile'),
            'entreprise' => __('En entreprise'),
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
            return 'Visio';
        }

        if ($mode === 'domicile' || $mode === 'entreprise') {
            return $this->address
                ?: ($this->clientProfile?->address ?: 'Domicile client');
        }

        if ($this->practiceLocation) {
            $pieces = array_filter([
                $this->practiceLocation->label,
                $this->practiceLocation->full_address,
            ]);
            return implode(' - ', $pieces);
        }

        return $this->user?->company_address ?: 'Cabinet';
    }

    /* ------------------------------------------------------------------ */
    /*  Google Calendar Sync                                              */
    /* ------------------------------------------------------------------ */
public function syncToGoogle(): void
{
    if ($this->external) return;

    $therapist = $this->user;
    if (!$therapist?->google_access_token) return;

    $tokenArr  = json_decode($therapist->google_access_token, true);
    $tokenPath = \App\Support\GoogleTokenFile::put($therapist->id, $tokenArr);

    config([
        'google-calendar.oauth_token'                    => $tokenArr,
        'google-calendar.auth_profiles.oauth.token_json' => $tokenPath,
    ]);

    try {
        // Default Google "blue" if user didn't pick a color
        $colorId = $therapist->google_event_color_id ?: '9';

        $productName = optional($this->product)->name ?? 'Prestation';
        $clientName  = trim(
            optional($this->clientProfile)->first_name . ' ' .
            optional($this->clientProfile)->last_name
        );

        $mode        = $this->getResolvedMode();
        $location    = $this->getResolvedLocationString();
        $description = rtrim(($this->notes ?? '') . "\n\n[AromaMade]");

        $eventData = [
            'name'          => $clientName ? "Rdv $productName – $clientName" : $productName,
            'description'   => $description,
            'startDateTime' => $this->appointment_date,
            'endDateTime'   => \Carbon\Carbon::parse($this->appointment_date)->addMinutes($this->duration ?? 60),
            'location'      => $location,
        ];

        if ($this->google_event_id) {
            $event = \Spatie\GoogleCalendar\Event::find($this->google_event_id);

            if ($event) {
                $event->update($eventData);

                // Keep color in sync as best effort, but never fail the appointment update.
                try {
                    $freshEvent = \Spatie\GoogleCalendar\Event::find($this->google_event_id);
                    if ($freshEvent) {
                        $freshEvent->googleEvent->setColorId((string) $colorId);
                        $freshEvent->save();
                    }
                } catch (\Throwable $e) {
                    Log::warning('Google event color sync skipped after update.', [
                        'appointment_id' => $this->id,
                        'google_event_id' => $this->google_event_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } else {
            $event = \Spatie\GoogleCalendar\Event::create($eventData);

            // Force color (blue by default)
            $event->googleEvent->setColorId((string) $colorId);
            $event->save();

            if ($mode === 'visio') {
                try {
                    // addMeetLink() may return the model or null/void depending on implementation.
                    $maybeEvent = $event->addMeetLink();

                    if ($maybeEvent instanceof \Spatie\GoogleCalendar\Event) {
                        $event = $maybeEvent;
                    }

                    // Persist either way; avoids calling save() on null.
                    $event->save();

                    // Re-apply color after Meet mutation (blue by default)
                    $event->googleEvent->setColorId((string) $colorId);
                    $event->save();
                } catch (\Throwable $e) {
                    \Log::warning('Meet link creation failed', [
                        'appointment_id'  => $this->id,
                        'google_event_id' => $event->id ?? null,
                        'error'           => $e->getMessage(),
                    ]);
                    // Continue without crashing the request.
                }
            }

            $this->forceFill(['google_event_id' => $event->id])->saveQuietly();
        }
    } finally {
        \App\Support\GoogleTokenFile::forget($therapist->id);
    }
}

    private function syncToGoogleSafely(string $context): void
    {
        try {
            $this->syncToGoogle();
        } catch (\Throwable $e) {
            Log::error('Google Calendar sync failed without blocking appointment request.', [
                'appointment_id' => $this->id,
                'google_event_id' => $this->google_event_id,
                'context' => $context,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function removeFromGoogleSafely(): void
    {
        try {
            $this->removeFromGoogle();
        } catch (\Throwable $e) {
            Log::warning('Google Calendar delete sync failed.', [
                'appointment_id' => $this->id,
                'google_event_id' => $this->google_event_id,
                'error' => $e->getMessage(),
            ]);
        }
    }



    public function removeFromGoogle(): void
    {
        $therapist = $this->user;
        if ($this->external || !$this->google_event_id || !$therapist?->google_access_token) {
            return;
        }

        $tokenArr  = json_decode($therapist->google_access_token, true);
        $tokenPath = \App\Support\GoogleTokenFile::put($therapist->id, $tokenArr);

        config([
            'google-calendar.oauth_token'                    => $tokenArr,
            'google-calendar.auth_profiles.oauth.token_json' => $tokenPath,
        ]);

        try {
            GoogleEvent::find($this->google_event_id)?->delete();
        } finally {
            \App\Support\GoogleTokenFile::forget($therapist->id);
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

    public function giftVoucher()
    {
        return $this->belongsTo(GiftVoucher::class, 'gift_voucher_id');
    }
}
