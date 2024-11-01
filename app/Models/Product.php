<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\InvoiceItem;
use App\Models\User;


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
        'image',       // Add this line
        'brochure',    // Add this line
		'display_order', // Add this line
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
        return $this->belongsToMany(Availability::class, 'availability_product');
    }
	public function getConsultationModes()
{
    $modes = [];

    if ($this->visio) {
        $modes[] = 'En Visio';
    }
    if ($this->adomicile) {
        $modes[] = 'À Domicile';
    }
    if ($this->dans_le_cabinet) {
        $modes[] = 'Dans le Cabinet';
    }

    return empty($modes) ? 'Non spécifié' : implode(', ', $modes);
}

	
}
