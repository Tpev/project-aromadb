<?php

namespace App\Domain\OfferJourneys\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OfferJourneyConversion extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'attribution_json' => 'array',
        'occurred_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function journey(): BelongsTo
    {
        return $this->belongsTo(OfferJourney::class, 'offer_journey_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyContact::class, 'offer_journey_contact_id');
    }

    public function convertible(): MorphTo
    {
        return $this->morphTo();
    }
}
