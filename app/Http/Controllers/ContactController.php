<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    /**
     * Afficher le formulaire de contact.
     */
    public function show()
    {
        return view('contact');
    }

    /**
     * Gérer l'envoi du formulaire.
     */
    public function send(Request $request)
    {
        // Valider les données du formulaire
        $validatedData = $request->validate([
            'name'    => 'required|max:255',
            'email'   => 'required|email|max:255',
            'subject' => 'required|max:255',
            'message' => 'required',
        ], [
            'name.required'    => 'Le champ Nom est obligatoire.',
            'email.required'   => 'Le champ Email est obligatoire.',
            'email.email'      => 'Veuillez fournir une adresse email valide.',
            'subject.required' => 'Le champ Sujet est obligatoire.',
            'message.required' => 'Le champ Message est obligatoire.',
        ]);

        // Envoyer l'email
        Mail::to('helpdesk.11qc1w@zapiermail.com')->queue(new ContactMail($validatedData));

        // (Optionnel) Stocker le message dans la base de données si vous le souhaitez

        // Rediriger avec un message de succès
        return redirect()->route('contact.show')->with('success', 'Votre message a été envoyé avec succès !');
    }
}
