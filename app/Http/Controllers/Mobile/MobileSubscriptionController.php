<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class MobileSubscriptionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $plans = config('license_features.plans', []);
        $family = $user?->licenseFamily() ?: 'free';
        $currentFeatures = $plans[$family] ?? [];
        $featureLabels = $this->featureLabels();

        $features = collect($featureLabels)
            ->map(fn (string $label, string $key) => [
                'key' => $key,
                'label' => $label,
                'enabled' => in_array($key, $currentFeatures, true) || $user?->canUseFeature($key),
            ])
            ->values();

        return view('mobile.subscription.index', [
            'user' => $user,
            'family' => $family,
            'planLabel' => $this->planLabel($family, $user?->license_product),
            'statusLabel' => $this->statusLabel($user?->license_status),
            'features' => $features,
            'enabledCount' => $features->where('enabled', true)->count(),
            'totalFeatureCount' => $features->count(),
        ]);
    }

    private function planLabel(?string $family, ?string $product): string
    {
        return match ($family) {
            'trial' => 'Essai',
            'free' => 'Gratuit',
            'starter' => 'Starter',
            'pro' => 'PRO',
            'premium' => 'Premium',
            'legacy' => $product ? 'Ancienne licence' : 'Licence historique',
            default => $product ?: 'Aucune offre',
        };
    }

    private function statusLabel(?string $status): string
    {
        return match ($status) {
            'active' => 'Actif',
            'trialing' => 'Essai actif',
            'past_due' => 'Paiement requis',
            'canceled', 'cancelled' => 'Annule',
            'inactive' => 'Inactif',
            null, '' => 'Non renseigne',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    private function featureLabels(): array
    {
        return [
            'client_profiles' => 'Fiches clients',
            'client_profiles_pro' => 'Clients PRO',
            'client_profile_advanced' => 'Suivi client avance',
            'espace-client' => 'Espace client',
            'appointement' => 'Agenda et RDV',
            'facturation' => 'Factures et devis',
            'livre_recettes' => 'Livre de recettes',
            'products' => 'Prestations',
            'questionnaires' => 'Questionnaires',
            'events' => 'Evenements',
            'gift_vouchers' => 'Bons cadeaux',
            'inventory' => 'Stock',
            'review' => 'Avis clients',
            'integration' => 'Integrations',
            'conseil' => 'Conseils',
            'blog' => 'Blog',
            'newsletter' => 'Newsletters',
        ];
    }
}
