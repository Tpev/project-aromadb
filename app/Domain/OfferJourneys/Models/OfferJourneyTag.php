<?php

namespace App\Domain\OfferJourneys\Models;

use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OfferJourneyTag extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_system' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(
            OfferJourneyContact::class,
            'offer_journey_contact_tag',
            'offer_journey_tag_id',
            'offer_journey_contact_id'
        )->withTimestamps();
    }

    public function clientProfiles(): BelongsToMany
    {
        return $this->belongsToMany(
            ClientProfile::class,
            'client_profile_offer_journey_tag',
            'offer_journey_tag_id',
            'client_profile_id'
        )->withTimestamps();
    }

}
