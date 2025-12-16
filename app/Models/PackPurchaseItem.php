<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackPurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pack_purchase_id',
        'product_id',
        'quantity_total',
        'quantity_remaining',
    ];

    public function purchase()
    {
        return $this->belongsTo(PackPurchase::class, 'pack_purchase_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
