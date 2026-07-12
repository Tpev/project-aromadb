<?php

namespace App\Domain\OfferJourneys\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfferJourneyMessageCampaign extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'processing_started_at' => 'datetime',
        'sent_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'summary_json' => 'array',
        'content_json' => 'array',
        'style_json' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function journeys(): BelongsToMany
    {
        return $this->belongsToMany(OfferJourney::class, 'offer_journey_message_campaign_journey')->withTimestamps();
    }

    public function segment(): BelongsTo
    {
        return $this->belongsTo(OfferJourneySegment::class, 'offer_journey_segment_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(OfferJourneyMessageDelivery::class, 'offer_journey_message_campaign_id');
    }

    public function emailAssets(): HasMany
    {
        return $this->hasMany(OfferJourneyEmailAsset::class, 'offer_journey_message_campaign_id');
    }
}
