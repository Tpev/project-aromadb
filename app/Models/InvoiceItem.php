<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Product;
use App\Models\Invoice;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'total_price',
    ];

    /**
     * Define the relationship to the Invoice model
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Define the relationship to the Product model
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
