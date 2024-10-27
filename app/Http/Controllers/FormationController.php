<?php

// app/Http/Controllers/FormationController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\FormationStartedMail;
use App\Mail\FormationCompletedMail;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

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

		$adminEmails = User::where('is_admin', true)->pluck('email')->toArray();
        // Envoyer un email pour le début (slide 1) et la fin (slide 49) de la formation
        if ($numero == 1) {
            Mail::to($adminEmails)->queue(new FormationStartedMail());
        } elseif ($numero == 49) {
            Mail::to($adminEmails)->queue(new FormationCompletedMail());
        }

    return view($viewName, ['numero' => (int)$numero, 'totalSlides' => $totalSlides]);
}

}
