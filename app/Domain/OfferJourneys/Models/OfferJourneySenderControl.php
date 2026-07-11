<?php

namespace App\Domain\OfferJourneys\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferJourneySenderControl extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'marketing_paused' => 'boolean',
        'all_email_paused' => 'boolean',
        'paused_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pausedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paused_by_user_id');
    }
}
