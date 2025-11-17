<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'applies_to_all',
        'practice_location_id',
    ];

    protected $casts = [
        'date'            => 'date',
        'applies_to_all'  => 'boolean',
    ];

    /**
     * Propriétaire (thérapeute).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Produits liés à cette dispo ponctuelle.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'special_availability_product');
    }

    /**
     * Lieu de pratique (cabinet) optionnel.
     */
    public function practiceLocation()
    {
        return $this->belongsTo(\App\Models\PracticeLocation::class);
    }

    /**
     * Scope helper pour retrouver les créneaux ponctuels pour un produit/mode/date.
     * (Tu pourras l'utiliser plus tard dans ton moteur de slots.)
     */
    public function scopeForProductModeAndDate($q, int $userId, int $productId, \Carbon\Carbon $date, ?int $locationId, string $mode)
    {
        $q->where('user_id', $userId)
          ->whereDate('date', $date->toDateString())
          ->where(function ($q) use ($productId) {
              $q->where('applies_to_all', true)
                ->orWhereHas('products', fn($qq) => $qq->where('products.id', $productId));
          });

        if ($mode === 'cabinet') {
            $q->where('practice_location_id', $locationId);
        }

        return $q;
    }
}
