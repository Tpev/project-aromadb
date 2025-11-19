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
		        // Google / source
        'source',
        'external_review_id',
        'rating',
        'reviewer_name',
        'reviewer_profile_photo_url',
        'visible_on_public_profile',
        'external_created_at',
        'external_updated_at',
        'owner_reply',
        'owner_reply_updated_at',
    ];
	    protected $casts = [
        'visible_on_public_profile' => 'boolean',
        'external_created_at'       => 'datetime',
        'external_updated_at'       => 'datetime',
        'owner_reply_updated_at'    => 'datetime',
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
