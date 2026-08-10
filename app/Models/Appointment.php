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

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PAID = 'paid';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const CANCELLED_STATUSES = [
        'cancelled',
        'canceled',
        'Annulé',
        'Annule',
        'Annulée',
        'Annulee',
        'annulé',
        'annule',
        'annulée',
        'annulee',
    ];

    public const WRITABLE_STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_PENDING_PAYMENT,
        self::STATUS_CONFIRMED,
        self::STATUS_PAID,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    public const ACCEPTED_STATUS_VALUES = [
        'scheduled', 'Programmé', 'Programme',
        'pending', 'pending_payment', 'En attente', 'En attente de paiement',
        'confirmed', 'Confirmé', 'Confirme', 'Confirmée', 'Confirmee',
        'paid', 'Payée', 'Payee', 'Payé', 'Paye',
        'completed', 'Complété', 'Complete', 'Complétée', 'Completee', 'Terminé', 'Termine', 'Terminée', 'Terminee',
        'cancelled', 'canceled', 'Annulé', 'Annule', 'Annulée', 'Annulee',
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
        'cancelled_at',
        'cancelled_by_type',
        'cancelled_by_id',
        'cancellation_reason',
        'rescheduled_at',
        'rescheduled_by_type',
        'rescheduled_by_id',
        'reminder_24h_sent_at',
        'reminder_1h_sent_at',
        'reminder_24h_queued_at',
        'reminder_1h_queued_at',
        'client_confirmation_sent_at',
        'consumed_pack_purchase_id',
        'financial_follow_up_required',

        // NEW
        'requires_emargement',
        'emargement_sent',
    ];

    protected $casts = [
        'appointment_date'     => 'datetime',
        'external'             => 'boolean',
        'duration'             => 'integer',
        'gift_voucher_amount_cents' => 'integer',
        'cancelled_at'         => 'datetime',
        'rescheduled_at'       => 'datetime',
        'reminder_24h_sent_at' => 'datetime',
        'reminder_1h_sent_at'  => 'datetime',
        'reminder_24h_queued_at' => 'datetime',
        'reminder_1h_queued_at' => 'datetime',
        'client_confirmation_sent_at' => 'datetime',
        'financial_follow_up_required' => 'boolean',

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
        return $this->canonicalStatus() === self::STATUS_CANCELLED;
    }

    public static function normalizeStatus(?string $status): string
    {
        $normalized = Str::lower(Str::ascii(trim((string) $status)));

        return match ($normalized) {
            'pending', 'pending_payment', 'en attente', 'en attente de paiement' => self::STATUS_PENDING_PAYMENT,
            'confirmed', 'confirme', 'confirmee' => self::STATUS_CONFIRMED,
            'paid', 'payee', 'paye' => self::STATUS_PAID,
            'completed', 'complete', 'completee', 'termine', 'terminee' => self::STATUS_COMPLETED,
            'cancelled', 'canceled', 'annule', 'annulee' => self::STATUS_CANCELLED,
            'scheduled', 'programme', 'programmee' => self::STATUS_SCHEDULED,
            default => self::STATUS_SCHEDULED,
        };
    }

    public static function statusValuesFor(string $canonicalStatus): array
    {
        return array_values(array_filter(
            self::ACCEPTED_STATUS_VALUES,
            fn (string $status): bool => self::normalizeStatus($status) === $canonicalStatus
        ));
    }

    public function canonicalStatus(): string
    {
        return self::normalizeStatus($this->status);
    }

    public function isScheduled(): bool
    {
        return $this->canonicalStatus() === self::STATUS_SCHEDULED;
    }

    public function isPendingPayment(): bool
    {
        return $this->canonicalStatus() === self::STATUS_PENDING_PAYMENT;
    }

    public function isConfirmed(): bool
    {
        return $this->canonicalStatus() === self::STATUS_CONFIRMED;
    }

    public function isPaid(): bool
    {
        return $this->canonicalStatus() === self::STATUS_PAID;
    }

    public function isCompleted(): bool
    {
        return $this->canonicalStatus() === self::STATUS_COMPLETED;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->canonicalStatus()) {
            self::STATUS_PENDING_PAYMENT => 'En attente de paiement',
            self::STATUS_CONFIRMED => 'Confirmé',
            self::STATUS_PAID => 'Payé',
            self::STATUS_COMPLETED => 'Terminé',
            self::STATUS_CANCELLED => 'Annulé',
            default => 'Programmé',
        };
    }

    public function managementDeadlineAt(): ?Carbon
    {
        if (!$this->appointment_date) {
            return null;
        }

        $hours = max(0, (int) ($this->user?->cancellation_notice_hours ?? 0));

        return $this->appointment_date->copy()->subHours($hours);
    }

    public function canBeManagedOnline(): bool
    {
        if ($this->external || !$this->appointment_date || $this->isCancelled() || $this->isCompleted()) {
            return false;
        }

        return $this->appointment_date->isFuture()
            && now()->lte($this->managementDeadlineAt());
    }

    public function requiresFinancialFollowUp(): bool
    {
        return (bool) $this->financial_follow_up_required
            || $this->isPaid()
            || $this->billingInvoices()->exists()
            || (int) ($this->gift_voucher_amount_cents ?? 0) > 0
            || !is_null($this->consumed_pack_purchase_id);
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

    if ($this->isCancelled()) {
        $this->removeFromGoogle();
        return;
    }

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
        $description = rtrim(($this->notes ?? '') . "\n\n[Olithea]");

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
            try {
                GoogleEvent::find($this->google_event_id)?->delete();
            } catch (\Google\Service\Exception $e) {
                if (! in_array((int) $e->getCode(), [404, 410], true)) {
                    throw $e;
                }
            }

            if ($this->exists) {
                $this->forceFill(['google_event_id' => null])->saveQuietly();
            }
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

    public function activities()
    {
        return $this->hasMany(AppointmentActivity::class)->orderBy('created_at');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function billingInvoices()
    {
        return $this->hasMany(Invoice::class)->where('type', 'invoice');
    }

    public function sessionNotes()
    {
        return $this->hasMany(SessionNote::class);
    }

    public function getSessionTrackingLabelAttribute(): string
    {
        if ($this->isCancelled()) {
            return 'Annulée';
        }

        if ($this->isCompleted() || $this->appointment_date?->isPast()) {
            return 'Terminée';
        }

        return 'À venir';
    }

    public function getNoteTrackingLabelAttribute(): string
    {
        return $this->sessionNotes->isNotEmpty() ? 'Note créée' : 'Note à rédiger';
    }

    public function getBillingTrackingLabelAttribute(): string
    {
        $invoices = $this->billingInvoices;

        if ($invoices->isEmpty()) {
            return 'À facturer';
        }

        if ($invoices->count() > 1) {
            return 'Plusieurs factures';
        }

        return $invoices->first()->appointment_billing_status;
    }

    public function giftVoucher()
    {
        return $this->belongsTo(GiftVoucher::class, 'gift_voucher_id');
    }
}
