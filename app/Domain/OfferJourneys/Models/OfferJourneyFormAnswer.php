<?php

namespace App\Domain\OfferJourneys\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferJourneyFormAnswer extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['value_json' => 'array', 'answered_at' => 'datetime'];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyContact::class, 'offer_journey_contact_id');
    }

    public function journey(): BelongsTo
    {
        return $this->belongsTo(OfferJourney::class, 'offer_journey_id');
    }
}
