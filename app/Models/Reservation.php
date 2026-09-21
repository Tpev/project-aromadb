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
        'payment_confirmation_requested_at' => 'datetime',
        'amount_ttc' => 'float',
        'confirmation_sent_at' => 'datetime',
        'therapist_notification_sent_at' => 'datetime',
        'reminder_24h_sent_at' => 'datetime',
        'reminder_1h_sent_at'  => 'datetime',
    ];

    public function isEmailEligible(): bool
    {
        return $this->event !== null && in_array($this->status, ['confirmed', 'paid'], true);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
