<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HuileHE;

class Recette extends Model
{
    use HasFactory;

    protected $fillable = [
        'REF',
        'NomRecette',
        'TypeApplication',
        'Ingredients',
        'Explication',
    ];

    /**
     * Get the parsed ingredients with full HuileHE details.
     *
     * @return array
     */
    public function getParsedIngredientsAttribute()
    {
        $ingredients = explode(';', $this->Ingredients);
        $parsedIngredients = [];

        foreach ($ingredients as $ingredient) {
            list($quantity, $huileHERef) = explode(',', $ingredient);
            $huileHE = HuileHE::where('REF', $huileHERef)->first();
            $parsedIngredients[] = [
                'quantity' => $quantity,
                'huileHE' => $huileHE,
            ];
        }

        return $parsedIngredients;
    }
}
