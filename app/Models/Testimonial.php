<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'testimonial_request_id',
        'therapist_id',
        'client_profile_id',
        'testimonial',
    ];

    /**
     * Get the testimonial request that owns the testimonial.
     */
    public function testimonialRequest()
    {
        return $this->belongsTo(TestimonialRequest::class);
    }

    /**
     * Get the therapist associated with the testimonial.
     */
    public function therapist()
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }

    /**
     * Get the client profile associated with the testimonial.
     */
    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class, 'client_profile_id');
    }
}
