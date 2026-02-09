<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class BookingLink extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'name',
        'allowed_product_ids',
        'expires_at',
        'max_uses',
        'uses_count',
        'is_enabled',
    ];

    protected $casts = [
        'allowed_product_ids' => 'array',
        'expires_at' => 'datetime',
        'is_enabled' => 'boolean',
        'uses_count' => 'integer',
        'max_uses' => 'integer',
    ];

    // -------------------------
    // Relations
    // -------------------------
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    // -------------------------
    // Helpers
    // -------------------------
    public static function generateToken(int $length = 32): string
    {
        // URL-safe token
        return Str::random($length);
    }

    public function isExpired(): bool
    {
        return $this->expires_at instanceof Carbon
            ? $this->expires_at->isPast()
            : false;
    }

    public function isMaxedOut(): bool
    {
        if (!$this->max_uses) {
            return false;
        }

        return $this->uses_count >= $this->max_uses;
    }

    public function canBeUsed(): bool
    {
        return $this->is_enabled && !$this->isExpired() && !$this->isMaxedOut();
    }

    public function allowedProductIds(): array
    {
        $ids = $this->allowed_product_ids ?? [];
        $ids = is_array($ids) ? $ids : [];

        // sanitize to int unique
        $ids = array_values(array_unique(array_filter(array_map(function ($v) {
            if (is_numeric($v)) return (int) $v;
            return null;
        }, Arr::flatten($ids)))));

        return $ids;
    }

    public function allowsProduct(int $productId): bool
    {
        return in_array($productId, $this->allowedProductIds(), true);
    }

    public function incrementUse(): void
    {
        $this->increment('uses_count');
    }
}
