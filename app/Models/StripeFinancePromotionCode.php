<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeFinancePromotionCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_promotion_code_id',
        'code',
        'stripe_coupon_id',
        'active',
        'max_redemptions',
        'times_redeemed',
        'expires_at',
        'metadata',
        'stripe_created_at',
        'last_synced_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'max_redemptions' => 'integer',
        'times_redeemed' => 'integer',
        'expires_at' => 'datetime',
        'metadata' => 'array',
        'stripe_created_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];
}
