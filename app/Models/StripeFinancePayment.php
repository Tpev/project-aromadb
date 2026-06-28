<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeFinancePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_charge_id',
        'stripe_payment_intent_id',
        'stripe_customer_id',
        'stripe_invoice_id',
        'stripe_subscription_id',
        'stripe_balance_transaction_id',
        'status',
        'paid',
        'captured',
        'refunded',
        'disputed',
        'currency',
        'amount_cents',
        'amount_captured_cents',
        'amount_refunded_cents',
        'fee_cents',
        'net_cents',
        'failure_code',
        'failure_message',
        'payment_method_type',
        'payment_method_label',
        'receipt_url',
        'metadata',
        'stripe_created_at',
        'last_synced_at',
    ];

    protected $casts = [
        'paid' => 'boolean',
        'captured' => 'boolean',
        'refunded' => 'boolean',
        'disputed' => 'boolean',
        'amount_cents' => 'integer',
        'amount_captured_cents' => 'integer',
        'amount_refunded_cents' => 'integer',
        'fee_cents' => 'integer',
        'net_cents' => 'integer',
        'metadata' => 'array',
        'stripe_created_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];
}
