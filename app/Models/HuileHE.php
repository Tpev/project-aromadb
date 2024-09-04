<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HuileHE extends Model
{
    use HasFactory;

    protected $fillable = [
        'REF', 
        'NomHE', 
        'NomLatin', 
        'Provenance', 
        'OrganeProducteur', 
        'Sb', 
        'Properties', 
        'Indications', 
        'ContreIndications', 
        'Note', 
        'Description'
    ];
}
