<?php

// app/Models/Reservation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'event_id',
        'full_name',
        'email',
        'phone',

        // status / payment
        'status', // confirmed | pending_payment | paid | canceled
        'amount_ttc',
        'currency',
        'stripe_session_id',
        'stripe_payment_intent_id',

        // reminders
        'reminder_24h_sent_at',
        'reminder_1h_sent_at',
    ];

    protected $casts = [
        'amount_ttc' => 'float',
        'reminder_24h_sent_at' => 'datetime',
        'reminder_1h_sent_at'  => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}