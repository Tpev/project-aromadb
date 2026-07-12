<?php

namespace App\Domain\OfferJourneys\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferJourneyMessageDelivery extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
        'is_test' => 'boolean',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'bounced_at' => 'datetime',
        'complained_at' => 'datetime',
        'rejected_at' => 'datetime',
        'failed_at' => 'datetime',
        'skipped_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function journey(): BelongsTo
    {
        return $this->belongsTo(OfferJourney::class, 'offer_journey_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyContact::class, 'offer_journey_contact_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyMessageCampaign::class, 'offer_journey_message_campaign_id');
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyAutomationRun::class, 'offer_journey_automation_run_id');
    }

    public function deliverabilityEvents()
    {
        return $this->hasMany(OfferJourneyDeliverabilityEvent::class, 'offer_journey_message_delivery_id');
    }
}
