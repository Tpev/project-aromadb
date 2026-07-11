<?php

namespace App\Domain\OfferJourneys\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferJourneyEvent extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
        'is_test' => 'boolean',
        'is_bot' => 'boolean',
        'occurred_at' => 'datetime',
    ];

    public function journey(): BelongsTo
    {
        return $this->belongsTo(OfferJourney::class, 'offer_journey_id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyPage::class, 'offer_journey_page_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyContact::class, 'offer_journey_contact_id');
    }

    public function campaignLink(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyCampaignLink::class, 'offer_journey_campaign_link_id');
    }
}
