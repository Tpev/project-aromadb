<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftVoucherOrder extends Model
{
    protected $fillable = [
        'user_id',
        'amount_cents',
        'currency',
        'cancel_token',
        'buyer_name',
        'buyer_email',
        'buyer_phone',
        'recipient_name',
        'recipient_email',
        'message',
        'expires_at',
        'status',
        'gift_voucher_id',
        'sale_invoice_id',
        'stripe_session_id',
        'stripe_payment_intent_id',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(GiftVoucher::class, 'gift_voucher_id');
    }

    public function saleInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'sale_invoice_id');
    }
}
