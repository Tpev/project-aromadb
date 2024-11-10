<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\User;
use App\Models\Appointment;

class StripeController extends Controller
{
    public function connect(Request $request)
    {
        $stripeSecretKey = config('services.stripe.secret');
        $stripe = new StripeClient($stripeSecretKey);

        $user = Auth::user();
		
        try {
            if (!$user->stripe_account_id) {
                // Créer un nouveau compte connecté
                $account = $stripe->accounts->create([
                    'type' => 'express',
					'business_profile' => [
					'name' => $user->company_name,
					'url' => 'https://aromamade.com/pro/' . auth()->user()->slug,
					],
                ]);
				
                // Sauvegarder l'ID du compte dans le profil de l'utilisateur
                $user->stripe_account_id = $account->id;
                $user->save();
            }

            // Créer le lien d'onboarding
            $accountLink = $stripe->accountLinks->create([
                'account' => $user->stripe_account_id,
                'refresh_url' => route('stripe.refresh'),
                'return_url' => route('stripe.return'),
                'type' => 'account_onboarding',
            ]);

            // Rediriger vers la page d'onboarding hébergée par Stripe
            return redirect($accountLink->url);

        } catch (\Exception $e) {
            // Gérer les exceptions
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function refresh(Request $request)
    {
        $stripeSecretKey = config('services.stripe.secret');
        $stripe = new StripeClient($stripeSecretKey);

        $user = Auth::user();

        try {
            $accountLink = $stripe->accountLinks->create([
                'account' => $user->stripe_account_id,
                'refresh_url' => route('stripe.refresh'),
                'return_url' => route('stripe.return'),
                'type' => 'account_onboarding',
            ]);

            // Rediriger vers la page d'onboarding hébergée par Stripe
            return redirect($accountLink->url);

        } catch (\Exception $e) {
            // Gérer les exceptions
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function return(Request $request)
    {
        // Après l'onboarding, vérifier le statut du compte
        $stripeSecretKey = config('services.stripe.secret');
        $stripe = new StripeClient($stripeSecretKey);

        $user = Auth::user();

        try {
            $account = $stripe->accounts->retrieve($user->stripe_account_id, []);
            if ($account->details_submitted) {
                // Le compte est entièrement configuré
                Session::flash('success', 'Votre compte Stripe est connecté avec succès.');
                return redirect()->route('dashboard-pro'); // Changez 'dashboard' par la route appropriée
            } else {
                // La configuration du compte est incomplète
                Session::flash('error', 'La configuration de votre compte Stripe est incomplète.');
                return redirect()->route('dashboard-pro');
            }
        } catch (\Exception $e) {
            // Gérer les exceptions
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
	

      /**
     * Créer une session de paiement Stripe Checkout pour un rendez-vous.
     */
    public function createCheckoutSession(Request $request, $token)
    {
    Log::info('createCheckoutSession called with token: ' . $token);

    // Vérifier la valeur de STRIPE_SECRET
     $stripeSecretKey = config('services.stripe.secret');
	//dd($stripeSecretKey);
    Log::info('Stripe Secret Key: ' . ($stripeSecretKey ? 'Défini' : 'Non défini'));

    if (!$stripeSecretKey) {
        Log::error('La clé secrète Stripe n\'est pas définie dans le fichier .env.');
        return redirect()->back()->withErrors('Erreur de configuration du paiement.');
    }

    try {
        // Instancier StripeClient avec la clé secrète
        $stripe = new StripeClient($stripeSecretKey);
    } catch (\Exception $e) {
        Log::error('Erreur lors de l\'instanciation de StripeClient: ' . $e->getMessage());
        return redirect()->back()->withErrors('Erreur de configuration du paiement.');
    }
        // Trouver la réservation par token
        $appointment = Appointment::where('token', $token)->first();

        if (!$appointment) {
            Log::warning('Aucun rendez-vous trouvé avec le token: ' . $token);
            return redirect()->back()->withErrors('Rendez-vous invalide.');
        }

        if ($appointment->status !== 'pending') {
            Log::warning('Rendez-vous déjà payé ou en cours: ID ' . $appointment->id);
            return redirect()->back()->withErrors('Le rendez-vous a déjà été payé ou est en cours.');
        }

        // Obtenir le produit et le thérapeute
        $product = $appointment->product;
        $therapist = $product->user;

        Log::info('Therapist Stripe Account ID: ' . $therapist->stripe_account_id);

        if (!$therapist->stripe_account_id) {
            Log::error('Le thérapeute ID ' . $therapist->id . ' n\'a pas connecté son compte Stripe.');
            return redirect()->back()->withErrors('Le thérapeute n\'a pas connecté son compte Stripe.');
        }

        // Récupérer la clé secrète Stripe
        $stripeSecretKey = config('services.stripe.secret');
        if (!$stripeSecretKey) {
            Log::error('La clé secrète Stripe n\'est pas définie dans le fichier .env.');
            return redirect()->back()->withErrors('Erreur de configuration du paiement.');
        }

          // Instancier StripeClient avec la clé secrète
        try {
            $stripe = new StripeClient($stripeSecretKey);
            Log::info('StripeClient instancié avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'instanciation de StripeClient: ' . $e->getMessage());
            return redirect()->back()->withErrors('Erreur de configuration du paiement.');
        }

        try {
            // Créer la session Stripe Checkout
            $session = $stripe->checkout->sessions->create(
                [
                    'line_items' => [
                        [
                            'price_data' => [
                                'currency' => 'eur', // Changez en 'eur' si nécessaire
                                'product_data' => [
                                    'name' => $product->name,
                                ],
                                'unit_amount' => intval($product->price * 100), // montant en centimes
                            ],
                            'quantity' => 1,
                        ],
                    ],

                    'mode' => 'payment',
                    'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}&account_id=' . $therapist->stripe_account_id,
                    'cancel_url' => route('checkout.cancel'),
                    'metadata' => [
                        'appointment_id' => $appointment->id,
                        'patient_email' => $appointment->clientProfile->email,
                    ],
                ],
                [
                    'stripe_account' => $therapist->stripe_account_id, // Compte connecté du thérapeute
                ]
            );

            Log::info('Stripe Checkout session créé avec succès: ' . $session->id);

            // Sauvegarder l'ID de la session Stripe dans la réservation
            $appointment->stripe_session_id = $session->id;
            $appointment->save();

            // Rediriger vers la page de paiement Stripe
            return redirect($session->url);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de la session Stripe Checkout: ' . $e->getMessage());
            return redirect()->back()->withErrors('Erreur lors de la création de la session de paiement : ' . $e->getMessage());
        }
    }

    /**
     * Gérer le succès du paiement.
     */
public function success(Request $request)
{
    // Gérer le paiement réussi
    $session_id = $request->get('session_id');

    if (!$session_id) {
        Log::warning('ID de session manquant lors du succès du paiement.');
        return redirect()->route('dashboard-pro')->withErrors('ID de session manquant.');
    }

    // Vérifier la valeur de STRIPE_SECRET
   $stripeSecretKey = config('services.stripe.secret');
        Log::info('Stripe Secret Key: ' . ($stripeSecretKey ? 'Défini' : 'Non défini'));

    if (!$stripeSecretKey) {
        Log::error('La clé secrète Stripe n\'est pas définie dans le fichier .env.');
        return redirect()->route('welcome')->withErrors('Erreur de configuration du paiement.');
    }

    // Instancier StripeClient avec la clé secrète
    try {
        $stripe = new StripeClient($stripeSecretKey);
        Log::info('StripeClient instancié avec succès.');
    } catch (\Exception $e) {
        Log::error('Erreur lors de l\'instanciation de StripeClient: ' . $e->getMessage());
        return redirect()->route('welcome')->withErrors('Erreur de configuration du paiement.');
    }

    try {
        // Récupérer la session
        $session = $stripe->checkout->sessions->retrieve($session_id);

        // Récupérer le PaymentIntent
        $paymentIntent = $stripe->paymentIntents->retrieve($session->payment_intent);

        // Obtenir les métadonnées
        $appointment_id = $paymentIntent->metadata->appointment_id;

        // Mettre à jour le statut de la réservation
        $appointment = Appointment::find($appointment_id);
        if ($appointment) {
            $appointment->status = 'paid';
            $appointment->save();
            Log::info('Rendez-vous ID ' . $appointment->id . ' marqué comme payé.');
        } else {
            Log::warning('Aucun rendez-vous trouvé avec l\'ID: ' . $appointment_id);
        }

        return redirect()->route('welcome')->with('success', 'Paiement réussi ! Votre rendez-vous est confirmé.');

    } catch (\Exception $e) {
        Log::error('Erreur lors de la récupération des informations de paiement: ' . $e->getMessage());
        return redirect()->route('welcome')->withErrors('Erreur lors de la récupération des informations de paiement : ' . $e->getMessage());
    }
}


    /**
     * Gérer l'annulation du paiement.
     */
    public function cancel(Request $request)
    {
        // Gérer l'annulation du paiement
        return redirect()->route('home')->with('error', 'Le paiement a été annulé.');
    }

    /**
     * Gérer les Webhooks Stripe (optionnel mais recommandé).
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            return response('Invalid payload', 400);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response('Invalid signature', 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $this->handleCheckoutSessionCompleted($session);
                break;
            // ... handle other event types
            default:
                Log::info('Received unknown event type ' . $event->type);
        }

        return response('Webhook handled', 200);
    }

    /**
     * Traitement après la complétion de la session de paiement.
     */
    protected function handleCheckoutSessionCompleted($session)
    {
        // Récupérer les métadonnées
        $appointment_id = $session->metadata->appointment_id;

        // Mettre à jour le statut de la réservation
        $appointment = Appointment::find($appointment_id);
        if ($appointment) {
            $appointment->status = 'paid';
            $appointment->save();
        }
    }
	
	    public function redirectToStripeDashboard()
    {
        $user = Auth::user();

        // Vérifier si l'utilisateur a un compte Stripe connecté
        if (!$user->stripe_account_id) {
            return redirect()->route('welcome')->withErrors('Votre compte Stripe n\'est pas connecté.');
        }

        // Initialiser Stripe
        $stripeSecretKey = config('services.stripe.secret');
        $stripe = new StripeClient($stripeSecretKey);

        try {
            // Créer un lien de connexion pour le tableau de bord Stripe
            $loginLink = $stripe->accounts->createLoginLink($user->stripe_account_id);

            Log::info('Stripe Login Link créé avec succès', [
                'user_id' => $user->id,
                'stripe_account_id' => $user->stripe_account_id,
                'login_link_url' => $loginLink->url,
            ]);

            // Rediriger l'utilisateur vers le tableau de bord Stripe
            return redirect($loginLink->url);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du lien de connexion Stripe : ' . $e->getMessage(), [
                'user_id' => $user->id,
                'stripe_account_id' => $user->stripe_account_id,
            ]);

            return redirect()->route('therapist.portal')->withErrors('Impossible de générer le lien vers le tableau de bord Stripe. Veuillez réessayer plus tard.');
        }
    }
	
	
  public function portal()
    {
        $user = Auth::user();



        // Initialiser Stripe
        $stripeSecretKey = config('services.stripe.secret');
        $stripe = new StripeClient($stripeSecretKey);

        $accountStatus = 'not_connected'; // Valeurs possibles : not_connected, incomplete, connected

        if ($user->stripe_account_id) {
            try {
                $account = $stripe->accounts->retrieve($user->stripe_account_id, []);
                if ($account->details_submitted) {
                    $accountStatus = 'connected';
                } else {
                    $accountStatus = 'incomplete';
                }
            } catch (\Exception $e) {
                Log::error('Erreur lors de la récupération du compte Stripe : ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'stripe_account_id' => $user->stripe_account_id,
                ]);

                // Traiter comme si le compte n'était pas connecté
                $accountStatus = 'not_connected';
            }
        }

        return view('therapist.stripe', compact('accountStatus'));
    }
}
