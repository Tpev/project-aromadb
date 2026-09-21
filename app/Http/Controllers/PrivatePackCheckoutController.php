<?php

namespace App\Http\Controllers;

use App\Models\PackProduct;
use App\Services\StripeAccountGuard;
use Illuminate\Http\Request;

class PrivatePackCheckoutController extends Controller
{
    private function pack(string $token): PackProduct
    {
        abort_unless(strlen($token) === 64, 404);

        $pack = PackProduct::with('user')
            ->where('private_checkout_token', $token)
            ->where('private_checkout_enabled', true)
            ->where('is_active', true)
            ->firstOrFail();

        abort_unless($pack->user?->is_therapist && filled($pack->user->slug), 404);

        return $pack;
    }

    public function show(Request $request, string $token, PublicCheckoutController $checkout, StripeAccountGuard $stripeGuard)
    {
        $pack = $this->pack($token);
        $request->attributes->set('private_pack', $pack);

        return response($checkout->show($request, $pack->user->slug, $stripeGuard))
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'private, no-store')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function store(Request $request, string $token, PublicCheckoutController $checkout, StripeAccountGuard $stripeGuard)
    {
        $pack = $this->pack($token);
        $request->attributes->set('private_pack', $pack);
        // The server binds the purchase to the pack granted by this link.
        $request->merge(['item' => 'pack:'.$pack->id]);

        return $checkout->store($request, $pack->user->slug, $stripeGuard);
    }

    public function cancel(string $token)
    {
        $pack = $this->pack($token);

        // Returning from Checkout is not proof of cancellation or failed payment.
        return redirect()->route('packs.private.show', $pack->private_checkout_token)
            ->with('warning', 'Le paiement a été interrompu. Vous pouvez réessayer.');
    }
}
