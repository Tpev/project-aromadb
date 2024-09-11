<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Favorite;

class Tisane extends Model
{
    use HasFactory;

    protected $fillable = [
        'REF', 
        'NomTisane',
        'NomLatin',
        'Provenance',
        'OrganeProducteur',
        'Sb',
        'Properties',
        'Indications',
        'ContreIndications',
        'Note',
        'Description',
        'slug', // Slug is fillable
    ];

    // Generate slug when saving
    public static function boot()
    {
        parent::boot();

        static::saving(function ($tisane) {
            $tisane->slug = Str::slug($tisane->NomTisane);
        });
    }

    // Relation to favorites
    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }
}
