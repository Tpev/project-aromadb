<?php

namespace App\Domain\OfferJourneys\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfferJourneyAutomationVersion extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'definition_json' => 'array',
        'published_at' => 'datetime',
    ];

    public function automation(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyAutomation::class, 'offer_journey_automation_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by_user_id');
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(OfferJourneyAutomationNode::class)->orderBy('position_y');
    }
}
