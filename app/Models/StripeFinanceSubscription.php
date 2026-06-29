<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StripeFinanceSubscription extends Model
{
    use HasFactory;

    public const STATUS_LABELS = [
        'active' => 'Actif',
        'trialing' => 'Essai',
        'past_due' => 'En retard',
        'unpaid' => 'Impayé',
        'incomplete' => 'Paiement incomplet',
        'incomplete_expired' => 'Incomplet expiré',
        'canceled' => 'Annulé',
        'paused' => 'En pause',
    ];

    public const INTERVAL_LABELS = [
        'day' => 'Jour',
        'week' => 'Semaine',
        'month' => 'Mensuel',
        'year' => 'Annuel',
    ];

    protected $fillable = [
        'stripe_subscription_id',
        'stripe_finance_customer_id',
        'user_id',
        'stripe_customer_id',
        'status',
        'collection_method',
        'cancel_at_period_end',
        'cancel_at',
        'canceled_at',
        'ended_at',
        'current_period_start',
        'current_period_end',
        'trial_start',
        'trial_end',
        'next_payment_attempt',
        'amount_cents',
        'currency',
        'interval',
        'interval_count',
        'product_id',
        'product_name',
        'price_id',
        'price_nickname',
        'license_label',
        'coupon_id',
        'coupon_name',
        'promotion_code',
        'discount_percent',
        'discount_amount_cents',
        'latest_invoice_id',
        'default_payment_method_label',
        'metadata',
        'last_synced_at',
    ];

    protected $casts = [
        'cancel_at_period_end' => 'boolean',
        'cancel_at' => 'datetime',
        'canceled_at' => 'datetime',
        'ended_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'trial_start' => 'datetime',
        'trial_end' => 'datetime',
        'next_payment_attempt' => 'datetime',
        'amount_cents' => 'integer',
        'interval_count' => 'integer',
        'discount_percent' => 'decimal:2',
        'discount_amount_cents' => 'integer',
        'metadata' => 'array',
        'last_synced_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(StripeFinanceCustomer::class, 'stripe_finance_customer_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(StripeFinanceInvoice::class, 'stripe_finance_subscription_id');
    }

    public function latestInvoice(): HasOne
    {
        return $this->hasOne(StripeFinanceInvoice::class, 'stripe_finance_subscription_id')->latestOfMany('stripe_created_at');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(StripeFinanceNote::class, 'stripe_finance_subscription_id');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['active', 'trialing', 'past_due', 'unpaid', 'incomplete']);
    }

    public function scopeRevenueActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['active', 'trialing'])
            ->where(function (Builder $inner) {
                $inner->whereNull('ended_at')->orWhere('ended_at', '>', now());
            });
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function getIntervalLabelAttribute(): string
    {
        return self::INTERVAL_LABELS[$this->interval] ?? 'Non défini';
    }

    public function getLicenseDisplayAttribute(): string
    {
        return $this->license_label ?: ($this->product_name ?: ($this->price_nickname ?: 'Licence non définie'));
    }

    public function getMrrCentsAttribute(): int
    {
        $amount = max(0, (int) $this->amount_cents);
        $count = max(1, (int) $this->interval_count);

        return match ($this->interval) {
            'year' => (int) round($amount / (12 * $count)),
            'month' => (int) round($amount / $count),
            'week' => (int) round(($amount * 52) / (12 * $count)),
            'day' => (int) round(($amount * 365) / (12 * $count)),
            default => $amount,
        };
    }

    public function getArrCentsAttribute(): int
    {
        return $this->mrr_cents * 12;
    }

    public function getIsFailedPaymentAttribute(): bool
    {
        return in_array($this->status, ['past_due', 'unpaid', 'incomplete'], true)
            || $this->invoices()
                ->where('attempted', true)
                ->where('amount_remaining_cents', '>', 0)
                ->whereIn('status', ['open', 'uncollectible'])
                ->exists();
    }

    public function getBoardStatusAttribute(): string
    {
        if ($this->is_failed_payment) {
            return 'payment_failed';
        }

        if ($this->cancel_at_period_end) {
            return 'canceling';
        }

        if ($this->status === 'trialing') {
            return 'trialing';
        }

        if (in_array($this->status, ['past_due', 'unpaid'], true)) {
            return 'past_due';
        }

        if (in_array($this->status, ['canceled', 'incomplete_expired'], true)) {
            return 'canceled';
        }

        return 'active';
    }

    public function getStripeDashboardUrlAttribute(): string
    {
        return 'https://dashboard.stripe.com/subscriptions/' . $this->stripe_subscription_id;
    }
}
