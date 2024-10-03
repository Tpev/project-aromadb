<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenseHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'license_tier_id', 
        'start_date', 
        'end_date', 
        'status'
    ];

    /**
     * Get the user associated with the license history.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the license tier associated with this history.
     */
    public function licenseTier()
    {
        return $this->belongsTo(LicenseTier::class);
    }
}
