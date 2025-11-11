<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssistantSession extends Model
{
    protected $fillable = [
        'user_id',
        'current_intent',
        'collected_slots',
        'missing_slots',
        'awaiting_confirmation',
        'expires_at',
    ];

    protected $casts = [
        'collected_slots' => 'array',
        'missing_slots'   => 'array',
        'awaiting_confirmation' => 'boolean',
        'expires_at' => 'datetime',
    ];
}
