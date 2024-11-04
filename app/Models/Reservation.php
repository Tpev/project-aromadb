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
    ];

    /**
     * Get the event that owns the reservation.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
