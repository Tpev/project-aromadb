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

    /**
     * NEW LICENSE DEFINITIONS — Add nothing else here unless needed
     */
    public const NEW_LICENSE_PRODUCTS = [
        'new_trial',
        'new_free',
        'new_starter_mensuelle',
        'new_pro_mensuelle',
        'new_premium_mensuelle',
        'new_starter_annuelle',
        'new_pro_annuelle',
        'new_premium_annuelle',
    ];

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
        'minimum_notice_hours',
        'profile_picture',
        'accept_online_appointments',
        'stripe_account_id',
        'stripe_customer_id',
        'license_product',
        'license_status',
        'view_count',
        'verified',
        'visible_annuarire_admin_set',
		    'google_event_color_id',

        // Address Fields (Admin)
        'street_address_setByAdmin',
        'address_line2_setByAdmin',
        'city_setByAdmin',
        'state_setByAdmin',
        'postal_code_setByAdmin',
        'country_setByAdmin',
        'latitude_setByAdmin',
        'longitude_setByAdmin',

        // Google Calendar
        'google_access_token',
        'google_refresh_token',
        'google_token_expires_at',

        // Featured Therapist
        'is_featured',
        'featured_until',
        'featured_weight',

        'buffer_time_between_appointments',
        'cgv_pdf_path',
		'onboarding_mode',
    ];

    protected $dates = ['last_login_at'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'accept_online_appointments' => 'boolean',
        'is_featured' => 'boolean',
        'featured_until' => 'datetime',
        'buffer_time_between_appointments' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
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

    public function clientProfiles()
    {
        return $this->hasMany(ClientProfile::class);
    }

    public function isAdmin()
    {
        return $this->is_admin;
    }

    public function isTherapist()
    {
        return $this->is_therapist;
    }

    public static function createUniqueSlug($companyName, $userId)
    {
        if (empty($companyName)) return null;

        $slug = Str::slug($companyName);
        $originalSlug = $slug;
        $counter = 1;

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

    public function licenseHistories()
    {
        return $this->hasMany(LicenseHistory::class);
    }

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

    public function testimonials()
    {
        return $this->hasMany(Testimonial::class, 'therapist_id');
    }

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function informationRequests()
    {
        return $this->hasMany(\App\Models\InformationRequest::class, 'therapist_id');
    }

    public function practiceLocations()
    {
        return $this->hasMany(\App\Models\PracticeLocation::class);
    }

    public function scopeTherapists($q)
    {
        return $q->where('is_therapist', true);
    }

    public function scopeCurrentlyFeatured($q)
    {
        return $q->where('is_featured', true)
            ->where(function ($q) {
                $q->whereNull('featured_until')
                  ->orWhere('featured_until', '>', now());
            });
    }

    public function scopeFeaturedOrdered($q)
    {
        return $q->orderByDesc('featured_weight')
                 ->orderByDesc('average_rating')
                 ->latest('id');
    }

    public function isFeatured(): bool
    {
        return (bool) $this->is_featured &&
            (is_null($this->featured_until) || $this->featured_until->isFuture());
    }


    /*
    |--------------------------------------------------------------------------
    | LICENSE LOGIC (NEW)
    |--------------------------------------------------------------------------
    */

    /**
     * Legacy = any user NOT using a new license_product.
     */
    public function isLegacyLicense(): bool
    {
        if (empty($this->license_product)) {
            return true;
        }

        return ! in_array($this->license_product, self::NEW_LICENSE_PRODUCTS, true);
    }

    /**
     * Returns: legacy | free | trial | starter | pro | premium
     */
    public function licenseFamily(): ?string
    {
        if ($this->isLegacyLicense()) {
            return 'legacy';
        }

        return match (true) {
            str_starts_with($this->license_product, 'new_free')    => 'free',
            str_starts_with($this->license_product, 'new_trial')   => 'trial',
            str_starts_with($this->license_product, 'new_starter') => 'starter',
            str_starts_with($this->license_product, 'new_pro')     => 'pro',
            str_starts_with($this->license_product, 'new_premium') => 'premium',
            default                                                => null,
        };
    }

    /**
     * Check if user can access a feature using the config map.
     */
    public function canUseFeature(string $feature): bool
    {
        if ($this->isLegacyLicense()) {
            return true; // legacy gets everything
        }

        $family = $this->licenseFamily();
        $plans = config('license_features.plans');

        return in_array($feature, $plans[$family] ?? [], true);
    }
}
