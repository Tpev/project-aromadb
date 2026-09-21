<?php

namespace App\Http\Controllers;

use App\Models\DigitalTraining;
use App\Models\PackProduct;
use App\Models\PackPurchase;
use App\Models\User;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class PublicPackCheckoutController extends Controller
{
    public function show(Request $request, string $slug, PackProduct $pack)
    {
        $item = 'pack:' . $pack->id;
        return redirect()->route('public.checkout.show', ['slug' => $slug, 'item' => $item]);
    }

    public function store(Request $request, string $slug, PackProduct $pack)
    {
        if (!$request->filled('item')) {
            $request->merge([
                'item' => 'pack:' . $pack->id,
            ]);
        }

        return app(PublicCheckoutController::class)->store($request, $slug);
    }

    public function success(Request $request)
    {
        $sessionId = (string) $request->query('session_id');
        $accountId = (string) $request->query('account_id');
        abort_unless($sessionId !== '' && $accountId !== '', 404);

        $stripe = new StripeClient(config('services.stripe.secret'));

        $session = $stripe->checkout->sessions->retrieve($sessionId, [
            'expand' => ['payment_intent', 'subscription'],
        ], [
            'stripe_account' => $accountId,
        ]);

        $paid = (($session->payment_status ?? null) === 'paid');
        $meta = $session->metadata?->toArray() ?? [];
        if (empty($meta)) {
            $meta = is_object($session->payment_intent) ? ($session->payment_intent->metadata?->toArray() ?? []) : [];
        }

        $purchaseKind = $meta['purchase_kind'] ?? 'pack';

        if (!empty($meta['pack_purchase_id'])) {
            $purchase = PackPurchase::with('user')->findOrFail((int) $meta['pack_purchase_id']);
            abort_unless($purchase->user && hash_equals((string) $purchase->user->stripe_account_id, $accountId), 404);
            abort_unless(!$purchase->stripe_session_id || hash_equals($purchase->stripe_session_id, $sessionId), 404);
            if ($paid) {
                $fulfilled = app(\App\Services\StripePurchaseWebhookService::class)->fulfillCheckoutSession($session, $accountId);
                if (!$fulfilled) {
                    return redirect()->route('therapist.show', $purchase->user->slug)
                        ->with('warning', 'Le paiement a été reçu. Contactez le praticien pour vérifier votre achat.');
                }
            }
        }

        if (!empty($meta['private_pack_id'])) {
            $privatePack = PackProduct::find((int) $meta['private_pack_id']);
            if ($privatePack && (int) $privatePack->user_id === (int) ($meta['therapist_id'] ?? 0)
                && $privatePack->private_checkout_enabled && $privatePack->is_active) {
                return redirect()->route('packs.private.show', $privatePack->private_checkout_token)
                    ->with($paid ? 'success' : 'warning', $paid ? 'Paiement confirmé. Votre achat est enregistré.' : 'La confirmation du paiement est en cours.');
            }
        }

        if ($purchaseKind === 'training' && !empty($meta['digital_training_id'])) {
            $training = DigitalTraining::find((int) $meta['digital_training_id']);

            if ($training) {
                return redirect()->route('digital-trainings.public.show', $training->slug)
                    ->with('success', $paid
                        ? 'Paiement confirmé. Votre achat est enregistré.'
                        : 'Paiement non confirmé.'
                    );
            }
        }

        $therapist = null;
        if (!empty($meta['therapist_id'])) {
            $therapist = User::find((int) $meta['therapist_id']);
        }

        if ($therapist?->slug) {
            return redirect()->route('therapist.show', $therapist->slug)
                ->with('success', $paid ? 'Paiement confirmé.' : 'Paiement non confirmé.');
        }

        return redirect('/')
            ->with('success', $paid ? 'Paiement confirmé.' : 'Paiement non confirmé.');
    }

    public function cancel(Request $request)
    {
        $purchaseId = (int) $request->query('purchase_id', 0);

        if ($purchaseId > 0) {
            $purchase = PackPurchase::find($purchaseId);
            if ($purchase && in_array($purchase->status, ['pending', 'failed'], true)) {
                // A browser return is not proof that Stripe cancelled the payment.
                // Keep this purchase fulfillable if its verified payment arrives later.

                $therapist = User::find($purchase->user_id);
                if ($therapist?->slug) {
                    return redirect()->route('therapist.show', $therapist->slug)
                        ->with('warning', 'Paiement interrompu. Vous pouvez réessayer.');
                }

                return redirect('/')
                    ->with('warning', 'Paiement interrompu.');
            }
        }

        return redirect('/')->with('warning', 'Paiement interrompu.');
    }
}
