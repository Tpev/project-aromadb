<?php

namespace App\Domain\OfferJourneys\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferJourneySegmentRule extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['value_json' => 'array'];

    public function segment(): BelongsTo
    {
        return $this->belongsTo(OfferJourneySegment::class, 'offer_journey_segment_id');
    }
}
