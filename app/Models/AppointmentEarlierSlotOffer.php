<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentEarlierSlotOffer extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_CLAIMED = 'claimed';

    public const STATUS_INVALIDATED = 'invalidated';

    protected $fillable = [
        'opportunity_id',
        'appointment_id',
        'token',
        'token_hash',
        'status',
        'sent_at',
        'claimed_at',
        'invalidated_at',
    ];

    protected $hidden = ['token', 'token_hash'];

    protected $casts = [
        'token' => 'encrypted',
        'sent_at' => 'datetime',
        'claimed_at' => 'datetime',
        'invalidated_at' => 'datetime',
    ];

    public function opportunity()
    {
        return $this->belongsTo(AppointmentEarlierSlotOpportunity::class, 'opportunity_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function isClaimable(): bool
    {
        $this->loadMissing(['opportunity', 'appointment.user']);

        return config('appointments.earlier_slots.enabled', false)
            && $this->status === self::STATUS_PENDING
            && $this->opportunity?->isOpen()
            && $this->appointment
            && $this->appointment->wants_earlier_slot
            && $this->appointment->canBeManagedOnline()
            && ! $this->appointment->isPendingPayment()
            && ! $this->appointment->isCompleted()
            && $this->opportunity->slot_start->lt($this->appointment->appointment_date);
    }
}
