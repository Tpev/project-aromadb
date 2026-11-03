<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestimonialRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'therapist_id',
        'client_profile_id',
        'token',
        'status',
        'destination',
        'google_review_url',
    ];

    public function isGoogle(): bool
    {
        return $this->destination === 'google';
    }

    public function scopeForDestination(Builder $query, string $destination): Builder
    {
        return $query->where(function (Builder $query) use ($destination) {
            $query->where('destination', $destination);

            if ($destination === 'internal') {
                $query->orWhereNull('destination');
            }
        });
    }

    /**
     * Get the therapist that owns the testimonial request.
     */
    public function therapist()
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }

    /**
     * Get the client profile associated with the testimonial request.
     */
    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class, 'client_profile_id');
    }

    /**
     * Get the testimonial associated with the request.
     */
    public function testimonial()
    {
        return $this->hasOne(Testimonial::class);
    }
}
