{{-- resources/views/dashboard-pro/gift-vouchers/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-semibold text-xl" style="color:#647a0b;">
                    Nouveau bon cadeau
                </h2>
                <p class="mt-1 text-xs text-slate-600">
                    Le paiement est encaissé en dehors d’AromaMade (espèces, virement, terminal CB). AromaMade gère le PDF + le suivi du solde.
                </p>
            </div>

            <a href="{{ route('pro.gift-vouchers.index') }}"
               class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold ring-1 ring-slate-200 bg-white hover:bg-slate-50 transition"
               style="color:#6b4f2a;">
                ← Retour
            </a>
        </div>
    </x-slot>

    <style>
        :root{ --brand:#647a0b; --brown:#6b4f2a; --cream:#f7f2ea; }
        .am-shell{
            background:
                radial-gradient(circle at 0% 0%, rgba(100,122,11,0.10), transparent 45%),
                radial-gradient(circle at 100% 10%, rgba(107,79,42,0.10), transparent 45%),
                radial-gradient(circle at 20% 100%, rgba(100,122,11,0.08), transparent 55%),
                linear-gradient(180deg, #fbfaf7 0%, #f3f4f6 100%);
            border-radius: 1.25rem;
        }
        .am-card{
            background: rgba(255,255,255,0.92);
            border: 1px solid rgba(15,23,42,0.10);
            box-shadow: 0 10px 30px rgba(15,23,42,0.05);
            border-radius: 1.25rem;
            overflow: hidden;
        }
        .am-label{ font-size:.75rem; font-weight:900; color:#334155; letter-spacing:.03em; text-transform:uppercase; }
        .am-input{
            width:100%;
            border-radius: 1rem;
            border: 1px solid rgba(15,23,42,0.12);
            background: white;
            padding: .7rem .9rem;
            outline: none;
        }
        .am-input:focus{
            border-color: rgba(100,122,11,0.55);
            box-shadow: 0 0 0 4px rgba(100,122,11,0.12);
        }
        .am-help{ font-size:.75rem; color:#64748b; }
        .am-error{ font-size:.8rem; color:#b91c1c; font-weight:700; margin-top:.35rem; }
        .am-btn{
            border-radius: 1rem;
            padding: .75rem 1rem;
            font-size: .9rem;
            font-weight: 900;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:.5rem;
            transition:.15s ease;
        }
        .am-btn-brand{ background: var(--brand); color:#fff; box-shadow: 0 12px 26px rgba(100,122,11,0.18); }
        .am-btn-brand:hover{ opacity:.95; }
        .am-soft{
            background: rgba(107,79,42,0.08);
            border: 1px solid rgba(107,79,42,0.18);
            color: var(--brown);
            border-radius: 1rem;
        }
    </style>

    <div class="container mt-6">
        <div class="am-shell p-4 md:p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2 am-card">
                    <div class="p-4 md:p-6 border-b border-slate-200/70">
                        <div class="text-sm font-extrabold text-slate-900">Informations du bon cadeau</div>
                        <div class="text-xs text-slate-600 mt-0.5">Le PDF est généré automatiquement et envoyé par email.</div>
                    </div>

                    <form method="POST" action="{{ route('pro.gift-vouchers.store') }}" class="p-4 md:p-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="am-label">Montant (€)</div>
                                <input class="am-input" name="amount_eur" type="number" step="0.01" min="5"
                                       value="{{ old('amount_eur') }}" placeholder="Ex: 50">
                                @error('amount_eur') <div class="am-error">{{ $message }}</div> @enderror
                                <div class="am-help mt-1">Minimum recommandé : 5 €.</div>
                            </div>

                            <div>
                                <div class="am-label">Date d’expiration (optionnel)</div>
                                <input class="am-input" name="expires_at" type="date" value="{{ old('expires_at') }}">
                                @error('expires_at') <div class="am-error">{{ $message }}</div> @enderror
                                <div class="am-help mt-1">Laissez vide pour “sans date limite”.</div>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="rounded-2xl border border-slate-200/70 bg-white p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-extrabold text-slate-900">Acheteur</div>
                                        <div class="text-xs text-slate-600">Reçoit toujours l’email + PDF.</div>
                                    </div>
                                    <div class="h-10 w-10 rounded-2xl flex items-center justify-center"
                                         style="background: rgba(100,122,11,0.10); color: var(--brand);">✦</div>
                                </div>

                                <div class="mt-4 space-y-3">
                                    <div>
                                        <div class="am-label">Nom (optionnel)</div>
                                        <input class="am-input" name="buyer_name" type="text" value="{{ old('buyer_name') }}" placeholder="Ex: Marie Dupont">
                                        @error('buyer_name') <div class="am-error">{{ $message }}</div> @enderror
                                    </div>
                                    <div>
                                        <div class="am-label">Email</div>
                                        <input class="am-input" name="buyer_email" type="email" value="{{ old('buyer_email') }}" placeholder="ex: marie@email.com">
                                        @error('buyer_email') <div class="am-error">{{ $message }}</div> @enderror
                                    </div>
                                    <div>
                                        <div class="am-label">Téléphone (optionnel)</div>
                                        <input class="am-input" name="buyer_phone" type="text" value="{{ old('buyer_phone') }}" placeholder="ex: 0612345678">
                                        @error('buyer_phone') <div class="am-error">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200/70 bg-white p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-extrabold text-slate-900">Bénéficiaire</div>
                                        <div class="text-xs text-slate-600">Optionnel (reçoit l’email + PDF).</div>
                                    </div>
                                    <div class="h-10 w-10 rounded-2xl flex items-center justify-center"
                                         style="background: rgba(107,79,42,0.10); color: var(--brown);">☰</div>
                                </div>

                                <div class="mt-4 space-y-3">
                                    <div>
                                        <div class="am-label">Nom (optionnel)</div>
                                        <input class="am-input" name="recipient_name" type="text" value="{{ old('recipient_name') }}" placeholder="Ex: Thomas">
                                        @error('recipient_name') <div class="am-error">{{ $message }}</div> @enderror
                                    </div>
                                    <div>
                                        <div class="am-label">Email (optionnel)</div>
                                        <input class="am-input" name="recipient_email" type="email" value="{{ old('recipient_email') }}" placeholder="ex: thomas@email.com">
                                        @error('recipient_email') <div class="am-error">{{ $message }}</div> @enderror
                                        <div class="am-help mt-1">Si vide, seul l’acheteur recevra le bon cadeau.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="am-label">Message personnalisé (optionnel)</div>
                            <textarea class="am-input" name="message" rows="4" placeholder="Ex: Joyeux anniversaire 🎁">{{ old('message') }}</textarea>
                            @error('message') <div class="am-error">{{ $message }}</div> @enderror
                            <div class="am-help mt-1">Le message apparaîtra sur le PDF.</div>
                        </div>

                        <div class="mt-6 rounded-2xl border border-slate-200/70 bg-white p-4">
                            <label class="inline-flex items-center gap-2 text-sm font-extrabold text-slate-900">
                                <input type="checkbox" name="create_sale_invoice" value="1" {{ old('create_sale_invoice') ? 'checked' : '' }}>
                                Créer une facture de vente du bon cadeau
                            </label>

                            <div class="mt-3">
                                <div class="am-label">Mode de paiement</div>
                                <select class="am-input" name="payment_method">
                                    <option value="other" {{ old('payment_method') === 'other' ? 'selected' : '' }}>Autre</option>
                                    <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>Carte</option>
                                    <option value="transfer" {{ old('payment_method') === 'transfer' ? 'selected' : '' }}>Virement</option>
                                    <option value="check" {{ old('payment_method') === 'check' ? 'selected' : '' }}>Chèque</option>
                                    <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Espèces</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-between">
                            <div class="text-xs text-slate-600">
                                <span class="font-bold" style="color: var(--brown);">Note :</span>
                                le code est secret (long alphanum) et le bon est utilisable en plusieurs fois.
                            </div>

                            <button class="am-btn am-btn-brand">
                                Créer & envoyer
                            </button>
                        </div>
                    </form>
                </div>

                <div class="am-card p-4 md:p-6">
                    <div class="text-sm font-extrabold text-slate-900">Aperçu (concept)</div>
                    <div class="text-xs text-slate-600 mt-0.5">Le PDF inclut un QR code vers votre portail.</div>

                    <div class="mt-4 rounded-2xl border border-slate-200/70 bg-white p-4">
                        <div class="text-xs font-bold text-slate-500 uppercase tracking-wide">Contenu PDF</div>
                        <ul class="mt-3 space-y-2 text-sm text-slate-700">
                            <li class="flex gap-2"><span style="color:var(--brand)">✔</span> Montant + code secret</li>
                            <li class="flex gap-2"><span style="color:var(--brand)">✔</span> Message personnalisé</li>
                            <li class="flex gap-2"><span style="color:var(--brand)">✔</span> QR code → portail thérapeute</li>
                            <li class="flex gap-2"><span style="color:var(--brand)">✔</span> Date d’expiration (si définie)</li>
                        </ul>
                    </div>

                    <div class="mt-4 rounded-2xl am-soft p-4">
                        <div class="text-sm font-extrabold">Conseil</div>
                        <div class="text-xs mt-1">
                            Pour une expérience premium : imprimez le PDF sur papier épais, ou envoyez-le directement au bénéficiaire.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
