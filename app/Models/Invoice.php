<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\ClientProfile;

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
    	'invoice_number',
    	'sent_at',	// Add this line
		'payment_link', // Add this line
		'type',
		'quote_number',
    ];

protected $attributes = [
    'type' => 'invoice',
];

public function isQuote()
{
    return $this->type === 'quote';
}

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
public function appointment()
{
    return $this->belongsTo(Appointment::class);
}

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'sent_at' => 'datetime',
    ];
	
	
public function receipts()
{
    return $this->hasMany(Receipt::class);
}

public function getTotalEncaisseAttribute(): float
{
    // total TTC encaissé = somme des credits - debits
    $credit = $this->receipts()->where('direction','credit')->sum('amount_ttc');
    $debit  = $this->receipts()->where('direction','debit')->sum('amount_ttc');
    return (float) ($credit - $debit);
}

public function getSoldeRestantAttribute(): float
{
    $ttc = (float) $this->total_amount_with_tax;
    return max(0, $ttc - $this->total_encaisse);
}	
	
}
