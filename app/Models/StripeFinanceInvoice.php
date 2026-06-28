<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StripeFinanceInvoice extends Model
{
    use HasFactory;

    public const STATUS_LABELS = [
        'draft' => 'Brouillon',
        'open' => 'Ouverte',
        'paid' => 'Payée',
        'uncollectible' => 'Irrécouvrable',
        'void' => 'Annulée',
    ];

    protected $fillable = [
        'stripe_invoice_id',
        'stripe_finance_customer_id',
        'stripe_finance_subscription_id',
        'stripe_customer_id',
        'stripe_subscription_id',
        'number',
        'status',
        'billing_reason',
        'collection_method',
        'currency',
        'subtotal_cents',
        'total_cents',
        'tax_cents',
        'discount_cents',
        'amount_due_cents',
        'amount_paid_cents',
        'amount_remaining_cents',
        'attempted',
        'attempt_count',
        'next_payment_attempt',
        'due_date',
        'period_start',
        'period_end',
        'paid_at',
        'stripe_created_at',
        'hosted_invoice_url',
        'invoice_pdf',
        'last_payment_error_code',
        'last_payment_error_message',
        'metadata',
        'last_synced_at',
    ];

    protected $casts = [
        'subtotal_cents' => 'integer',
        'total_cents' => 'integer',
        'tax_cents' => 'integer',
        'discount_cents' => 'integer',
        'amount_due_cents' => 'integer',
        'amount_paid_cents' => 'integer',
        'amount_remaining_cents' => 'integer',
        'attempted' => 'boolean',
        'attempt_count' => 'integer',
        'next_payment_attempt' => 'datetime',
        'due_date' => 'datetime',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'paid_at' => 'datetime',
        'stripe_created_at' => 'datetime',
        'metadata' => 'array',
        'last_synced_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(StripeFinanceCustomer::class, 'stripe_finance_customer_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(StripeFinanceSubscription::class, 'stripe_finance_subscription_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getIsFailedAttribute(): bool
    {
        return $this->attempted
            && $this->amount_remaining_cents > 0
            && in_array($this->status, ['open', 'uncollectible'], true);
    }

    public function getStripeDashboardUrlAttribute(): string
    {
        return 'https://dashboard.stripe.com/invoices/' . $this->stripe_invoice_id;
    }
}
