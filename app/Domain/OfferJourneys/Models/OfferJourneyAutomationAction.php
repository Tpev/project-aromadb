<?php

namespace App\Domain\OfferJourneys\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferJourneyAutomationAction extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['payload_json' => 'array', 'executed_at' => 'datetime', 'failed_at' => 'datetime'];

    public function run(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyAutomationRun::class, 'offer_journey_automation_run_id');
    }
}
