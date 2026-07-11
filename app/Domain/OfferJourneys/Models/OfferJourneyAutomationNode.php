<?php

namespace App\Domain\OfferJourneys\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferJourneyAutomationNode extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['config_json' => 'array'];

    public function version(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyAutomationVersion::class, 'offer_journey_automation_version_id');
    }
}
