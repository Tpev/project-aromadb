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
        'tax_rate', // Ajout du champ tax_rate
    ];

    /**
     * L'utilisateur (thérapeute) qui a créé le produit.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir les éléments de facture associés à ce produit.
     */
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
