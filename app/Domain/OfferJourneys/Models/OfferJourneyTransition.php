<?php

namespace App\Domain\OfferJourneys\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferJourneyTransition extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'condition_json' => 'array',
        'is_fallback' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function journey(): BelongsTo
    {
        return $this->belongsTo(OfferJourney::class, 'offer_journey_id');
    }

    public function fromPage(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyPage::class, 'from_page_id');
    }

    public function toPage(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyPage::class, 'to_page_id');
    }
}
