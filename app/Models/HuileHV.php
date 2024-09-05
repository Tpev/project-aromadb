<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HuileHV extends Model
{
    use HasFactory;

	 protected $table = 'huile_hvs';

    protected $fillable = [
	    'REF', 
        'NomHV',
        'NomLatin',
        'Provenance',
        'OrganeProducteur',
        'Sb',
        'Properties',
        'Indications',
        'ContreIndications',
        'Note',
        'Description',
    ];
}
