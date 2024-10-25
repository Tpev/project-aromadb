<?php

// app/Http/Controllers/FormationController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FormationController extends Controller
{
    public function show($numero)
    {
        // Vérifier si la vue existe
        $viewName = 'formation.Utilisateur-Aromatherapie' . $numero;
        if (!view()->exists($viewName)) {
            abort(404);
        }

        return view($viewName, ['numero' => $numero]);
    }
}
