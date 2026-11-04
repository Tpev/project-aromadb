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
        'status',
        'source',
        'released_at',
    ];

    protected $casts = [
        'released_at' => 'datetime',
    ];

    public function statusLabel(): string
    {
        return match ($this->status) {
            'reserved' => 'Réservation en cours',
            'released' => 'Réservation libérée',
            default => 'Utilisation confirmée',
        };
    }

    public function amountLabel(): string
    {
        $prefix = match ($this->status) {
            'reserved' => 'Montant réservé : ',
            'released' => 'Montant libéré : ',
            default => 'Montant utilisé : ',
        };

        return $prefix.number_format($this->amount_cents / 100, 2, ',', ' ').' €';
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(GiftVoucher::class, 'gift_voucher_id');
    }

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
