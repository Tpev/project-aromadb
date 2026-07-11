<?php

namespace App\Domain\OfferJourneys\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfferJourneyAutomationRun extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'started_at' => 'datetime',
        'next_action_at' => 'datetime',
        'exited_at' => 'datetime',
    ];

    public function automation(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyAutomation::class, 'offer_journey_automation_id');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyAutomationVersion::class, 'offer_journey_automation_version_id');
    }

    public function journeyVersion(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyVersion::class, 'offer_journey_version_id');
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyEntry::class, 'offer_journey_entry_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyContact::class, 'offer_journey_contact_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(OfferJourneyMessageDelivery::class);
    }
}
