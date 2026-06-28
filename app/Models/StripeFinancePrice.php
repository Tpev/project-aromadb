<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeFinancePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_price_id',
        'stripe_product_id',
        'nickname',
        'active',
        'currency',
        'unit_amount_cents',
        'billing_scheme',
        'type',
        'interval',
        'interval_count',
        'lookup_key',
        'metadata',
        'stripe_created_at',
        'last_synced_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'unit_amount_cents' => 'integer',
        'interval_count' => 'integer',
        'metadata' => 'array',
        'stripe_created_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];
}
