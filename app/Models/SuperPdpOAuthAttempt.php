<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuperPdpOAuthAttempt extends Model
{
    protected $table = 'super_pdp_oauth_attempts';

    protected $fillable = [
        'user_id',
        'environment',
        'state_hash',
        'receive_in_app',
        'expires_at',
        'consumed_at',
    ];

    protected $casts = [
        'receive_in_app' => 'boolean',
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
