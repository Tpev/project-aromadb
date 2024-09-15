<?php
namespace App\Http\Controllers;

use App\Models\Recette;
use Illuminate\Http\Request;

	class RecetteController extends Controller
	{
		public function index()
		{
			$recettes = Recette::all();
			return view('recette.index', compact('recettes'));
		}

		public function create()
		{
			return view('recette.create');
		}

		public function store(Request $request)
		{
			$data = $request->validate([
				'REF' => 'required|string|max:255|unique:recettes',
				'NomRecette' => 'required|string|max:255',
				'TypeApplication' => 'required|string|max:255',
				'Ingredients' => 'required|string',
				'Explication' => 'required|string',
			]);

			$data['slug'] = Str::slug($data['NomRecette']);

			Recette::create($data);

			return redirect()->route('recettes.index')->with('success', 'Recette created successfully.');
		}

public function show($slug)
{
    // Retrieve the Recette by its slug
    $recette = Recette::where('slug', $slug)->firstOrFail();

    // Retrieve the parsed ingredients for each type
    $parsedIngredients = $recette->parsed_ingredients;

    // Gather all Contre Indications from the parsed ingredients
    $all_contre_indications = [];
    foreach ($parsedIngredients as $type => $ingredients) {
        foreach ($ingredients as $ingredient) {
            // Check for ContreIndications in HuileHE, HuileHV, and Tisane
            if (isset($ingredient['huile']) && !empty($ingredient['huile']->ContreIndications) && $ingredient['huile']->ContreIndications !== 'None') {
                $all_contre_indications = array_merge($all_contre_indications, explode(';', $ingredient['huile']->ContreIndications));
            } elseif (isset($ingredient['tisane']) && !empty($ingredient['tisane']->ContreIndications) && $ingredient['tisane']->ContreIndications !== 'None') {
                $all_contre_indications = array_merge($all_contre_indications, explode(';', $ingredient['tisane']->ContreIndications));
            }
        }
    }

    // Remove duplicate Contre Indications
    $unique_contre_indications = array_unique($all_contre_indications);

    // Return the view with parsed data and contre indications
    return view('recette.show', [
        'recette' => $recette,
        'parsed_ingredients_he' => $parsedIngredients['IngredientsHE'],
        'parsed_ingredients_hv' => $parsedIngredients['IngredientsHV'],
        'parsed_ingredients_tisane' => $parsedIngredients['IngredientsTisane'],
        'all_contre_indications' => $unique_contre_indications,
    ]);
}



		public function edit(Recette $recette)
		{
			return view('recette.edit', compact('recette'));
		}

		public function update(Request $request, Recette $recette)
		{
			$data = $request->validate([
				'REF' => 'required|string|max:255|unique:recettes,REF,'.$recette->id,
				'NomRecette' => 'required|string|max:255',
				'TypeApplication' => 'required|string|max:255',
				'Ingredients' => 'required|string',
				'Explication' => 'required|string',
			]);

			$data['slug'] = Str::slug($data['NomRecette']);

			$recette->update($data);

			return redirect()->route('recettes.index')->with('success', 'Recette updated successfully.');
		}

		public function destroy(Recette $recette)
		{
			$recette->delete();
			return redirect()->route('recettes.index')->with('success', 'Recette deleted successfully.');
		}
	}
