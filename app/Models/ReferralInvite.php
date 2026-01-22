<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralInvite extends Model
{
    protected $fillable = [
        'referrer_user_id',
        'email',
        'token',
        'status',
        'invited_user_id',
        'opened_at',
        'signed_up_at',
        'paid_at',
        'reward_granted_at',
        'expires_at',
        'message',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'signed_up_at' => 'datetime',
        'paid_at' => 'datetime',
        'reward_granted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function invitedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_user_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
