<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
protected $fillable = [
    'name',
    'reference',
    'description',
    'price',
	'price_per_ml',
    'selling_price',
	'selling_price_per_ml',
    'quantity_in_stock',
    'brand',
    'unit_type',
    'quantity_per_unit',
    'quantity_remaining',
    'user_id',
	 'drop_to_ml_ratio',
	 'vat_rate_purchase',
	 'vat_rate_sale',
];
public function getPriceHtAttribute()
{
    return $this->vat_rate_purchase ? $this->price / (1 + ($this->vat_rate_purchase / 100)) : $this->price;
}

public function getSellingPriceHtAttribute()
{
    return $this->vat_rate_sale ? $this->selling_price / (1 + ($this->vat_rate_sale / 100)) : $this->selling_price;
}


}
