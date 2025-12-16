<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PackProduct;
use App\Models\PackPurchase;
use App\Models\ClientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class PublicPackCheckoutController extends Controller
{
    public function show(string $slug, PackProduct $pack)
    {
        $therapist = User::where('slug', $slug)
            ->where('is_therapist', true)
            ->firstOrFail();

        abort_unless($pack->user_id === $therapist->id, 404);
        abort_unless(($pack->is_active ?? true) && ($pack->visible_in_portal !== false), 404);

        $pack->load(['items.product']);

        $taxRate = (float) ($pack->tax_rate ?? 0);
        $packPriceTtc = (float) ($pack->price_incl_tax ?? ($pack->price + ($pack->price * $taxRate / 100)));

        $unitTotalTtc = (float) $pack->items->sum(function($it) {
            $p = $it->product;
            if (!$p) return 0;

            $pTax = (float) ($p->tax_rate ?? 0);
            $pTtc = (float) ($p->price_incl_tax ?? ($p->price + ($p->price * $pTax / 100)));

            return $pTtc * (int) ($it->quantity ?? 1);
        });

        $saving = max(0, $unitTotalTtc - $packPriceTtc);
        $savingPct = $unitTotalTtc > 0 ? (int) round(($saving / $unitTotalTtc) * 100) : null;

        return view('packs.checkout', compact('therapist', 'pack', 'packPriceTtc', 'unitTotalTtc', 'saving', 'savingPct'));
    }

    public function store(Request $request, string $slug, PackProduct $pack)
    {
        $therapist = User::where('slug', $slug)
            ->where('is_therapist', true)
            ->firstOrFail();

        abort_unless($pack->user_id === $therapist->id, 404);
        abort_unless(($pack->is_active ?? true) && ($pack->visible_in_portal !== false), 404);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'phone'      => 'nullable|string|max:20',
            'notes'      => 'nullable|string|max:2000',
        ], [
            'first_name.required' => 'Le prénom est requis.',
            'last_name.required'  => 'Le nom est requis.',
            'email.required'      => 'L’email est requis.',
            'email.email'         => 'Veuillez fournir une adresse e-mail valide.',
        ]);

        $pack->load(['items']); // PackProductItem lines (qty + product_id)

        // 1) Create/merge ClientProfile by email (same pattern as booking)
        $clientProfile = ClientProfile::firstOrCreate(
            [
                'email'   => $request->email,
                'user_id' => $therapist->id,
            ],
            [
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'phone'      => $request->phone,
                'notes'      => $request->notes,
            ]
        );

        // Optionnel: compléter si vide
        $clientProfile->update([
            'first_name' => $clientProfile->first_name ?: $request->first_name,
            'last_name'  => $clientProfile->last_name  ?: $request->last_name,
            'phone'      => $clientProfile->phone      ?: $request->phone,
        ]);

        // 2) Create PackPurchase + its credit lines atomically
        $purchase = DB::transaction(function () use ($therapist, $pack, $clientProfile, $request) {

            $purchase = PackPurchase::create([
                'user_id'          => $therapist->id,
                'pack_product_id'  => $pack->id,
                'client_profile_id'=> $clientProfile->id,
                'status'           => 'pending',     // becomes 'active' on success
                'notes'            => $request->notes,
                'purchased_at'     => null,
                'expires_at'       => null,          // tu pourras mettre une durée plus tard
            ]);

            // Create pack_purchase_items from pack_product_items
            foreach ($pack->items as $line) {
                $qty = (int) ($line->quantity ?? 0);
                if ($qty <= 0) continue;

                $purchase->items()->create([
                    'product_id'          => (int) $line->product_id,
                    'quantity_total'      => $qty,
                    'quantity_remaining'  => $qty,
                ]);
            }

            return $purchase;
        });

        // 3) Stripe Checkout (Connect) — same logic style as your appointment flow
        if ($therapist->stripe_account_id) {
            try {
                $taxRate = (float) ($pack->tax_rate ?? 0);
                $amountHt = (float) ($pack->price ?? 0);
                $amountTtc = (float) ($pack->price_incl_tax ?? ($amountHt + ($amountHt * $taxRate / 100)));

                $stripe = new StripeClient(config('services.stripe.secret'));

                $session = $stripe->checkout->sessions->create([
                    'payment_method_types' => ['card'],
                    'customer_email' => $request->email,
                    'line_items' => [[
                        'price_data' => [
                            'currency'     => 'eur',
                            'product_data' => [
                                'name' => 'Pack : ' . $pack->name,
                            ],
                            'unit_amount'  => (int) round($amountTtc * 100),
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => route('packs.checkout.success') . '?session_id={CHECKOUT_SESSION_ID}&account_id=' . $therapist->stripe_account_id,
                    'cancel_url'  => route('packs.checkout.cancel') . '?purchase_id=' . $purchase->id,
                    'payment_intent_data' => [
                        'metadata' => [
                            'pack_purchase_id' => $purchase->id,
                            'pack_product_id'  => $pack->id,
                            'therapist_id'     => $therapist->id,
                            'client_profile_id'=> $clientProfile->id,
                            'customer_email'   => $request->email,
                        ],
                    ],
                ], [
                    'stripe_account' => $therapist->stripe_account_id,
                ]);

                // IMPORTANT: add this column if you don't already have it on pack_purchases
                // (otherwise remove this and use a separate table/metadata-only)
                $purchase->stripe_session_id = $session->id;
                $purchase->save();

                return redirect($session->url);

            } catch (\Exception $e) {
                Log::error('Pack Stripe Checkout creation failed: ' . $e->getMessage());

                // Keep purchase pending (or mark failed if you prefer)
                return back()->withErrors([
                    'payment' => 'Erreur lors de la création de la session de paiement. Veuillez réessayer.'
                ])->withInput();
            }
        }

        // 4) No stripe connected: activate immediately (same spirit as booking fallback)
        $purchase->update([
            'status'       => 'active',
            'purchased_at' => Carbon::now(),
        ]);

        return redirect()->route('therapist.show', $therapist->slug)
            ->with('success', 'Pack acheté avec succès.');
    }

    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        $accountId = $request->query('account_id');

        abort_unless($sessionId && $accountId, 404);

        $stripe = new StripeClient(config('services.stripe.secret'));

        // Retrieve the checkout session on the connected account
        $session = $stripe->checkout->sessions->retrieve($sessionId, [], [
            'stripe_account' => $accountId,
        ]);

        // Find purchase by session id
        $purchase = PackPurchase::where('stripe_session_id', $sessionId)->firstOrFail();

        if (($session->payment_status ?? null) === 'paid') {
            $purchase->update([
                'status'       => 'active',
                'purchased_at' => Carbon::now(),
            ]);
        } else {
            $purchase->update(['status' => 'failed']);
        }

        $therapist = User::find($purchase->user_id);

        return redirect()->route('therapist.show', $therapist?->slug ?? '/')
            ->with('success', $purchase->status === 'active'
                ? 'Paiement confirmé. Pack activé.'
                : 'Paiement non confirmé.'
            );
    }

    public function cancel(Request $request)
    {
        $purchaseId = $request->query('purchase_id');
        abort_unless($purchaseId, 404);

        $purchase = PackPurchase::findOrFail($purchaseId);
        $purchase->update(['status' => 'cancelled']);

        $therapist = User::find($purchase->user_id);

        return redirect()->route('therapist.show', $therapist?->slug ?? '/')
            ->with('success', 'Paiement annulé.');
    }
}
