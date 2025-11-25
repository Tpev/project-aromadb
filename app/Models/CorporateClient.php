<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorporateClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'trade_name',
        'siret',
        'vat_number',
        'billing_address',
        'billing_zip',
        'billing_city',
        'billing_country',
        'billing_email',
        'billing_phone',
        'main_contact_first_name',
        'main_contact_last_name',
        'main_contact_email',
        'main_contact_phone',
        'notes',
    ];

    /**
     * Thérapeute propriétaire.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Personnes (clients individuels) liées à cette entreprise.
     */
    public function clientProfiles()
    {
        return $this->hasMany(ClientProfile::class, 'company_id');
    }
}
