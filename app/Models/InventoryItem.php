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
    'selling_price',
    'quantity_in_stock',
    'brand',
    'unit_type',
    'quantity_per_unit',
    'quantity_remaining',
    'user_id',
	 'drop_to_ml_ratio',
];

}
