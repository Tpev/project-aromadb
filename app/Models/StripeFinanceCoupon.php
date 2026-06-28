<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeFinanceCoupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_coupon_id',
        'name',
        'valid',
        'duration',
        'duration_in_months',
        'percent_off',
        'amount_off_cents',
        'currency',
        'max_redemptions',
        'times_redeemed',
        'redeem_by',
        'metadata',
        'stripe_created_at',
        'last_synced_at',
    ];

    protected $casts = [
        'valid' => 'boolean',
        'duration_in_months' => 'integer',
        'percent_off' => 'decimal:2',
        'amount_off_cents' => 'integer',
        'max_redemptions' => 'integer',
        'times_redeemed' => 'integer',
        'redeem_by' => 'datetime',
        'metadata' => 'array',
        'stripe_created_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];
}
