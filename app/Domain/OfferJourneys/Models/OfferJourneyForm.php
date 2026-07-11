<?php

namespace App\Domain\OfferJourneys\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfferJourneyForm extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_active' => 'boolean'];

    public function journey(): BelongsTo
    {
        return $this->belongsTo(OfferJourney::class, 'offer_journey_id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyPage::class, 'offer_journey_page_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(OfferJourneyFormField::class)->orderBy('position');
    }
}
