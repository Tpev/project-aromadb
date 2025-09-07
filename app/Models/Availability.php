<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'day_of_week',
        'start_time',
        'end_time',
        'applies_to_all',
        'practice_location_id', // ← needed so create/update() can save the location
    ];

    protected $casts = [
        'applies_to_all' => 'boolean',
        'day_of_week'    => 'integer',
    ];

    /**
     * Owner (therapist).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Products linked to this availability.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'availability_product');
    }

    /**
     * Practice location (nullable).
     */
    public function practiceLocation()
    {
        return $this->belongsTo(\App\Models\PracticeLocation::class);
    }

    /**
     * Scope used by your availability lookups (product + mode + optional location).
     */
    public function scopeForProductAndMode($q, int $userId, int $productId, int $dayOfWeek, ?int $locationId, string $mode)
    {
        $q->where('user_id', $userId)
          ->where('day_of_week', $dayOfWeek)
          ->where(function ($q) use ($productId) {
              $q->where('applies_to_all', true)
                ->orWhereHas('products', fn ($qq) => $qq->where('products.id', $productId));
          });

        if ($mode === 'cabinet') {
            // Cabinet: require the exact location
            $q->where('practice_location_id', $locationId);
        }
        // Visio / Domicile: no location filter

        return $q;
    }
}
