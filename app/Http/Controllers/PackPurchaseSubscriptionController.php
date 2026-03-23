<?php

namespace App\Http\Controllers;

use App\Models\PackPurchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class PackPurchaseSubscriptionController extends Controller
{
    public function cancel(Request $request, PackPurchase $packPurchase)
    {
        if ((int) $packPurchase->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $request->validate([
            'cancel_mode' => 'required|in:end_of_period,immediate',
        ]);

        if (($packPurchase->payment_mode ?? 'one_time') !== 'installments') {
            return back()->with('error', 'Cet achat n’est pas un paiement en plusieurs fois.');
        }

        $currentState = (string) ($packPurchase->payment_state ?? 'pending');
        if (in_array($currentState, ['completed', 'canceled', 'failed'], true)) {
            return back()->with('error', 'Cet abonnement ne peut plus être annulé.');
        }

        if (!$packPurchase->stripe_subscription_id) {
            return back()->with('error', 'Aucun abonnement Stripe lié à cet achat.');
        }

        $user = auth()->user();
        if (!$user->stripe_account_id) {
            return back()->with('error', 'Aucun compte Stripe Connect associé à votre profil.');
        }

        $stripe = new StripeClient((string) config('services.stripe.secret'));
        $mode = (string) $request->input('cancel_mode');

        try {
            if ($mode === 'immediate') {
                $stripe->subscriptions->cancel(
                    $packPurchase->stripe_subscription_id,
                    [],
                    ['stripe_account' => $user->stripe_account_id]
                );

                $packPurchase->update([
                    'payment_state' => 'canceled',
                    'status' => 'cancelled',
                    'canceled_requested_at' => Carbon::now(),
                    'canceled_effective_at' => Carbon::now(),
                ]);

                return back()->with('success', 'Abonnement annulé immédiatement.');
            }

            $subscription = $stripe->subscriptions->update(
                $packPurchase->stripe_subscription_id,
                ['cancel_at_period_end' => true],
                ['stripe_account' => $user->stripe_account_id]
            );

            $effectiveAt = null;
            if (!empty($subscription->current_period_end)) {
                $effectiveAt = Carbon::createFromTimestamp((int) $subscription->current_period_end);
            }

            $packPurchase->update([
                'payment_state' => 'cancel_scheduled',
                'canceled_requested_at' => Carbon::now(),
                'canceled_effective_at' => $effectiveAt,
            ]);

            return back()->with('success', 'Annulation programmée à la fin de la période en cours.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Impossible de modifier cet abonnement pour le moment.');
        }
    }
}
