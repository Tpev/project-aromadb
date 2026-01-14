<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftVoucherRedemption extends Model
{
    protected $fillable = [
        'gift_voucher_id',
        'user_id',
        'amount_cents',
        'appointment_id',
        'invoice_id',
        'note',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(GiftVoucher::class, 'gift_voucher_id');
    }

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
