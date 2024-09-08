<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Favorite;

class Tisane extends Model
{
    use HasFactory;

    protected $fillable = [
        'REF', 
        'NomTisane', 
        'NomLatin', 
        'Provenance', 
        'Properties', 
        'Indications', 
        'ContreIndications', 
        'Description'
    ];

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }
}
