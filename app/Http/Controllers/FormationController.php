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

    // Nombre total de diapositives (ajusté pour refléter le nouveau total)
    $totalSlides = 49; // Mettez à jour ce nombre

    return view($viewName, ['numero' => (int)$numero, 'totalSlides' => $totalSlides]);
}

}
