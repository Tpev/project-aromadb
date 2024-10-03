<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLicense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'license_tier_id', 
        'start_date', 
        'expiration_date'
    ];

    /**
     * Get the user who owns this license.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the license tier associated with this license.
     */
    public function licenseTier()
    {
        return $this->belongsTo(LicenseTier::class);
    }
	
}
