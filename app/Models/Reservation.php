<?php

// app/Models/Reservation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    public const ACTIVE_STATUSES = ['confirmed', 'pending_payment', 'paid'];

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
        'cancelled_at' => 'datetime',
        'payment_confirmation_requested_at' => 'datetime',
        'amount_ttc' => 'float',
        'confirmation_sent_at' => 'datetime',
        'therapist_notification_sent_at' => 'datetime',
        'reminder_24h_sent_at' => 'datetime',
        'reminder_1h_sent_at'  => 'datetime',
    ];

    public function isEmailEligible(): bool
    {
        return $this->event !== null && $this->isActive() && $this->status !== 'pending_payment';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('cancelled_at')->whereIn('status', self::ACTIVE_STATUSES);
    }

    public function isActive(): bool
    {
        return ! $this->cancelled_at && in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
