<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'event_id',
        'full_name',
        'email',
        'phone',
		'reminder_24h_sent_at',
        'reminder_1h_sent_at',
    ];
    protected $casts = [
        'reminder_24h_sent_at' => 'datetime',
        'reminder_1h_sent_at'  => 'datetime',
    ];
    /**
     * Get the event that owns the reservation.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
