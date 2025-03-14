<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Policies\ClientProfilePolicy;

class ClientProfile extends Model
{
    use HasFactory;

    // Allow mass assignment for these fields
    protected $fillable = ['user_id', 'first_name', 'last_name', 'email', 'phone', 'address', 'birthdate', 'notes', 'first_name_billing', 'last_name_billing'];
	   // Register policy
    protected static $policies = [
        ClientProfile::class => ClientProfilePolicy::class,
    ];

    /**
     * The user that owns the client profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the appointments for the client profile.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the session notes for the client profile.
     */
    public function sessionNotes()
    {
        return $this->hasMany(SessionNote::class);
    }

    /**
     * Get the invoices for the client profile.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
	public function testimonialRequests()
{
    return $this->hasMany(TestimonialRequest::class);
}

/**
 * Get the testimonials for the client profile.
 */
public function testimonials()
{
    return $this->hasMany(Testimonial::class);
}
public function conseilsSent()
{
    return $this->belongsToMany(\App\Models\Conseil::class, 'client_conseil', 'client_profile_id', 'conseil_id')
                ->withPivot('sent_at', 'token')
                ->withTimestamps();
}
	
public function metrics()
{
    // If your foreign key is client_profile_id
    return $this->hasMany(Metric::class, 'client_profile_id');
}
public function clientFiles()
{
    return $this->hasMany(ClientFile::class);
}


}
