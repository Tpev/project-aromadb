<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackProductItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pack_product_id',
        'product_id',
        'quantity',
        'sort_order',
    ];

    public function pack()
    {
        return $this->belongsTo(PackProduct::class, 'pack_product_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
