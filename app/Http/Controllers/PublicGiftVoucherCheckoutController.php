<?php

namespace App\Http\Controllers;

use App\Http\Requests\GiftVoucherPublicCheckoutRequest;
use App\Models\GiftVoucherOrder;
use App\Models\User;
use App\Services\GiftVoucherCheckoutService;
use App\Services\StripeAccountGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\StripeClient;

class PublicGiftVoucherCheckoutController extends Controller
{
    public function show(string $slug, StripeAccountGuard $stripeGuard)
    {
        $therapist = User::where('slug', $slug)
            ->where('is_therapist', true)
            ->firstOrFail();

        abort_unless($therapist->canUseFeature('gift_vouchers'), 404);
        abort_unless((bool) $therapist->gift_voucher_online_enabled, 404);
        abort_unless($stripeGuard->canAcceptOnlineCheckout($therapist), 404);

        return view('gift-vouchers.checkout', compact('therapist'));
    }

    public function store(
        GiftVoucherPublicCheckoutRequest $request,
        string $slug,
        StripeAccountGuard $stripeGuard
    ) {
        $therapist = User::where('slug', $slug)
            ->where('is_therapist', true)
            ->firstOrFail();

        if (! $therapist->canUseFeature('gift_vouchers')
            || ! $therapist->gift_voucher_online_enabled
            || ! $stripeGuard->canAcceptOnlineCheckout($therapist)) {
            return back()->withErrors([
                'payment' => 'L’achat en ligne de bons cadeaux n’est pas disponible pour ce profil.',
            ])->withInput();
        }

        $amountCents = (int) round(((float) $request->input('amount_eur')) * 100);

        $order = GiftVoucherOrder::create([
            'user_id' => $therapist->id,
            'amount_cents' => $amountCents,
            'currency' => 'EUR',
            'cancel_token' => Str::random(64),
            'buyer_name' => $request->input('buyer_name'),
            'buyer_email' => strtolower((string) $request->input('buyer_email')),
            'buyer_phone' => $request->input('buyer_phone'),
            'recipient_name' => $request->input('recipient_name'),
            'recipient_email' => $request->input('recipient_email'),
            'message' => $request->input('message'),
            'expires_at' => $request->input('expires_at') ?: null,
            'status' => 'pending',
        ]);

        try {
            $stripe = new StripeClient((string) config('services.stripe.secret'));
            $session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'customer_email' => $order->buyer_email,
                'metadata' => [
                    'purchase_kind' => 'gift_voucher',
                    'gift_voucher_order_id' => $order->id,
                    'therapist_id' => $therapist->id,
                ],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Bon cadeau – ' . ($therapist->company_name ?? $therapist->name ?? 'AromaMade'),
                        ],
                        'unit_amount' => $amountCents,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('gift-vouchers.checkout.success') . '?session_id={CHECKOUT_SESSION_ID}&account_id=' . $therapist->stripe_account_id . '&order_id=' . $order->id . '&token=' . $order->cancel_token,
                'cancel_url' => route('gift-vouchers.checkout.cancel') . '?order_id=' . $order->id . '&token=' . $order->cancel_token,
                'payment_intent_data' => [
                    'metadata' => [
                        'purchase_kind' => 'gift_voucher',
                        'gift_voucher_order_id' => $order->id,
                        'therapist_id' => $therapist->id,
                    ],
                ],
            ], [
                'stripe_account' => $therapist->stripe_account_id,
            ]);

            $order->stripe_session_id = $session->id;
            $order->save();

            return redirect($session->url);
        } catch (\Throwable $e) {
            Log::error('Gift voucher checkout session creation failed', [
                'therapist_id' => $therapist->id,
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'payment' => 'Erreur lors de la création de la session de paiement.',
            ])->withInput();
        }
    }

    public function success(
        Request $request,
        GiftVoucherCheckoutService $checkoutService
    ) {
        $sessionId = (string) $request->query('session_id');
        $accountId = (string) $request->query('account_id');
        $orderIdFromUrl = (int) $request->query('order_id', 0);
        $tokenFromUrl = (string) $request->query('token', '');

        if ($sessionId === '') {
            Log::warning('Gift voucher checkout success called without session id', [
                'query' => $request->query(),
            ]);

            return redirect('/')
                ->withErrors(['payment' => 'Paiement recu, verification en cours. Si besoin, contactez le praticien.']);
        }

        $order = GiftVoucherOrder::query()
            ->where('stripe_session_id', $sessionId)
            ->first();

        if (! $order && $orderIdFromUrl > 0 && $tokenFromUrl !== '') {
            $order = GiftVoucherOrder::query()
                ->whereKey($orderIdFromUrl)
                ->where('cancel_token', $tokenFromUrl)
                ->first();
        }

        if (! $order) {
            Log::warning('Gift voucher checkout success could not resolve local order', [
                'session_id' => $sessionId,
                'account_id' => $accountId,
                'order_id' => $orderIdFromUrl ?: null,
            ]);

            return redirect('/')
                ->withErrors(['payment' => 'Paiement recu, mais la commande est encore en cours de synchronisation.']);
        }

        $therapist = $order->therapist;
        if (! $therapist) {
            Log::warning('Gift voucher checkout success resolved order without therapist', [
                'order_id' => $order->id,
                'session_id' => $sessionId,
            ]);

            return redirect('/')
                ->withErrors(['payment' => 'Paiement recu, mais la commande est encore en cours de synchronisation.']);
        }

        $resolvedAccountId = $accountId !== ''
            ? $accountId
            : (string) ($therapist->stripe_account_id ?? '');

        if ($resolvedAccountId === '' || (string) $therapist->stripe_account_id !== $resolvedAccountId) {
            Log::warning('Gift voucher checkout success account mismatch', [
                'order_id' => $order->id,
                'session_id' => $sessionId,
                'query_account_id' => $accountId ?: null,
                'therapist_stripe_account_id' => $therapist->stripe_account_id,
            ]);

            return redirect()
                ->route('therapist.show', $therapist->slug)
                ->withErrors(['payment' => 'Paiement recu, verification en cours.']);
        }

        if ($order->status === 'paid' && $order->gift_voucher_id) {
            return redirect()
                ->route('therapist.show', $therapist->slug)
                ->with('success', 'Paiement confirme. Votre bon cadeau a ete envoye.');
        }

        try {
            $stripe = new StripeClient((string) config('services.stripe.secret'));
            $session = $stripe->checkout->sessions->retrieve($sessionId, [
                'expand' => ['payment_intent'],
            ], [
                'stripe_account' => $resolvedAccountId,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Gift voucher checkout success lookup failed', [
                'order_id' => $order->id,
                'session_id' => $sessionId,
                'account_id' => $resolvedAccountId,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('therapist.show', $therapist->slug)
                ->withErrors(['payment' => 'Paiement recu, votre bon cadeau est en cours de preparation.']);
        }

        if (($session->payment_status ?? null) === 'paid') {
            $paymentIntentId = '';
            if (!empty($session->payment_intent)) {
                $paymentIntentId = is_object($session->payment_intent)
                    ? (string) ($session->payment_intent->id ?? '')
                    : (string) $session->payment_intent;
            }

            $checkoutService->finalizePaidOrder(
                $order,
                (string) $session->id,
                $paymentIntentId !== '' ? $paymentIntentId : null
            );

            return redirect()
                ->route('therapist.show', $therapist->slug)
                ->with('success', 'Paiement confirmé. Votre bon cadeau a été envoyé.');
        }

        return redirect()
            ->route('gift-vouchers.checkout.show', $therapist->slug)
            ->withErrors(['payment' => 'Paiement non confirmé.']);
    }

    public function cancel(Request $request)
    {
        $orderId = (int) $request->query('order_id', 0);
        $token = (string) $request->query('token', '');
        $order = null;

        if ($orderId > 0 && $token !== '') {
            $order = GiftVoucherOrder::query()
                ->whereKey($orderId)
                ->where('cancel_token', $token)
                ->first();
        }

        if ($order && $order->status === 'pending') {
            $order->status = 'cancelled';
            $order->save();

            return redirect()
                ->route('therapist.show', $order->therapist->slug)
                ->with('success', 'Paiement annulé.');
        }

        return redirect('/')->with('success', 'Paiement annulé.');
    }
}
