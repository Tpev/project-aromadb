<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeFinanceProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_product_id',
        'name',
        'active',
        'type',
        'description',
        'metadata',
        'stripe_created_at',
        'last_synced_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'metadata' => 'array',
        'stripe_created_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];
}
