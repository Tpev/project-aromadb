<?php

namespace App\Models;

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
    ];

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
