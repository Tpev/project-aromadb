<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestEmail;

class TestEmailController extends Controller
{
    public function sendTestEmail()
    {
        $toEmail = 'peverelli.t@gmail.com'; // Remplacez par votre adresse e-mail

        try {
            Mail::to($toEmail)->send(new TestEmail());

            return 'E-mail de test envoyé avec succès ! Vérifiez votre boîte de réception.';
        } catch (\Exception $e) {
            return 'Échec de l\'envoi de l\'e-mail : ' . $e->getMessage();
        }
    }
}
