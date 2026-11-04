<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GiftVoucher extends Model
{
    public const STATUS_FILTERS = [
        'all' => 'Tous', 'active' => 'Actifs', 'reserved' => 'Réservations en cours',
        'expired' => 'Expirés', 'exhausted' => 'Épuisés', 'disabled' => 'Désactivés',
    ];

    protected $fillable = [
        'user_id',
        'code',
        'original_amount_cents',
        'remaining_amount_cents',
        'currency',
        'is_active',
        'expires_at',
        'buyer_name',
        'buyer_email',
        'buyer_phone',
        'recipient_name',
        'recipient_email',
        'message',
        'source',
        'sale_channel',
        'sale_status',
        'sale_invoice_id',
        'background_mode_snapshot',
        'background_path_snapshot',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(GiftVoucherRedemption::class);
    }

    public function saleInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'sale_invoice_id');
    }

    public function isExpired(): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        return now()->greaterThan($this->expires_at);
    }

    public function isUsable(): bool
    {
        return $this->is_active
            && ! $this->isExpired()
            && $this->remaining_amount_cents > 0;
    }

    public function reservedAmountCents(): int
    {
        if ($this->relationLoaded('redemptions')) {
            return (int) $this->redemptions->where('status', 'reserved')->sum('amount_cents');
        }
        if (array_key_exists('reserved_amount_cents', $this->attributes)) {
            return (int) $this->attributes['reserved_amount_cents'];
        }

        return $this->exists ? (int) $this->redemptions()->where('status', 'reserved')->sum('amount_cents') : 0;
    }

    public function displayStatus(): string
    {
        $reserved = $this->reservedAmountCents();
        // Legacy fully used vouchers were automatically disabled. Do not change
        // their activation flag or reconstruct balances to correct the label.
        if ($this->remaining_amount_cents <= 0 && $reserved <= 0) {
            return 'exhausted';
        }
        if (! $this->is_active) {
            return 'disabled';
        }
        if ($this->isExpired()) {
            return 'expired';
        }
        if ($reserved > 0) {
            return 'reserved';
        }

        return 'active';
    }

    public function statusLabel(): string
    {
        return match ($this->displayStatus()) {
            'exhausted' => 'Épuisé',
            'disabled' => 'Désactivé',
            'expired' => 'Expiré',
            'reserved' => 'Réservation en cours',
            default => 'Actif',
        };
    }

    public function scopeWithReservationTotals(Builder $query): Builder
    {
        return $query->withSum(['redemptions as reserved_amount_cents' => fn ($uses) => $uses->where('status', 'reserved')], 'amount_cents');
    }

    public function scopeWithDisplayStatus(Builder $query, string $status): Builder
    {
        $reserved = fn ($uses) => $uses->where('status', 'reserved')->where('amount_cents', '>', 0);
        if ($status === 'exhausted') {
            return $query->where('remaining_amount_cents', '<=', 0)->whereDoesntHave('redemptions', $reserved);
        }
        if (! in_array($status, ['active', 'reserved', 'expired', 'disabled'], true)) {
            return $query;
        }

        $query->where(fn ($q) => $q->where('remaining_amount_cents', '>', 0)->orWhereHas('redemptions', $reserved));
        if ($status === 'disabled') {
            return $query->where('is_active', false);
        }

        $query->where('is_active', true);
        if ($status === 'expired') {
            return $query->whereNotNull('expires_at')->where('expires_at', '<', now());
        }

        $query->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()));

        return $status === 'reserved'
            ? $query->whereHas('redemptions', $reserved)
            : $query->whereDoesntHave('redemptions', $reserved)->where('remaining_amount_cents', '>', 0);
    }

    public function money(int $cents): string
    {
        // French formatting
        return number_format($cents / 100, 2, ',', ' ').' €';
    }

    public function originalAmountStr(): string
    {
        return $this->money($this->original_amount_cents);
    }

    public function remainingAmountStr(): string
    {
        return $this->money($this->remaining_amount_cents);
    }

    public function expiresAtStr(): ?string
    {
        return $this->expires_at?->timezone('Europe/Paris')->format('d/m/Y');
    }
}
