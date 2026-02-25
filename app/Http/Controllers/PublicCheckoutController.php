<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PackProduct;
use App\Models\PackPurchase;
use App\Models\ClientProfile;
use App\Models\DigitalTraining;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Stripe\StripeClient;

class PublicCheckoutController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $therapist = User::where('slug', $slug)
            ->where('is_therapist', true)
            ->firstOrFail();

        // Packs for dropdown
        $packs = PackProduct::where('user_id', $therapist->id)
            ->where(function ($q) {
                $q->whereNull('is_active')->orWhere('is_active', true);
            })
            ->where(function ($q) {
                $q->whereNull('visible_in_portal')->orWhere('visible_in_portal', '!=', false);
            })
            ->with(['items.product'])
            ->orderBy('name')
            ->get();

        // Trainings for dropdown (SAFE: only filter if columns exist)
        $trainingsQuery = DigitalTraining::query()->where('user_id', $therapist->id);

        if (Schema::hasColumn('digital_trainings', 'visibility')) {
            $trainingsQuery->whereIn('visibility', ['public']);
        }

        if (Schema::hasColumn('digital_trainings', 'published_at')) {
            $trainingsQuery->whereNotNull('published_at')->orderByDesc('published_at');
        } elseif (Schema::hasColumn('digital_trainings', 'created_at')) {
            $trainingsQuery->orderByDesc('created_at');
        }

        if (Schema::hasColumn('digital_trainings', 'status')) {
            $trainingsQuery->whereIn('status', ['published', 'active']);
        }

        $trainings = $trainingsQuery->get();

        // Selected item from ?item=
        $selectedType = null;
        $selectedId   = null;

        $itemParam = (string) $request->query('item', '');
        if (preg_match('/^(pack|training):(\d+)$/', $itemParam, $m)) {
            $selectedType = $m[1];
            $selectedId   = (int) $m[2];
        }

        // Default selection if missing/invalid: first pack, else first training
        if (!$selectedType || !$selectedId) {
            if ($packs->count()) {
                $selectedType = 'pack';
                $selectedId   = (int) $packs->first()->id;
            } elseif ($trainings->count()) {
                $selectedType = 'training';
                $selectedId   = (int) $trainings->first()->id;
            } else {
                abort(404); // nothing buyable
            }
        }

        // Compute details
        $pack = null;
        $packPriceTtc = null;
        $unitTotalTtc = null;
        $saving = null;
        $savingPct = null;

        $training = null;
        $trainingPriceStr = null;

        if ($selectedType === 'pack') {
            $pack = $packs->firstWhere('id', $selectedId);
            abort_unless($pack, 404);

            $pack->loadMissing(['items.product']);

            $taxRate = (float) ($pack->tax_rate ?? 0);
            $packPriceTtc = (float) ($pack->price_incl_tax ?? ($pack->price + ($pack->price * $taxRate / 100)));

            $unitTotalTtc = (float) $pack->items->sum(function ($it) {
                $p = $it->product;
                if (!$p) return 0;

                $pTax = (float) ($p->tax_rate ?? 0);
                $pTtc = (float) ($p->price_incl_tax ?? ($p->price + ($p->price * $pTax / 100)));

                return $pTtc * (int) ($it->quantity ?? 1);
            });

            $saving = max(0, $unitTotalTtc - $packPriceTtc);
            $savingPct = $unitTotalTtc > 0 ? (int) round(($saving / $unitTotalTtc) * 100) : null;
        } else {
            $training = $trainings->firstWhere('id', $selectedId);
            abort_unless($training, 404);

            $isFree = (bool) ($training->is_free ?? false);
            if (!$isFree && !is_null($training->price_cents)) {
                $trainingPriceStr = number_format($training->price_cents / 100, 2, ',', ' ') . ' €';
            }
        }

        return view('packs.checkout', compact(
            'therapist',
            'pack',
            'packs',
            'trainings',
            'selectedType',
            'selectedId',
            'packPriceTtc',
            'unitTotalTtc',
            'saving',
            'savingPct',
            'training',
            'trainingPriceStr',
        ));
    }

    public function store(Request $request, string $slug)
    {
        $therapist = User::where('slug', $slug)
            ->where('is_therapist', true)
            ->firstOrFail();

        $request->validate([
            'item'       => 'required|string', // pack:ID or training:ID
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'phone'      => 'nullable|string|max:20',
            'notes'      => 'nullable|string|max:2000',
        ], [
            'item.required'       => 'Veuillez sélectionner un achat.',
            'first_name.required' => 'Le prénom est requis.',
            'last_name.required'  => 'Le nom est requis.',
            'email.required'      => 'L’email est requis.',
            'email.email'         => 'Veuillez fournir une adresse e-mail valide.',
        ]);

        if (!preg_match('/^(pack|training):(\d+)$/', (string) $request->item, $m)) {
            return back()->withErrors(['item' => 'Sélection invalide.'])->withInput();
        }

        $type = $m[1];
        $id   = (int) $m[2];

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

        $clientProfile->update([
            'first_name' => $clientProfile->first_name ?: $request->first_name,
            'last_name'  => $clientProfile->last_name  ?: $request->last_name,
            'phone'      => $clientProfile->phone      ?: $request->phone,
        ]);

        $amountTtc = 0.0;
        $lineLabel = '';
        $purchase = null;

        if ($type === 'pack') {
            $pack = PackProduct::where('user_id', $therapist->id)
                ->with(['items.product'])
                ->findOrFail($id);

            abort_unless(($pack->is_active ?? true) && ($pack->visible_in_portal !== false), 404);

            $taxRate   = (float) ($pack->tax_rate ?? 0);
            $amountHt  = (float) ($pack->price ?? 0);
            $amountTtc = (float) ($pack->price_incl_tax ?? ($amountHt + ($amountHt * $taxRate / 100)));

            $lineLabel = 'Pack : ' . $pack->name;

            $pack->load(['items']);

            $purchase = DB::transaction(function () use ($therapist, $pack, $clientProfile, $request) {
                $purchase = PackPurchase::create([
                    'user_id'           => $therapist->id,
                    'pack_product_id'   => $pack->id,
                    'client_profile_id' => $clientProfile->id,
                    'status'            => 'pending',
                    'notes'             => $request->notes,
                    'purchased_at'      => null,
                    'expires_at'        => null,
                ]);

                foreach ($pack->items as $line) {
                    $qty = (int) ($line->quantity ?? 0);
                    if ($qty <= 0) continue;

                    $purchase->items()->create([
                        'product_id'         => (int) $line->product_id,
                        'quantity_total'     => $qty,
                        'quantity_remaining' => $qty,
                    ]);
                }

                return $purchase;
            });
        } else {
            $training = DigitalTraining::where('user_id', $therapist->id)->findOrFail($id);

            if (Schema::hasColumn('digital_trainings', 'visibility')) {
                abort_unless(in_array($training->visibility, ['public'], true), 404);
            }
            if (Schema::hasColumn('digital_trainings', 'published_at')) {
                abort_unless(!is_null($training->published_at), 404);
            }
            if (Schema::hasColumn('digital_trainings', 'status')) {
                abort_unless(in_array($training->status, ['published', 'active'], true), 404);
            }

            $isFree = (bool) ($training->is_free ?? false);
            abort_unless(!$isFree && !is_null($training->price_cents), 404);

            $amountTtc = (float) ($training->price_cents / 100);
            $lineLabel = 'Formation : ' . $training->title;

            if (
                Schema::hasColumn('pack_purchases', 'digital_training_id')
                && Schema::hasColumn('pack_purchases', 'purchase_type')
                && Schema::hasColumn('pack_purchases', 'pack_product_id')
            ) {
                $purchase = PackPurchase::create([
                    'user_id'             => $therapist->id,
                    'pack_product_id'     => null,
                    'client_profile_id'   => $clientProfile->id,
                    'purchase_type'       => 'training',
                    'digital_training_id' => $training->id,
                    'status'              => 'pending',
                    'notes'               => $request->notes,
                    'purchased_at'        => null,
                    'expires_at'          => null,
                ]);
            }
        }

        if ($therapist->stripe_account_id) {
            try {
                $stripe = new StripeClient(config('services.stripe.secret'));

                $metadata = [
                    'purchase_kind'     => $type,
                    'therapist_id'      => $therapist->id,
                    'client_profile_id' => $clientProfile->id,
                    'customer_email'    => $request->email,
                ];

                if ($type === 'pack') {
                    $metadata['pack_purchase_id'] = $purchase->id;
                    $metadata['pack_product_id']  = $pack->id;
                } else {
                    $metadata['digital_training_id'] = $id;
                    if ($purchase) $metadata['pack_purchase_id'] = $purchase->id;
                }

                $session = $stripe->checkout->sessions->create([
                    'payment_method_types' => ['card'],
                    'customer_email' => $request->email,
                    'line_items' => [[
                        'price_data' => [
                            'currency'     => 'eur',
                            'product_data' => ['name' => $lineLabel],
                            'unit_amount'  => (int) round($amountTtc * 100),
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => route('packs.checkout.success') . '?session_id={CHECKOUT_SESSION_ID}&account_id=' . $therapist->stripe_account_id,
                    'cancel_url'  => route('packs.checkout.cancel') . ($purchase ? ('?purchase_id=' . $purchase->id) : ''),
                    'payment_intent_data' => [
                        'metadata' => $metadata,
                    ],
                ], [
                    'stripe_account' => $therapist->stripe_account_id,
                ]);

                if ($purchase && Schema::hasColumn('pack_purchases', 'stripe_session_id')) {
                    $purchase->stripe_session_id = $session->id;
                    $purchase->save();
                }

                return redirect($session->url);
            } catch (\Exception $e) {
                Log::error('Checkout Stripe creation failed: ' . $e->getMessage());

                return back()->withErrors([
                    'payment' => 'Erreur lors de la création de la session de paiement. Veuillez réessayer.'
                ])->withInput();
            }
        }

        if ($purchase) {
            $purchase->update([
                'status'       => 'active',
                'purchased_at' => Carbon::now(),
            ]);
        }

        return redirect()->route('therapist.show', $therapist->slug)
            ->with('success', $type === 'pack' ? 'Pack acheté avec succès.' : 'Formation achetée avec succès.');
    }
}