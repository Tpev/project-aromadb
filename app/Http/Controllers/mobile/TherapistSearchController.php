<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Services\TherapistDirectorySearchService;
use Illuminate\Http\Request;

class TherapistSearchController extends Controller
{
    /**
     * GET /mobile/therapeutes
     * Affiche simplement le formulaire de recherche mobile.
     */
    public function index(Request $request)
    {
        // Si tu veux pré-remplir les champs plus tard, tu peux
        // passer des valeurs à la vue ici.
        return view('mobile.therapists.index');
    }

    /**
     * POST /mobile/therapeutes/rechercher
     * Traite la recherche et affiche les résultats.
     */
    public function search(Request $request, TherapistDirectorySearchService $directorySearch)
    {
        $data = $request->validate([
            'name'      => 'nullable|string',
            'specialty' => 'nullable|string',
            'location'  => 'nullable|string',
        ]);

        $specialty = $data['specialty'] ?? null;
        $region    = $data['location'] ?? null;
        $therapists = $directorySearch->search($data);

        return view('mobile.therapists.results', [
            'therapists' => $therapists,
            'specialty'  => $specialty,
            'region'     => $region,
        ]);
    }
}
