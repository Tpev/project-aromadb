<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'day_of_week', 'start_time', 'end_time', 'applies_to_all'];

    /**
     * The user (therapist) that owns the availability.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The products associated with the availability.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}
