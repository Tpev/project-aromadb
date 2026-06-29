<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StripeFinanceCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_customer_id',
        'user_id',
        'name',
        'email',
        'phone',
        'currency',
        'invoice_prefix',
        'default_payment_method_label',
        'delinquent',
        'balance_cents',
        'metadata',
        'stripe_created_at',
        'last_synced_at',
    ];

    protected $casts = [
        'delinquent' => 'boolean',
        'balance_cents' => 'integer',
        'metadata' => 'array',
        'stripe_created_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(StripeFinanceSubscription::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(StripeFinanceInvoice::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(StripeFinanceNote::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: ($this->email ?: $this->stripe_customer_id);
    }

    public function getStripeDashboardUrlAttribute(): string
    {
        return 'https://dashboard.stripe.com/customers/' . $this->stripe_customer_id;
    }
}
