<?php

namespace App\Domain\OfferJourneys\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferJourneyAbandonmentCandidate extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'started_at' => 'datetime',
        'reminder_due_at' => 'datetime',
        'reminded_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function journey(): BelongsTo
    {
        return $this->belongsTo(OfferJourney::class, 'offer_journey_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyContact::class, 'offer_journey_contact_id');
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyEntry::class, 'offer_journey_entry_id');
    }
}
