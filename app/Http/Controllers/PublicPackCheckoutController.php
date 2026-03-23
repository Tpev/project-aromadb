<?php

namespace App\Http\Controllers;

use App\Models\DigitalTraining;
use App\Models\PackProduct;
use App\Models\PackPurchase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
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
        $meta = (array) ($session->metadata ?? []);
        if (empty($meta)) {
            $meta = (array) ($session->payment_intent->metadata ?? []);
        }

        $purchaseKind = $meta['purchase_kind'] ?? 'pack';
        $paymentMode = $meta['payment_mode'] ?? 'one_time';

        if (!empty($meta['pack_purchase_id'])) {
            $purchase = PackPurchase::find((int) $meta['pack_purchase_id']);
            if ($purchase) {
                if ($paymentMode === 'installments') {
                    $payload = [
                        'status' => $paid ? 'active' : 'pending',
                    ];

                    if (Schema::hasColumn('pack_purchases', 'stripe_subscription_id')) {
                        $payload['stripe_subscription_id'] = (string) ($session->subscription->id ?? $session->subscription ?? '');
                    }
                    if (Schema::hasColumn('pack_purchases', 'stripe_customer_id')) {
                        $payload['stripe_customer_id'] = (string) ($session->customer ?? '');
                    }
                    if (Schema::hasColumn('pack_purchases', 'payment_state')) {
                        $payload['payment_state'] = $paid ? 'active' : 'pending';
                    }
                    if ($paid && Schema::hasColumn('pack_purchases', 'activated_at')) {
                        $payload['activated_at'] = Carbon::now();
                    }
                    if ($paid && Schema::hasColumn('pack_purchases', 'purchased_at')) {
                        $payload['purchased_at'] = $purchase->purchased_at ?: Carbon::now();
                    }

                    $purchase->update($payload);
                } else {
                    $payload = [
                        'status' => $paid ? 'active' : 'failed',
                        'purchased_at' => $paid ? Carbon::now() : null,
                    ];
                    if (Schema::hasColumn('pack_purchases', 'payment_state')) {
                        $payload['payment_state'] = $paid ? 'completed' : 'failed';
                    }
                    if ($paid && Schema::hasColumn('pack_purchases', 'activated_at')) {
                        $payload['activated_at'] = Carbon::now();
                    }
                    if ($paid && Schema::hasColumn('pack_purchases', 'completed_at')) {
                        $payload['completed_at'] = Carbon::now();
                    }
                    $purchase->update($payload);
                }
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
                $payload = ['status' => 'cancelled'];
                if (Schema::hasColumn('pack_purchases', 'payment_state')) {
                    $payload['payment_state'] = 'canceled';
                }
                $purchase->update($payload);

                $therapist = User::find($purchase->user_id);
                if ($therapist?->slug) {
                    return redirect()->route('therapist.show', $therapist->slug)
                        ->with('success', 'Paiement annulé.');
                }

                return redirect('/')
                    ->with('success', 'Paiement annulé.');
            }
        }

        return redirect('/')->with('success', 'Paiement annulé.');
    }
}
