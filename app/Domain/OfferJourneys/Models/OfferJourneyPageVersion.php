<?php

namespace App\Domain\OfferJourneys\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferJourneyPageVersion extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'content_json' => 'array',
        'theme_json' => 'array',
        'is_indexable' => 'boolean',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyVersion::class, 'offer_journey_version_id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyPage::class, 'offer_journey_page_id');
    }

}
