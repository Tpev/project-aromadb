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
	
		public function relatedRecettes()
{
    // Fetch all recettes and manually filter the ones that use this HuileHE
    $recettes = Recette::all();
    $relatedRecettes = [];

    foreach ($recettes as $recette) {
        // Parse IngredientsTisane in the recette
        if ($recette->IngredientsTisane) {
            $IngredientsTisane = explode(';', $recette->IngredientsTisane);
            foreach ($IngredientsTisane as $ingredient) {
                // Ensure that we can split the ingredient into exactly two parts: quantity and REF
                $ingredientParts = explode(',', $ingredient);
                if (count($ingredientParts) === 2) {
                    list($quantity, $TisaneRef) = $ingredientParts;

                    // Check if this recette uses the current HuileHE
                    if ($TisaneRef == $this->REF) {
                        $relatedRecettes[] = $recette;
                        break; // No need to check further ingredients if a match is found
                    }
                }
            }
        }
    }

    return collect($relatedRecettes); // Return as a collection for easier handling
}
	
	
	
	
}
