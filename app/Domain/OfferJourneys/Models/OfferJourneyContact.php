<?php

namespace App\Domain\OfferJourneys\Models;

use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfferJourneyContact extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
        'last_activity_at' => 'datetime',
        'next_action_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function pipelineStage(): BelongsTo
    {
        return $this->belongsTo(OfferJourneyPipelineStage::class, 'pipeline_stage_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(OfferJourneyEntry::class);
    }

    public function consents(): HasMany
    {
        return $this->hasMany(OfferJourneyConsent::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(OfferJourneyTask::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(OfferJourneyContactActivity::class)->orderByDesc('occurred_at');
    }

    public function messageDeliveries(): HasMany
    {
        return $this->hasMany(OfferJourneyMessageDelivery::class);
    }

    public function suppressions(): HasMany
    {
        return $this->hasMany(OfferJourneySuppression::class, 'offer_journey_contact_id');
    }

    public function formAnswers(): HasMany
    {
        return $this->hasMany(OfferJourneyFormAnswer::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            OfferJourneyTag::class,
            'offer_journey_contact_tag',
            'offer_journey_contact_id',
            'offer_journey_tag_id'
        )->withTimestamps();
    }

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function getDisplayNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name) ?: ($this->email ?: 'Contact');
    }
}
