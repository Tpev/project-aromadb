<?php

namespace App\Domain\OfferJourneys\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfferJourneyPage extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'draft_content_json' => 'array',
        'theme_json' => 'array',
        'is_indexable' => 'boolean',
    ];

    public function journey(): BelongsTo
    {
        return $this->belongsTo(OfferJourney::class, 'offer_journey_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(OfferJourneyPageVersion::class);
    }

    public function form(): HasOne
    {
        return $this->hasOne(OfferJourneyForm::class);
    }

    public function outgoingTransitions(): HasMany
    {
        return $this->hasMany(OfferJourneyTransition::class, 'from_page_id')->orderBy('priority');
    }
}
