<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class GiftVoucher extends Model
{
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
        if (!$this->expires_at) return false;
        return now()->greaterThan($this->expires_at);
    }

    public function isUsable(): bool
    {
        return $this->is_active
            && !$this->isExpired()
            && $this->remaining_amount_cents > 0;
    }

    public function statusLabel(): string
    {
        if (!$this->is_active) return 'Désactivé';
        if ($this->isExpired()) return 'Expiré';
        if ($this->remaining_amount_cents <= 0) return 'Épuisé';
        return 'Actif';
    }

    public function money(int $cents): string
    {
        // French formatting
        return number_format($cents / 100, 2, ',', ' ') . ' €';
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
