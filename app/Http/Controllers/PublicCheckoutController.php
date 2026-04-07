<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use App\Models\DigitalTraining;
use App\Models\PackProduct;
use App\Models\PackPurchase;
use App\Models\User;
use App\Services\StripeAccountGuard;
use App\Support\InstallmentPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Stripe\StripeClient;

class PublicCheckoutController extends Controller
{
    public function show(Request $request, string $slug, StripeAccountGuard $stripeGuard)
    {
        $therapist = User::where('slug', $slug)
            ->where('is_therapist', true)
            ->firstOrFail();

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

        $selectedType = null;
        $selectedId = null;
        $itemParam = (string) $request->query('item', '');
        if (preg_match('/^(pack|training):(\d+)$/', $itemParam, $m)) {
            $selectedType = $m[1];
            $selectedId = (int) $m[2];
        }

        if (!$selectedType || !$selectedId) {
            if ($packs->count()) {
                $selectedType = 'pack';
                $selectedId = (int) $packs->first()->id;
            } elseif ($trainings->count()) {
                $selectedType = 'training';
                $selectedId = (int) $trainings->first()->id;
            } else {
                abort(404);
            }
        }

        $pack = null;
        $packPriceTtc = null;
        $unitTotalTtc = null;
        $saving = null;
        $savingPct = null;

        $training = null;
        $trainingPriceStr = null;
        $totalAmountCents = 0;
        $selectedRetractationNoticeRequired = false;
        $selectedRetractationNoticeLabel = null;
        $selectedRetractationNoticeUrl = null;

        if ($selectedType === 'pack') {
            $pack = $packs->firstWhere('id', $selectedId);
            abort_unless($pack, 404);

            $pack->loadMissing(['items.product']);

            $taxRate = (float) ($pack->tax_rate ?? 0);
            $packPriceTtc = (float) ($pack->price_incl_tax ?? ($pack->price + ($pack->price * $taxRate / 100)));
            $totalAmountCents = (int) round($packPriceTtc * 100);

            $unitTotalTtc = (float) $pack->items->sum(function ($it) {
                $p = $it->product;
                if (!$p) {
                    return 0;
                }

                $pTax = (float) ($p->tax_rate ?? 0);
                $pTtc = (float) ($p->price_incl_tax ?? ($p->price + ($p->price * $pTax / 100)));

                return $pTtc * (int) ($it->quantity ?? 1);
            });

            $saving = max(0, $unitTotalTtc - $packPriceTtc);
            $savingPct = $unitTotalTtc > 0 ? (int) round(($saving / $unitTotalTtc) * 100) : null;
        } else {
            $training = $trainings->firstWhere('id', $selectedId);
            abort_unless($training, 404);
            $training->loadMissing('user');

            $isFree = (bool) ($training->is_free ?? false);
            if (!$isFree && !is_null($training->price_cents)) {
                $trainingPriceStr = number_format($training->price_cents / 100, 2, ',', ' ') . ' €';
                $totalAmountCents = (int) $training->price_cents;
            }

            if (
                Schema::hasColumn('digital_trainings', 'use_global_retractation_notice')
                && $training->requiresRetractationNotice()
            ) {
                $selectedRetractationNoticeRequired = true;
                $selectedRetractationNoticeLabel = $training->user?->digitalSalesRetractationNoticeLabel();
                $selectedRetractationNoticeUrl = $training->user?->digital_sales_retractation_url;
            }
        }

        $stripeStatus = $stripeGuard->status($therapist);
        $stripeReady = (bool) ($stripeStatus['ready'] ?? false);
        $selectedInstallmentsEnabled = false;
        $availableInstallmentPlans = [];
        $defaultInstallmentCount = null;

        if ($totalAmountCents > 0) {
            $allowed = $selectedType === 'pack'
                ? InstallmentPlan::sanitizeAllowed((array) ($pack?->allowed_installments ?? []))
                : InstallmentPlan::sanitizeAllowed((array) ($training?->allowed_installments ?? []));

            $installmentsEnabledByProduct = $selectedType === 'pack'
                ? (bool) ($pack?->installments_enabled ?? false)
                : (bool) ($training?->installments_enabled ?? false);

            $selectedInstallmentsEnabled = $stripeReady && $installmentsEnabledByProduct && !empty($allowed);
            $availableInstallmentPlans = $selectedInstallmentsEnabled
                ? InstallmentPlan::plansForAllowed($totalAmountCents, $allowed)
                : [];
            $defaultInstallmentCount = !empty($availableInstallmentPlans)
                ? (int) array_key_first($availableInstallmentPlans)
                : null;
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
            'stripeReady',
            'selectedInstallmentsEnabled',
            'availableInstallmentPlans',
            'defaultInstallmentCount',
            'selectedRetractationNoticeRequired',
            'selectedRetractationNoticeLabel',
            'selectedRetractationNoticeUrl',
        ));
    }

    public function store(Request $request, string $slug, StripeAccountGuard $stripeGuard)
    {
        $therapist = User::where('slug', $slug)
            ->where('is_therapist', true)
            ->firstOrFail();

        $request->validate([
            'item' => 'required|string',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:2000',
            'payment_choice' => 'nullable|in:one_time,installments',
            'installment_count' => 'nullable|integer|min:2|max:12',
        ], [
            'item.required' => 'Veuillez sélectionner un achat.',
            'first_name.required' => 'Le prénom est requis.',
            'last_name.required' => 'Le nom est requis.',
            'email.required' => 'L’email est requis.',
            'email.email' => 'Veuillez fournir une adresse e-mail valide.',
        ]);

        if (!preg_match('/^(pack|training):(\d+)$/', (string) $request->item, $m)) {
            return back()->withErrors(['item' => 'Sélection invalide.'])->withInput();
        }

        $type = $m[1];
        $id = (int) $m[2];
        $paymentChoice = (string) ($request->input('payment_choice') ?: 'one_time');

        $clientProfile = ClientProfile::firstOrCreate(
            [
                'email' => $request->email,
                'user_id' => $therapist->id,
            ],
            [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'notes' => $request->notes,
            ]
        );

        $clientProfile->update([
            'first_name' => $clientProfile->first_name ?: $request->first_name,
            'last_name' => $clientProfile->last_name ?: $request->last_name,
            'phone' => $clientProfile->phone ?: $request->phone,
        ]);

        $amountTtc = 0.0;
        $amountCents = 0;
        $lineLabel = '';
        $purchase = null;
        $allowedInstallments = [];
        $retractationNoticeRequired = false;
        $retractationNoticeLabel = null;
        $retractationNoticeUrl = null;

        if ($type === 'pack') {
            $pack = PackProduct::where('user_id', $therapist->id)
                ->with(['items.product'])
                ->findOrFail($id);

            abort_unless(($pack->is_active ?? true) && ($pack->visible_in_portal !== false), 404);

            $taxRate = (float) ($pack->tax_rate ?? 0);
            $amountHt = (float) ($pack->price ?? 0);
            $amountTtc = (float) ($pack->price_incl_tax ?? ($amountHt + ($amountHt * $taxRate / 100)));
            $amountCents = (int) round($amountTtc * 100);
            $lineLabel = 'Pack : ' . $pack->name;
            $allowedInstallments = ((bool) ($pack->installments_enabled ?? false))
                ? InstallmentPlan::sanitizeAllowed((array) ($pack->allowed_installments ?? []))
                : [];

            $pack->load(['items']);
            $purchase = DB::transaction(function () use ($therapist, $pack, $clientProfile, $request, $paymentChoice) {
                $payload = [
                    'user_id' => $therapist->id,
                    'pack_product_id' => $pack->id,
                    'client_profile_id' => $clientProfile->id,
                    'status' => 'pending',
                    'notes' => $request->notes,
                    'purchased_at' => null,
                    'expires_at' => null,
                ];

                if (Schema::hasColumn('pack_purchases', 'payment_mode')) {
                    $payload['payment_mode'] = $paymentChoice === 'installments' ? 'installments' : 'one_time';
                }
                if (Schema::hasColumn('pack_purchases', 'payment_state')) {
                    $payload['payment_state'] = 'pending';
                }

                $purchase = PackPurchase::create($payload);

                foreach ($pack->items as $line) {
                    $qty = (int) ($line->quantity ?? 0);
                    if ($qty <= 0) {
                        continue;
                    }

                    $purchase->items()->create([
                        'product_id' => (int) $line->product_id,
                        'quantity_total' => $qty,
                        'quantity_remaining' => $qty,
                    ]);
                }

                return $purchase;
            });
        } else {
            $training = DigitalTraining::where('user_id', $therapist->id)->findOrFail($id);
            $training->loadMissing('user');

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

            $amountCents = (int) $training->price_cents;
            $amountTtc = (float) ($training->price_cents / 100);
            $lineLabel = 'Formation : ' . $training->title;
            $allowedInstallments = ((bool) ($training->installments_enabled ?? false))
                ? InstallmentPlan::sanitizeAllowed((array) ($training->allowed_installments ?? []))
                : [];

            if (
                Schema::hasColumn('digital_trainings', 'use_global_retractation_notice')
                && $training->requiresRetractationNotice()
            ) {
                $retractationNoticeRequired = true;
                $retractationNoticeLabel = $training->user?->digitalSalesRetractationNoticeLabel();
                $retractationNoticeUrl = $training->user?->digital_sales_retractation_url;
            }

            if ($retractationNoticeRequired && !$request->boolean('retractation_notice_acknowledged')) {
                return back()->withErrors([
                    'retractation_notice_acknowledged' => 'Veuillez confirmer la lecture du document lié au droit de rétractation avant de poursuivre.',
                ])->withInput();
            }

            if (
                Schema::hasColumn('pack_purchases', 'digital_training_id')
                && Schema::hasColumn('pack_purchases', 'purchase_type')
                && Schema::hasColumn('pack_purchases', 'pack_product_id')
            ) {
                $payload = [
                    'user_id' => $therapist->id,
                    'pack_product_id' => null,
                    'client_profile_id' => $clientProfile->id,
                    'purchase_type' => 'training',
                    'digital_training_id' => $training->id,
                    'status' => 'pending',
                    'notes' => $request->notes,
                    'purchased_at' => null,
                    'expires_at' => null,
                ];

                if (Schema::hasColumn('pack_purchases', 'payment_mode')) {
                    $payload['payment_mode'] = $paymentChoice === 'installments' ? 'installments' : 'one_time';
                }
                if (Schema::hasColumn('pack_purchases', 'payment_state')) {
                    $payload['payment_state'] = 'pending';
                }
                if (Schema::hasColumn('pack_purchases', 'retractation_notice_required')) {
                    $payload['retractation_notice_required'] = $retractationNoticeRequired;
                }
                if (Schema::hasColumn('pack_purchases', 'retractation_notice_accepted_at') && $retractationNoticeRequired) {
                    $payload['retractation_notice_accepted_at'] = now();
                }
                if (Schema::hasColumn('pack_purchases', 'retractation_notice_label_snapshot') && $retractationNoticeRequired) {
                    $payload['retractation_notice_label_snapshot'] = $retractationNoticeLabel;
                }
                if (Schema::hasColumn('pack_purchases', 'retractation_notice_url_snapshot') && $retractationNoticeRequired) {
                    $payload['retractation_notice_url_snapshot'] = $retractationNoticeUrl;
                }

                $purchase = PackPurchase::create($payload);
            }
        }

        if (!$purchase) {
            return back()->withErrors([
                'payment' => 'Configuration incomplète pour cet achat. Veuillez contacter le support.',
            ])->withInput();
        }

        $stripeReady = (bool) ($stripeGuard->status($therapist)['ready'] ?? false);
        $selectedInstallmentCount = $request->filled('installment_count') ? (int) $request->input('installment_count') : null;
        $plans = InstallmentPlan::plansForAllowed($amountCents, $allowedInstallments);
        $selectedPlan = ($selectedInstallmentCount && isset($plans[$selectedInstallmentCount]))
            ? $plans[$selectedInstallmentCount]
            : null;

        if ($paymentChoice === 'installments') {
            if (!$therapist->stripe_account_id || !$stripeReady) {
                $this->cleanupAbandonedPurchase($purchase);
                return back()->withErrors([
                    'payment' => 'Le paiement en plusieurs fois nécessite un compte Stripe Connect configuré.',
                ])->withInput();
            }

            if (!$selectedPlan) {
                $this->cleanupAbandonedPurchase($purchase);
                return back()->withErrors([
                    'installment_count' => 'Cette option de paiement en plusieurs fois n’est pas disponible pour cet achat.',
                ])->withInput();
            }

            if ($purchase && Schema::hasColumn('pack_purchases', 'installments_total')) {
                $purchase->installments_total = (int) $selectedPlan['count'];
                $purchase->installments_paid = 0;
                $purchase->installment_amount_cents = (int) $selectedPlan['base_cents'];
                $purchase->save();
            }
        }

        if ($therapist->stripe_account_id) {
            try {
                $stripe = new StripeClient(config('services.stripe.secret'));

                $metadata = [
                    'purchase_kind' => $type,
                    'therapist_id' => (string) $therapist->id,
                    'client_profile_id' => (string) $clientProfile->id,
                    'customer_email' => (string) $request->email,
                    'payment_mode' => $paymentChoice === 'installments' ? 'installments' : 'one_time',
                ];

                if ($type === 'pack' && isset($pack)) {
                    $metadata['pack_product_id'] = (string) $pack->id;
                } elseif ($type === 'training') {
                    $metadata['digital_training_id'] = (string) $id;
                }

                if ($purchase) {
                    $metadata['pack_purchase_id'] = (string) $purchase->id;
                }

                if ($paymentChoice === 'installments' && $selectedPlan) {
                    $metadata['installments_total'] = (string) $selectedPlan['count'];

                    $sessionData = [
                        'payment_method_types' => ['card'],
                        'customer_email' => $request->email,
                        'mode' => 'subscription',
                        'line_items' => [[
                            'price_data' => [
                                'currency' => 'eur',
                                'product_data' => [
                                    'name' => $lineLabel . ' (paiement en plusieurs fois)',
                                ],
                                'unit_amount' => (int) $selectedPlan['base_cents'],
                                'recurring' => [
                                    'interval' => 'month',
                                    'interval_count' => 1,
                                ],
                            ],
                            'quantity' => 1,
                        ]],
                        'metadata' => $metadata,
                        'subscription_data' => [
                            'metadata' => $metadata,
                        ],
                        'success_url' => route('packs.checkout.success') . '?session_id={CHECKOUT_SESSION_ID}&account_id=' . $therapist->stripe_account_id,
                        'cancel_url' => route('packs.checkout.cancel') . ($purchase ? ('?purchase_id=' . $purchase->id) : ''),
                    ];

                    if ((int) $selectedPlan['remainder_cents'] > 0) {
                        $sessionData['subscription_data']['add_invoice_items'] = [[
                            'price_data' => [
                                'currency' => 'eur',
                                'product_data' => [
                                    'name' => $lineLabel . ' (ajustement 1ère échéance)',
                                ],
                                'unit_amount' => (int) $selectedPlan['remainder_cents'],
                            ],
                            'quantity' => 1,
                        ]];
                    }

                    $session = $stripe->checkout->sessions->create($sessionData, [
                        'stripe_account' => $therapist->stripe_account_id,
                    ]);
                } else {
                    $session = $stripe->checkout->sessions->create([
                        'payment_method_types' => ['card'],
                        'customer_email' => $request->email,
                        'line_items' => [[
                            'price_data' => [
                                'currency' => 'eur',
                                'product_data' => ['name' => $lineLabel],
                                'unit_amount' => (int) round($amountTtc * 100),
                            ],
                            'quantity' => 1,
                        ]],
                        'mode' => 'payment',
                        'metadata' => $metadata,
                        'success_url' => route('packs.checkout.success') . '?session_id={CHECKOUT_SESSION_ID}&account_id=' . $therapist->stripe_account_id,
                        'cancel_url' => route('packs.checkout.cancel') . ($purchase ? ('?purchase_id=' . $purchase->id) : ''),
                        'payment_intent_data' => [
                            'metadata' => $metadata,
                        ],
                    ], [
                        'stripe_account' => $therapist->stripe_account_id,
                    ]);
                }

                if ($purchase && Schema::hasColumn('pack_purchases', 'stripe_session_id')) {
                    $purchase->stripe_session_id = $session->id;
                    $purchase->save();
                }

                return redirect($session->url);
            } catch (\Throwable $e) {
                Log::error('Checkout Stripe creation failed: ' . $e->getMessage(), [
                    'therapist_id' => $therapist->id,
                    'purchase_type' => $type,
                    'payment_mode' => $paymentChoice,
                ]);
                $this->cleanupAbandonedPurchase($purchase);

                return back()->withErrors([
                    'payment' => 'Erreur lors de la création de la session de paiement. Veuillez réessayer.',
                ])->withInput();
            }
        }

        if ($paymentChoice === 'installments') {
            return back()->withErrors([
                'payment' => 'Le paiement en plusieurs fois nécessite Stripe Connect.',
            ])->withInput();
        }

        if ($purchase) {
            $payload = [
                'status' => 'active',
                'purchased_at' => Carbon::now(),
            ];
            if (Schema::hasColumn('pack_purchases', 'payment_state')) {
                $payload['payment_state'] = 'completed';
            }
            if (Schema::hasColumn('pack_purchases', 'activated_at')) {
                $payload['activated_at'] = Carbon::now();
            }
            $purchase->update($payload);
        }

        return redirect()->route('therapist.show', $therapist->slug)
            ->with('success', $type === 'pack' ? 'Pack acheté avec succès.' : 'Formation achetée avec succès.');
    }

    private function cleanupAbandonedPurchase(?PackPurchase $purchase): void
    {
        if (!$purchase) {
            return;
        }

        if ($purchase->status === 'pending' && !$purchase->purchased_at) {
            $purchase->delete();
        }
    }
}
