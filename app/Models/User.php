<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use App\Models\InventoryItem;
use App\Models\Availability;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'login_count',
        'last_login_at',
        'is_therapist',
        'company_name',
        'company_address',
        'company_email',
        'company_phone',
        'legal_mentions',
        'slug',
		'share_address_publicly',
        'share_phone_publicly',
        'share_email_publicly',
		'about',
		'services',
		'profile_description',
		'minimum_notice_hours', // Ensure this line is present
        'profile_picture',
		'accept_online_appointments', // Ensure this line is present
		'stripe_account_id',
		'stripe_customer_id', 
		'license_product',
		'license_status',

    ];

    protected $dates = ['last_login_at'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
		'accept_online_appointments' => 'boolean', // Ensure this line is present
    ];

    // Relationships

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }    
	public function availabilities()
    {
        return $this->hasMany(Availability::class);
    }

    // Add the missing clientProfiles relationship
    public function clientProfiles()
    {
        return $this->hasMany(ClientProfile::class); // Assuming one therapist can have multiple client profiles
    }

    // Additional methods

    public function isAdmin()
    {
        return $this->is_admin; // Assuming `is_admin` is a boolean field
    }

    public function isTherapist()
    {
        return $this->is_therapist; // Assuming `is_therapist` is a boolean field
    }

    /**
     * Génère un slug unique basé sur le nom de l'entreprise.
     *
     * @param string $companyName
     * @param int $userId
     * @return string|null
     */
    public static function createUniqueSlug($companyName, $userId)
    {
        if (empty($companyName)) {
            return null;
        }

        $slug = Str::slug($companyName);
        $originalSlug = $slug;
        $counter = 1;

        // Vérifier l'unicité du slug
        while (self::where('slug', $slug)->where('id', '!=', $userId)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
	
	    public function activeLicense()
    {
        return $this->hasOne(UserLicense::class);
    }

    /**
     * Get the license history for the user.
     */
    public function licenseHistories()
    {
        return $this->hasMany(LicenseHistory::class);
    }
	// User.php
public function license()
{
    return $this->hasOne(UserLicense::class);
}
public function questionnaires()
{
    return $this->hasMany(Questionnaire::class);
}
public function testimonialRequests()
{
    return $this->hasMany(TestimonialRequest::class, 'therapist_id');
}

/**
 * Get the testimonials made by the therapist.
 */
public function testimonials()
{
    return $this->hasMany(Testimonial::class, 'therapist_id');
}
    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }
}
