<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'pack_purchase_id',
        'sequence_number',
        'amount_cents',
        'currency',
        'status',
        'due_at',
        'paid_at',
        'stripe_invoice_id',
        'stripe_payment_intent_id',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function purchase()
    {
        return $this->belongsTo(PackPurchase::class, 'pack_purchase_id');
    }
}

