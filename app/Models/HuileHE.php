<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Favorite;

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
	
public function favorites()
{
    return $this->morphMany(Favorite::class, 'favoritable');
}



}
