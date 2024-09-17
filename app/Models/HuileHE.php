<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Favorite;
use App\Models\Recette;

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
        'Description',
        'MetaDesc'
    ];
	
public function favorites()
{
    return $this->morphMany(Favorite::class, 'favoritable');
}

public function getRouteKeyName()
{
    return 'slug'; // Instead of 'id'
}

public function relatedRecettes()
{
    // Fetch all recettes and manually filter the ones that use this HuileHE
    $recettes = Recette::all();
    $relatedRecettes = [];

    foreach ($recettes as $recette) {
        // Parse IngredientsHE in the recette
        if ($recette->IngredientsHE) {
            $IngredientsHE = explode(';', $recette->IngredientsHE);
            foreach ($IngredientsHE as $ingredient) {
                // Ensure that we can split the ingredient into exactly two parts: quantity and REF
                $ingredientParts = explode(',', $ingredient);
                if (count($ingredientParts) === 2) {
                    list($quantity, $huileHERef) = $ingredientParts;

                    // Check if this recette uses the current HuileHE
                    if ($huileHERef == $this->REF) {
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
