<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Favorite;
use Illuminate\Support\Str;

class HuileHV extends Model
{
    use HasFactory;

    protected $table = 'huile_hvs';

    protected $fillable = [
        'REF', 
        'NomHV',
        'slug',  // Ensure slug is fillable
        'NomLatin',
        'Provenance',
        'OrganeProducteur',
        'Sb',
        'Properties',
        'Indications',
        'ContreIndications',
        'Note',
        'Description',
        'MetaDesc',
    ];

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    // Automatically generate the slug on creation or update
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($huileHV) {
            // If no slug is present, generate it from the name
            $huileHV->slug = Str::slug($huileHV->NomHV);
        });

        static::updating(function ($huileHV) {
            // Ensure slug is updated if NomHV changes
            if ($huileHV->isDirty('NomHV')) {
                $huileHV->slug = Str::slug($huileHV->NomHV);
            }
        });
    }
	
	public function relatedRecettes()
{
    // Fetch all recettes and manually filter the ones that use this HuileHE
    $recettes = Recette::all();
    $relatedRecettes = [];

    foreach ($recettes as $recette) {
        // Parse IngredientsHV in the recette
        if ($recette->IngredientsHV) {
            $IngredientsHV = explode(';', $recette->IngredientsHV);
            foreach ($IngredientsHV as $ingredient) {
                // Ensure that we can split the ingredient into exactly two parts: quantity and REF
                $ingredientParts = explode(',', $ingredient);
                if (count($ingredientParts) === 2) {
                    list($quantity, $huileHVRef) = $ingredientParts;

                    // Check if this recette uses the current HuileHE
                    if ($huileHVRef == $this->REF) {
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
