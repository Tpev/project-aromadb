<?php

namespace App\Domain\OfferJourneys\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfferJourneyVersion extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'snapshot_json' => 'array',
        'published_at' => 'datetime',
    ];

    public function journey(): BelongsTo
    {
        return $this->belongsTo(OfferJourney::class, 'offer_journey_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by_user_id');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(OfferJourneyPageVersion::class)->orderBy('position');
    }

}
