<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeFinanceBalanceTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_balance_transaction_id',
        'stripe_source_id',
        'stripe_payout_id',
        'stripe_customer_id',
        'stripe_invoice_id',
        'stripe_subscription_id',
        'type',
        'reporting_category',
        'status',
        'currency',
        'amount_cents',
        'fee_cents',
        'net_cents',
        'exchange_rate',
        'available_on',
        'stripe_created_at',
        'description',
        'metadata',
        'last_synced_at',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'fee_cents' => 'integer',
        'net_cents' => 'integer',
        'exchange_rate' => 'decimal:6',
        'available_on' => 'datetime',
        'stripe_created_at' => 'datetime',
        'metadata' => 'array',
        'last_synced_at' => 'datetime',
    ];
}
