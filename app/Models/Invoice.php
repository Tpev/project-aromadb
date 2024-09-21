<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = ['client_profile_id', 'user_id', 'invoice_date', 'total_amount', 'status'];

    /**
     * The user (therapist) that generated the invoice.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The client profile for the invoice.
     */
    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }
}
