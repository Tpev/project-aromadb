<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'price',
        'tax_rate',
        'is_active',
        'visible_in_portal',
        'price_visible_in_portal',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'visible_in_portal' => 'boolean',
        'price_visible_in_portal' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PackProductItem::class)->orderBy('sort_order');
    }

    public function purchases()
    {
        return $this->hasMany(PackPurchase::class);
    }

    public function getPriceInclTaxAttribute()
    {
        return $this->price + ($this->price * $this->tax_rate / 100);
    }
}
