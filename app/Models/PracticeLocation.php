<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PracticeLocation extends Model
{
    protected $fillable = [
        'user_id','label','address_line1','address_line2','postal_code','city','country','is_primary','is_shared','shared_enabled_at','latitude','longitude',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_shared' => 'boolean',
        'shared_enabled_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(PracticeLocationMember::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'practice_location_members')
            ->withPivot(['id', 'role', 'accepted_at', 'added_by_user_id'])
            ->wherePivotNotNull('accepted_at');
    }

    public function invites(): HasMany
    {
        return $this->hasMany(PracticeLocationInvite::class);
    }

    public function pendingInvites(): HasMany
    {
        return $this->hasMany(PracticeLocationInvite::class)
            ->where('status', PracticeLocationInvite::STATUS_PENDING);
    }

    public function isAccessibleBy(User $user): bool
    {
        if ((int) $this->user_id === (int) $user->id) {
            return true;
        }

        if (!$this->is_shared || !(bool) config('features.shared_cabinets_v1', false)) {
            return false;
        }

        return $this->memberships()
            ->where('user_id', $user->id)
            ->whereNotNull('accepted_at')
            ->exists();
    }

    public function getFullAddressAttribute(): string
    {
        return trim(collect([
            $this->address_line1,
            $this->address_line2,
            trim(($this->postal_code ? $this->postal_code.' ' : '').($this->city ?? '')),
            $this->country,
        ])->filter()->implode(', '));
    }
}
