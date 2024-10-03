<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenseTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'duration_days', 
        'is_trial', 
        'trial_duration_days', 
        'price', 
        'features'
    ];

    protected $casts = [
        'features' => 'array', // Cast features JSON to array
    ];

    /**
     * Get the licenses that are using this tier.
     */
    public function userLicenses()
    {
        return $this->hasMany(UserLicense::class);
    }

    /**
     * Get the license histories that used this tier.
     */
    public function licenseHistories()
    {
        return $this->hasMany(LicenseHistory::class);
    }
}
