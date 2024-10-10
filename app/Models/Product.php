<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'price',
        'tax_rate',
        'duration',
        'can_be_booked_online',
        'visio',
        'adomicile',
        'dans_le_cabinet',
        'max_per_day',
    ];

    /**
     * The user (therapist) that created the product.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the invoice items associated with the product.
     */
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * The availabilities associated with the product.
     */
    public function availabilities()
    {
        return $this->belongsToMany(Availability::class);
    }
}
