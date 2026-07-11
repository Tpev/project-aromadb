<?php

namespace App\Domain\OfferJourneys\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfferJourney extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'show_on_profile' => 'boolean',
        'published_at' => 'datetime',
        'paused_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(OfferJourneyPage::class)->orderBy('position');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(OfferJourneyVersion::class)->orderByDesc('version_number');
    }

    public function publishedVersion(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyVersion::class, 'published_version_id');
    }

    public function transitions(): HasMany
    {
        return $this->hasMany(OfferJourneyTransition::class)->orderBy('priority');
    }

    public function automations(): HasMany
    {
        return $this->hasMany(OfferJourneyAutomation::class);
    }

    public function campaignLinks(): HasMany
    {
        return $this->hasMany(OfferJourneyCampaignLink::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(OfferJourneyEvent::class);
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(OfferJourneyConversion::class);
    }

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_version_id');
    }
}
