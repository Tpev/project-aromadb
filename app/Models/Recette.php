<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\HuileHE;
use App\Models\HuileHV;
use App\Models\Tisane;
use App\Models\Favorite;

class Recette extends Model
{
    use HasFactory;

    protected $fillable = [
        'REF',
        'NomRecette',
        'slug', // Ensure slug is fillable
        'TypeApplication',
        'IngredientsHE',
        'IngredientsHV',
        'IngredientsTisane',
        'Explication',
    ];

    // Automatically generate slug on creating or updating
    public static function boot()
    {
        parent::boot();


    }

public function getParsedIngredientsAttribute()
{
    $parsedIngredients = [
        'IngredientsHE' => [],
        'IngredientsHV' => [],
        'IngredientsTisane' => [],
    ];

    // Parse IngredientsHE
    if ($this->IngredientsHE) {
        $IngredientsHE = explode(';', $this->IngredientsHE);
        foreach ($IngredientsHE as $ingredient) {
            list($quantity, $huileHERef) = explode(',', $ingredient);
            $huileHE = HuileHE::where('REF', $huileHERef)->first();
            $parsedIngredients['IngredientsHE'][] = [
                'quantity' => $quantity,
                'huile' => $huileHE,
            ];
        }
    }

    // Parse IngredientsHV
    if ($this->IngredientsHV) {
        $IngredientsHV = explode(';', $this->IngredientsHV);
        foreach ($IngredientsHV as $ingredient) {
            list($quantity, $huileHVRef) = explode(',', $ingredient);
            $huileHV = HuileHV::where('REF', $huileHVRef)->first();
            $parsedIngredients['IngredientsHV'][] = [
                'quantity' => $quantity,
                'huile' => $huileHV,
            ];
        }
    }

    // Parse IngredientsTisane
    if ($this->IngredientsTisane) {
        $IngredientsTisane = explode(';', $this->IngredientsTisane);
        foreach ($IngredientsTisane as $ingredient) {
            list($quantity, $tisaneRef) = explode(',', $ingredient);
            $tisane = Tisane::where('REF', $tisaneRef)->first();
            $parsedIngredients['IngredientsTisane'][] = [
                'quantity' => $quantity,
                'tisane' => $tisane,
            ];
        }
    }

    return $parsedIngredients;
}


    // The relationship for favorites (morphable)
    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }
}
