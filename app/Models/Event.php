<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'start_date_time',
        'duration',
        'booking_required',
        'limited_spot',
        'number_of_spot',
        'associated_product',
        'image',
        'showOnPortail',
        'location',
    ];

    /**
     * Get the user that owns the event.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the associated product.
     */
    public function associatedProduct()
    {
        return $this->belongsTo(Product::class, 'associated_product');
    }
	public function reservations()
{
    return $this->hasMany(Reservation::class);
}

}
