<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

protected $fillable = [
    'client_profile_id',
    'user_id',
    'invoice_date',
    'due_date',
    'total_amount',
    'total_tax_amount',
    'total_amount_with_tax',
    'status',
    'notes',
	'invoice_number', // Add this line
];


    /**
     * L'utilisateur (thérapeute) qui a créé la facture.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Le profil client associé à la facture.
     */
    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }

    /**
     * Obtenir les éléments de cette facture.
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
