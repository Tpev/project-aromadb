<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

protected $fillable = [
    'invoice_id',
    'product_id',
    'description',
    'quantity',
    'unit_price',
    'tax_rate',
    'tax_amount',
    'total_price',
    'total_price_with_tax',
];


    /**
     * Obtenir la facture associée à cet élément.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Obtenir le produit associé à cet élément (le cas échéant).
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
