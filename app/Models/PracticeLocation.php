<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PracticeLocation extends Model
{
    protected $fillable = [
        'user_id','label','address_line1','address_line2','postal_code','city','country','is_primary',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
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
