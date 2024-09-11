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
        $recette = Recette::where('slug', $slug)->firstOrFail();
        return view('recette.show', compact('recette'));
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
