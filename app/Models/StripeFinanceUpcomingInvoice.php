<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StripeFinanceUpcomingInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_subscription_id',
        'stripe_finance_customer_id',
        'stripe_finance_subscription_id',
        'stripe_customer_id',
        'currency',
        'subtotal_cents',
        'total_cents',
        'amount_due_cents',
        'discount_cents',
        'period_start',
        'period_end',
        'next_payment_attempt',
        'due_date',
        'coupon_id',
        'coupon_name',
        'promotion_code',
        'metadata',
        'previewed_at',
    ];

    protected $casts = [
        'subtotal_cents' => 'integer',
        'total_cents' => 'integer',
        'amount_due_cents' => 'integer',
        'discount_cents' => 'integer',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'next_payment_attempt' => 'datetime',
        'due_date' => 'datetime',
        'metadata' => 'array',
        'previewed_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(StripeFinanceCustomer::class, 'stripe_finance_customer_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(StripeFinanceSubscription::class, 'stripe_finance_subscription_id');
    }
}
