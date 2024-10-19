<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HuileHE;
use App\Models\HuileHV;
use App\Models\Tisane;
use App\Models\Recette;
use App\Models\BlogPost;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json(['error' => 'No query provided.'], 400);
        }

        // Search across all models
        $huileHEs = HuileHE::where('NomHE', 'like', "%{$query}%")
            ->orWhere('NomLatin', 'like', "%{$query}%")
            ->get(['id', 'NomHE', 'NomLatin', 'slug']);

        $huileHVs = HuileHV::where('NomHV', 'like', "%{$query}%")
            ->orWhere('NomLatin', 'like', "%{$query}%")
            ->get(['id', 'NomHV', 'NomLatin', 'slug']);

        $tisanes = Tisane::where('Nom', 'like', "%{$query}%")
            ->get(['id', 'Nom', 'slug']);

        $recettes = Recette::where('NomRecette', 'like', "%{$query}%")
            ->get(['id', 'NomRecette', 'slug']);

        $articles = BlogPost::where('Title', 'like', "%{$query}%")
            ->get(['id', 'Title', 'slug']);

        // Add type to each result
        $huileHEs = $huileHEs->map(function ($item) {
            $item->type = 'Huile Essentielle';
            return $item;
        });

        $huileHVs = $huileHVs->map(function ($item) {
            $item->type = 'Huile Végétale';
            return $item;
        });

        $tisanes = $tisanes->map(function ($item) {
            $item->type = 'Tisane';
            return $item;
        });

        $recettes = $recettes->map(function ($item) {
            $item->type = 'Recette';
            return $item;
        });

        $articles = $articles->map(function ($item) {
            $item->type = 'Article';
            return $item;
        });

        return response()->json([
            'huileHEs' => $huileHEs,
            'huileHVs' => $huileHVs,
            'tisanes' => $tisanes,
            'recettes' => $recettes,
            'articles' => $articles,
        ]);
    }
}
