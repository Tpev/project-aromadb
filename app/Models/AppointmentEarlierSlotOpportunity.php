<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentEarlierSlotOpportunity extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_CLAIMED = 'claimed';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'user_id',
        'released_appointment_id',
        'claimed_appointment_id',
        'product_id',
        'practice_location_id',
        'location_fingerprint',
        'slot_start',
        'duration',
        'mode',
        'status',
        'expires_at',
        'claimed_at',
    ];

    protected $casts = [
        'slot_start' => 'datetime',
        'duration' => 'integer',
        'expires_at' => 'datetime',
        'claimed_at' => 'datetime',
    ];

    public function practitioner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function practiceLocation()
    {
        return $this->belongsTo(PracticeLocation::class);
    }

    public function releasedAppointment()
    {
        return $this->belongsTo(Appointment::class, 'released_appointment_id');
    }

    public function claimedAppointment()
    {
        return $this->belongsTo(Appointment::class, 'claimed_appointment_id');
    }

    public function offers()
    {
        return $this->hasMany(AppointmentEarlierSlotOffer::class, 'opportunity_id');
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN
            && $this->expires_at?->isFuture();
    }
}
