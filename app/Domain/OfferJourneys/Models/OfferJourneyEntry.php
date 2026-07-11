<?php

namespace App\Domain\OfferJourneys\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferJourneyEntry extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'entered_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'converted_at' => 'datetime',
        'exited_at' => 'datetime',
    ];

    public function journey(): BelongsTo
    {
        return $this->belongsTo(OfferJourney::class, 'offer_journey_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyContact::class, 'offer_journey_contact_id');
    }

    public function currentPage(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyPage::class, 'current_page_id');
    }
}
