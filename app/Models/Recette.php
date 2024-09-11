<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\HuileHE;
use App\Models\Favorite;

class Recette extends Model
{
    use HasFactory;

    protected $fillable = [
        'REF',
        'NomRecette',
        'slug', // Ensure slug is fillable
        'TypeApplication',
        'Ingredients',
        'Explication',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($recette) {
            $recette->slug = Str::slug($recette->NomRecette);
        });

        static::updating(function ($recette) {
            $recette->slug = Str::slug($recette->NomRecette);
        });
    }

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

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }
}
