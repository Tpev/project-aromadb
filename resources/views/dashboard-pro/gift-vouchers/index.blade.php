{{-- resources/views/dashboard-pro/gift-vouchers/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-semibold text-xl" style="color:#647a0b;">
                    Bons cadeaux
                </h2>
                <p class="mt-1 text-xs text-slate-600">
                    Créez des bons cadeaux (montant) et déduisez les utilisations au fil des séances.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-2">
                @if($user->canUseFeature('gift_vouchers'))
                    <a href="{{ route('pro.gift-vouchers.create') }}"
                       class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-95 transition"
                       style="background:#647a0b;">
                        + Nouveau bon cadeau
                    </a>
                @else
                    <a href="{{ url('/license-tiers/pricing') }}"
                       class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold shadow-sm transition"
                       style="background:#fff7ed;color:#9a6700;border:1px solid rgba(154,103,0,.18);">
                        🔒 Débloquer les bons cadeaux
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    @php
        $canUseGiftVouchers = $user && method_exists($user, 'canUseFeature') ? $user->canUseFeature('gift_vouchers') : false;
        $plansConfig = config('license_features.plans', []);
        $familyOrder = ['free', 'starter', 'pro', 'premium'];
        $requiredFamily = null;

        foreach ($familyOrder as $family) {
            if (in_array('gift_vouchers', $plansConfig[$family] ?? [], true)) {
                $requiredFamily = $family;
                break;
            }
        }

        $requiredLabel = match ($requiredFamily) {
            'starter' => 'Starter',
            'pro' => 'Pro',
            'premium' => 'Premium',
            default => 'Pro',
        };

        $upgradeUrl = url('/license-tiers/pricing');
    @endphp

    <style>
        :root{
            --brand: #647a0b;
            --brown: #6b4f2a;
            --cream: #f7f2ea;
        }
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
        .am-badge{
            border-radius: 999px;
            padding: .25rem .6rem;
            font-size: .75rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border: 1px solid rgba(15,23,42,0.10);
            background: white;
            white-space: nowrap;
        }
        .am-dot{ width:.45rem;height:.45rem;border-radius:999px;display:inline-block; }
        .am-table th{
            font-size: .72rem;
            letter-spacing: .05em;
            text-transform: uppercase;
        }
        .am-row:hover{ background: rgba(100,122,11,0.06); }
        .am-chip{
            border-radius: 999px;
            padding: .4rem .75rem;
            font-size: .75rem;
            font-weight: 800;
            border: 1px solid rgba(15,23,42,0.10);
            background: white;
            transition: .15s ease;
        }
        .am-chip:hover{ background: rgba(100,122,11,0.06); }
        .am-chip-active{
            background: rgba(100,122,11,0.12);
            border-color: rgba(100,122,11,0.25);
            color: var(--brand);
        }
        .am-btn{
            border-radius: .9rem;
            padding: .55rem .9rem;
            font-size: .78rem;
            font-weight: 900;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            transition: .15s ease;
            white-space: nowrap;
        }
        .am-btn-soft{
            background: white;
            border: 1px solid rgba(15,23,42,0.10);
        }
        .am-btn-soft:hover{ background: rgba(100,122,11,0.06); }
        .am-btn-brand{
            background: var(--brand);
            color: white;
            box-shadow: 0 10px 22px rgba(100,122,11,0.18);
        }
        .am-btn-brand:hover{ opacity: .95; }
        .am-btn-brown{
            background: rgba(107,79,42,0.10);
            color: var(--brown);
            border: 1px solid rgba(107,79,42,0.20);
        }
        .am-btn-brown:hover{ background: rgba(107,79,42,0.14); }
        .am-upgrade-band{
            border-radius: 1.25rem;
            border: 1px solid rgba(154,103,0,.18);
            background:
                radial-gradient(circle at top left, rgba(251,191,36,.18), transparent 36%),
                linear-gradient(135deg, #fff8ef 0%, #ffffff 65%);
            box-shadow: 0 18px 40px rgba(15,23,42,0.06);
        }
        .am-upgrade-chip{
            border-radius: 999px;
            padding: .3rem .7rem;
            font-size: .74rem;
            font-weight: 900;
            color: #9a6700;
            border: 1px solid rgba(154,103,0,.18);
            background: rgba(255,255,255,.82);
        }
        .am-upgrade-btn{
            border-radius: .95rem;
            padding: .8rem 1.1rem;
            font-size: .86rem;
            font-weight: 900;
            background: linear-gradient(135deg, #8ea633 0%, #647a0b 100%);
            color: #fff;
            box-shadow: 0 14px 26px rgba(100,122,11,.22);
        }
        .am-upgrade-btn:hover{ opacity:.96; }
        .am-lock-wrapper{ position:relative; }
        .am-lock-wrapper.is-locked > .am-lock-target{
            filter: blur(4px) saturate(.92);
            pointer-events: none;
            user-select: none;
        }
        .am-lock-overlay{
            position:absolute;
            inset:0;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:1.25rem;
        }
        .am-lock-overlay-card{
            width:min(100%, 530px);
            border-radius:1.25rem;
            background: rgba(255,255,255,.96);
            border:1px solid rgba(154,103,0,.16);
            box-shadow: 0 18px 45px rgba(15,23,42,.14);
            padding: 1.4rem;
            text-align:center;
        }
        .am-lock-kicker{
            font-size:.72rem;
            letter-spacing:.08em;
            text-transform:uppercase;
            font-weight:900;
            color:#9a6700;
        }
        .am-lock-title{
            margin-top:.35rem;
            font-size:1.2rem;
            line-height:1.2;
            font-weight:900;
            color:#1f2937;
        }
        .am-lock-text{
            margin-top:.55rem;
            font-size:.92rem;
            color:#556274;
        }
        .am-lock-list{
            margin-top:.9rem;
            display:grid;
            gap:.55rem;
            text-align:left;
            font-size:.88rem;
            color:#334155;
        }
        .am-lock-list div{
            border-radius:.9rem;
            border:1px solid rgba(15,23,42,.08);
            background:#f8fafc;
            padding:.7rem .85rem;
        }
    </style>

    <div class="container mt-6">
        <div class="am-shell p-4 md:p-6">

            @if(session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-900">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-extrabold">Succès</div>
                            <div class="text-sm mt-0.5">{{ session('success') }}</div>
                        </div>
                        <div class="text-xs font-bold opacity-70">AromaMade PRO</div>
                    </div>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-900">
                    <div class="text-sm font-extrabold">Erreur de configuration</div>
                    <ul class="mt-1 text-sm list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @unless($canUseGiftVouchers)
                <div class="am-upgrade-band p-5 md:p-6 mb-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="max-w-3xl">
                            <div class="am-upgrade-chip inline-flex">Fonction disponible avec votre formule {{ $requiredLabel }}</div>
                            <h3 class="mt-3 text-xl md:text-2xl font-black text-slate-900">
                                Créez des bons cadeaux élégants, vendables en ligne et prêts à envoyer.
                            </h3>
                            <p class="mt-2 text-sm md:text-[15px] text-slate-600">
                                Avec l’option Bon cadeau, vous personnalisez le visuel, générez le PDF automatiquement,
                                envoyez l’email au bon destinataire et pouvez même vendre vos bons cadeaux sur votre portail.
                            </p>
                            <div class="mt-4 grid gap-2 md:grid-cols-3 text-sm text-slate-700">
                                <div class="rounded-2xl border border-white/70 bg-white/70 px-4 py-3">PDF brandé avec image de fond, QR code et code unique.</div>
                                <div class="rounded-2xl border border-white/70 bg-white/70 px-4 py-3">Envoi automatique acheteur + bénéficiaire, suivi et relance en 1 clic.</div>
                                <div class="rounded-2xl border border-white/70 bg-white/70 px-4 py-3">Achat en ligne sur le portail avec Stripe quand votre compte est prêt.</div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <a href="{{ $upgradeUrl }}" class="am-upgrade-btn inline-flex items-center justify-center">
                                Débloquer les bons cadeaux
                            </a>
                            <a href="{{ route('profile.license') }}" class="am-btn am-btn-soft inline-flex items-center justify-center">
                                Voir ma formule
                            </a>
                        </div>
                    </div>
                </div>
            @endunless

            <div class="am-lock-wrapper {{ $canUseGiftVouchers ? '' : 'is-locked' }} mb-4">
                <div class="am-lock-target am-card p-4">
                    <div class="text-sm font-extrabold text-slate-900">Paramètres globaux bon cadeau</div>
                    <div class="text-xs text-slate-600 mt-0.5">
                        Le visuel défini ici s’appliquera aux futurs bons cadeaux (les anciens restent inchangés).
                    </div>

                    <form method="POST" action="{{ route('pro.gift-vouchers.settings.update') }}" enctype="multipart/form-data" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                        @csrf

                    <div class="md:col-span-3">
                        <label class="inline-flex items-center gap-2 text-sm font-bold text-slate-900">
                            <input type="checkbox" name="gift_voucher_online_enabled" value="1" {{ ($user->gift_voucher_online_enabled ?? false) ? 'checked' : '' }}>
                            Activer l’achat de bons cadeaux en ligne sur mon portail
                        </label>
                    </div>

                    <div>
                        <div class="text-xs font-bold text-slate-600 uppercase">Mode visuel</div>
                        <select name="gift_voucher_background_mode" class="mt-1 w-full rounded-xl border-slate-300">
                            <option value="default" {{ ($user->gift_voucher_background_mode ?? 'default') === 'default' ? 'selected' : '' }}>Par défaut</option>
                            <option value="custom_upload" {{ ($user->gift_voucher_background_mode ?? 'default') === 'custom_upload' ? 'selected' : '' }}>Image personnalisée</option>
                        </select>
                    </div>

                    <div>
                        <div class="text-xs font-bold text-slate-600 uppercase">Image de fond (global)</div>
                        <input id="giftVoucherBackgroundInput" type="file" name="gift_voucher_background" accept=".jpg,.jpeg,.png,.webp" class="mt-1 block w-full text-sm">
                    </div>

                    <div>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700 mt-6">
                            <input type="checkbox" name="remove_gift_voucher_background" value="1">
                            Supprimer l’image personnalisée
                        </label>
                    </div>

                    @php
                        $previewInlineStyle = $backgroundPreviewDataUri
                            ? "background-image:url('{$backgroundPreviewDataUri}');background-size:cover;background-position:center;"
                            : "background:linear-gradient(145deg, rgba(100,122,11,0.30), rgba(107,79,42,0.25));";
                    @endphp
                    <div class="md:col-span-3">
                        <div class="text-xs font-bold text-slate-600 uppercase mb-2">Aperçu bon cadeau</div>
                        <div class="rounded-2xl border border-slate-200/70 bg-white p-3">
                            <div id="giftVoucherBackgroundPreview"
                                 class="relative rounded-xl overflow-hidden border border-slate-200/70"
                                 style="height:220px;{{ $previewInlineStyle }}">
                                <div style="position:absolute;inset:0;background:linear-gradient(180deg, rgba(15,23,42,0.10), rgba(15,23,42,0.28));"></div>
                                <div style="position:absolute;left:16px;right:16px;bottom:16px;color:white;">
                                <div style="font-size:20px;font-weight:800;line-height:1.1;">Bon cadeau</div>
                                    <div style="font-size:12px;opacity:0.95;">Aperçu visuel du futur PDF</div>
                                </div>
                            </div>
                            <div id="giftVoucherBackgroundPreviewHint" class="mt-2 text-xs text-slate-600">
                                @if($backgroundPreviewDataUri)
                                    Fond personnalisé actif.
                                @else
                                    Aucun fond personnalisé actif (thème par défaut).
                                @endif
                            </div>
                        </div>
                    </div>

                        <div class="md:col-span-3 flex justify-end">
                            <button class="am-btn am-btn-brand" type="submit">Enregistrer</button>
                        </div>
                    </form>
                </div>

                @unless($canUseGiftVouchers)
                    <div class="am-lock-overlay">
                        <div class="am-lock-overlay-card">
                            <div class="am-lock-kicker">Configuration premium</div>
                            <div class="am-lock-title">Préparez un univers cadeau cohérent avec votre marque</div>
                            <p class="am-lock-text">
                                Ajoutez un fond personnalisé, activez l’achat en ligne sur votre portail et préparez un PDF soigné.
                                La configuration est visible ici, mais son activation demande la formule {{ $requiredLabel }}.
                            </p>
                            <div class="mt-4">
                                <a href="{{ $upgradeUrl }}" class="am-upgrade-btn inline-flex items-center justify-center">
                                    Débloquer cette configuration
                                </a>
                            </div>
                        </div>
                    </div>
                @endunless
            </div>

            @php
                // Small stats (no controller changes required)
                $total = $vouchers->total();
                $activeCount = 0;
                $expiredCount = 0;
                $exhaustedCount = 0;
                $disabledCount = 0;

                foreach ($vouchers as $v) {
                    if (!$v->is_active) { $disabledCount++; continue; }
                    if (method_exists($v, 'isExpired') && $v->isExpired()) { $expiredCount++; continue; }
                    if ($v->remaining_amount_cents <= 0) { $exhaustedCount++; continue; }
                    $activeCount++;
                }
            @endphp

            <div class="am-lock-wrapper {{ $canUseGiftVouchers ? '' : 'is-locked' }}">
            <div class="am-lock-target">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-4">
                <div class="am-card p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-semibold text-slate-500">Total</div>
                            <div class="mt-1 text-2xl font-extrabold text-slate-900">{{ $total }}</div>
                        </div>
                        <div class="h-10 w-10 rounded-2xl flex items-center justify-center"
                             style="background: rgba(100,122,11,0.10); color: var(--brand);">🎁</div>
                    </div>
                    <div class="mt-2 text-xs text-slate-600">Tous vos bons cadeaux.</div>
                </div>

                <div class="am-card p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-semibold text-slate-500">Actifs</div>
                            <div class="mt-1 text-2xl font-extrabold text-slate-900">{{ $activeCount }}</div>
                        </div>
                        <div class="h-10 w-10 rounded-2xl flex items-center justify-center"
                             style="background: rgba(100,122,11,0.10); color: var(--brand);">●</div>
                    </div>
                    <div class="mt-2 text-xs text-slate-600">Utilisables.</div>
                </div>

                <div class="am-card p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-semibold text-slate-500">Expirés</div>
                            <div class="mt-1 text-2xl font-extrabold text-slate-900">{{ $expiredCount }}</div>
                        </div>
                        <div class="h-10 w-10 rounded-2xl flex items-center justify-center"
                             style="background: rgba(107,79,42,0.10); color: var(--brown);">⏳</div>
                    </div>
                    <div class="mt-2 text-xs text-slate-600">Date dépassée.</div>
                </div>

                <div class="am-card p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-semibold text-slate-500">Désactivés</div>
                            <div class="mt-1 text-2xl font-extrabold text-slate-900">{{ $disabledCount }}</div>
                        </div>
                        <div class="h-10 w-10 rounded-2xl flex items-center justify-center"
                             style="background: rgba(239,68,68,0.10); color: rgb(185,28,28);">⛔</div>
                    </div>
                    <div class="mt-2 text-xs text-slate-600">Non utilisables.</div>
                </div>
            </div>

            @php
                $tabs = [
                    'all' => 'Tous',
                    'active' => 'Actifs',
                    'expired' => 'Expirés',
                    'exhausted' => 'Épuisés',
                    'disabled' => 'Désactivés',
                ];
                $currentStatus = $status ?? request('status', 'all');
            @endphp

            <div class="am-card">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-slate-200/70">
                    <div>
                        <div class="text-sm font-extrabold text-slate-900">Liste des bons cadeaux</div>
                        <div class="text-xs text-slate-600 mt-0.5">Gérez, téléchargez le PDF, et renvoyez les emails.</div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @if($canUseGiftVouchers)
                            <a href="{{ route('pro.gift-vouchers.create') }}"
                               class="am-btn am-btn-brand">
                                + Nouveau
                            </a>
                        @else
                            <a href="{{ $upgradeUrl }}"
                               class="am-btn am-btn-soft"
                               style="color:#9a6700;border-color:rgba(154,103,0,.16);background:#fff7ed;">
                                🔒 Nouveau
                            </a>
                        @endif
                    </div>
                </div>

                <div class="p-4 border-b border-slate-200/70 bg-white">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div class="flex flex-wrap gap-2">
                            @foreach($tabs as $key => $label)
                                <a href="{{ route('pro.gift-vouchers.index', ['status' => $key]) }}"
                                   class="am-chip {{ $currentStatus === $key ? 'am-chip-active' : 'text-slate-700' }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>

                        <div class="text-xs text-slate-600">
                            Page {{ $vouchers->currentPage() }} / {{ $vouchers->lastPage() }}
                        </div>
                    </div>
                </div>

                {{-- Mobile --}}
                <div class="block md:hidden divide-y divide-slate-100 bg-white">
                    @forelse($vouchers as $v)
                        @php
                            $label = method_exists($v, 'statusLabel') ? $v->statusLabel() : ($v->is_active ? 'Actif' : 'Désactivé');
                            $badgeStyle = 'color:#334155;background:rgba(15,23,42,0.04);';
                            if ($label === 'Actif') $badgeStyle='color:var(--brand);border-color:rgba(100,122,11,0.25);background:rgba(100,122,11,0.06);';
                            if ($label === 'Expiré') $badgeStyle='color:var(--brown);border-color:rgba(107,79,42,0.25);background:rgba(107,79,42,0.07);';
                            if ($label === 'Épuisé') $badgeStyle='color:#1d4ed8;border-color:rgba(29,78,216,0.20);background:rgba(29,78,216,0.06);';
                            if ($label === 'Désactivé') $badgeStyle='color:#b91c1c;border-color:rgba(185,28,28,0.20);background:rgba(185,28,28,0.06);';
                        @endphp

                        <div class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <a href="{{ $canUseGiftVouchers ? route('pro.gift-vouchers.show', $v) : $upgradeUrl }}"
                                       class="text-sm font-extrabold text-slate-900 hover:underline">
                                        {{ $v->code }}
                                    </a>
                                    <div class="mt-1 text-xs text-slate-600">
                                        {{ $v->buyer_email }} • {{ $v->created_at->timezone('Europe/Paris')->format('d/m/Y') }}
                                    </div>
                                </div>
                                <span class="am-badge" style="{{ $badgeStyle }}">
                                    <span class="am-dot" style="background: currentColor;"></span>
                                    {{ $label }}
                                </span>
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-3">
                                <div class="rounded-xl border border-slate-200/70 bg-slate-50 px-3 py-2">
                                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Montant</div>
                                    <div class="text-sm font-extrabold text-slate-900">{{ $v->originalAmountStr() }}</div>
                                </div>
                                <div class="rounded-xl border border-slate-200/70 bg-slate-50 px-3 py-2">
                                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Restant</div>
                                    <div class="text-sm font-extrabold text-slate-900">{{ $v->remainingAmountStr() }}</div>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <a href="{{ $canUseGiftVouchers ? route('pro.gift-vouchers.pdf', $v) : $upgradeUrl }}" class="am-btn am-btn-brown">PDF</a>

                                @if($canUseGiftVouchers)
                                    <form action="{{ route('pro.gift-vouchers.resend', $v) }}" method="POST">
                                        @csrf
                                        <button class="am-btn am-btn-soft" style="color: var(--brand);">Renvoyer</button>
                                    </form>
                                @else
                                    <a href="{{ $upgradeUrl }}" class="am-btn am-btn-soft" style="color:#9a6700;background:#fff7ed;border-color:rgba(154,103,0,.16);">Renvoyer</a>
                                @endif

                                <a href="{{ $canUseGiftVouchers ? route('pro.gift-vouchers.show', $v) : $upgradeUrl }}" class="am-btn am-btn-brand">Voir</a>
                            </div>
                        </div>
                    @empty
                        <div class="p-10 text-center">
                            <div class="text-lg font-extrabold text-slate-900">{{ $canUseGiftVouchers ? 'Aucun bon cadeau' : 'Vos futurs bons cadeaux apparaîtront ici' }}</div>
                            <div class="mt-1 text-sm text-slate-600">{{ $canUseGiftVouchers ? 'Crée ton premier bon cadeau.' : 'Codes, statuts, PDF, relance email et suivi d’utilisation seront centralisés dans cet écran.' }}</div>
                            <div class="mt-4">
                                <a href="{{ $canUseGiftVouchers ? route('pro.gift-vouchers.create') : $upgradeUrl }}" class="am-btn am-btn-brand px-4 py-2">
                                    {{ $canUseGiftVouchers ? '+ Nouveau bon cadeau' : 'Débloquer les bons cadeaux' }}
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Desktop --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full text-sm am-table">
                        <thead style="background: linear-gradient(90deg, rgba(100,122,11,0.10), rgba(107,79,42,0.08));">
                            <tr class="text-left text-slate-700">
                                <th class="px-4 py-3 font-extrabold">Code</th>
                                <th class="px-4 py-3 font-extrabold">Acheteur</th>
                                <th class="px-4 py-3 font-extrabold">Montant</th>
                                <th class="px-4 py-3 font-extrabold">Restant</th>
                                <th class="px-4 py-3 font-extrabold">Statut</th>
                                <th class="px-4 py-3 font-extrabold">Expiration</th>
                                <th class="px-4 py-3 font-extrabold text-right">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($vouchers as $v)
                                @php
                                    $label = method_exists($v, 'statusLabel') ? $v->statusLabel() : ($v->is_active ? 'Actif' : 'Désactivé');
                                    $badgeStyle = 'color:#334155;background:rgba(15,23,42,0.04);';
                                    if ($label === 'Actif') $badgeStyle='color:var(--brand);border-color:rgba(100,122,11,0.25);background:rgba(100,122,11,0.06);';
                                    if ($label === 'Expiré') $badgeStyle='color:var(--brown);border-color:rgba(107,79,42,0.25);background:rgba(107,79,42,0.07);';
                                    if ($label === 'Épuisé') $badgeStyle='color:#1d4ed8;border-color:rgba(29,78,216,0.20);background:rgba(29,78,216,0.06);';
                                    if ($label === 'Désactivé') $badgeStyle='color:#b91c1c;border-color:rgba(185,28,28,0.20);background:rgba(185,28,28,0.06);';
                                @endphp

                                <tr class="am-row">
                                    <td class="px-4 py-3">
                                        <a href="{{ $canUseGiftVouchers ? route('pro.gift-vouchers.show', $v) : $upgradeUrl }}"
                                           class="font-extrabold text-slate-900 hover:underline">
                                            {{ $v->code }}
                                        </a>
                                        <div class="text-xs text-slate-600 mt-1">
                                            {{ $v->created_at->timezone('Europe/Paris')->format('d/m/Y') }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="font-bold text-slate-900">{{ $v->buyer_name ?: '—' }}</div>
                                        <div class="text-xs text-slate-600">{{ $v->buyer_email }}</div>
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap font-extrabold text-slate-900">
                                        {{ $v->originalAmountStr() }}
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap font-extrabold text-slate-900">
                                        {{ $v->remainingAmountStr() }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <span class="am-badge" style="{{ $badgeStyle }}">
                                            <span class="am-dot" style="background: currentColor;"></span>
                                            {{ $label }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                                        {{ $v->expiresAtStr() ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        <div class="inline-flex flex-wrap justify-end gap-2">
                                            <a href="{{ $canUseGiftVouchers ? route('pro.gift-vouchers.pdf', $v) : $upgradeUrl }}" class="am-btn am-btn-brown">PDF</a>

                                            @if($canUseGiftVouchers)
                                                <form action="{{ route('pro.gift-vouchers.resend', $v) }}" method="POST">
                                                    @csrf
                                                    <button class="am-btn am-btn-soft" style="color: var(--brand);">
                                                        Renvoyer
                                                    </button>
                                                </form>
                                            @else
                                                <a href="{{ $upgradeUrl }}" class="am-btn am-btn-soft" style="color:#9a6700;background:#fff7ed;border-color:rgba(154,103,0,.16);">
                                                    Renvoyer
                                                </a>
                                            @endif

                                            <a href="{{ $canUseGiftVouchers ? route('pro.gift-vouchers.show', $v) : $upgradeUrl }}" class="am-btn am-btn-brand">Voir</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center">
                                        <div class="mx-auto max-w-md">
                                            <div class="text-lg font-extrabold text-slate-900">{{ $canUseGiftVouchers ? 'Aucun bon cadeau' : 'Un espace de suivi prêt à l’emploi' }}</div>
                                            <div class="mt-1 text-sm text-slate-600">{{ $canUseGiftVouchers ? 'Crée ton premier bon cadeau en 30 secondes.' : 'Vous pourrez créer vos bons cadeaux, consulter leur solde restant, télécharger le PDF et relancer les emails depuis cet écran.' }}</div>
                                            <div class="mt-4">
                                                <a href="{{ $canUseGiftVouchers ? route('pro.gift-vouchers.create') : $upgradeUrl }}"
                                                   class="am-btn am-btn-brand px-4 py-2">
                                                    {{ $canUseGiftVouchers ? '+ Nouveau bon cadeau' : 'Débloquer les bons cadeaux' }}
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-200/70 bg-white">
                    {{ $vouchers->links() }}
                </div>
            </div>
            </div>

            @unless($canUseGiftVouchers)
                <div class="am-lock-overlay">
                    <div class="am-lock-overlay-card">
                        <div class="am-lock-kicker">Gestion verrouillée</div>
                        <div class="am-lock-title">Suivez vos ventes, vos soldes et vos relances sans friction</div>
                        <p class="am-lock-text">
                            La page est prête : filtres, PDF, renvoi d’email et suivi du montant restant.
                            Passez sur la formule {{ $requiredLabel }} pour l’utiliser en vrai.
                        </p>
                        <div class="am-lock-list">
                            <div>Relance email en 1 clic pour l’acheteur ou le bénéficiaire.</div>
                            <div>Export PDF immédiat avec le visuel configuré sur votre compte.</div>
                            <div>Gestion du solde restant quand le bon cadeau est utilisé en plusieurs fois.</div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ $upgradeUrl }}" class="am-upgrade-btn inline-flex items-center justify-center">
                                Débloquer les bons cadeaux
                            </a>
                        </div>
                    </div>
                </div>
            @endunless
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('giftVoucherBackgroundInput');
            const preview = document.getElementById('giftVoucherBackgroundPreview');
            const hint = document.getElementById('giftVoucherBackgroundPreviewHint');
            if (!input || !preview || !hint) return;

            input.addEventListener('change', function () {
                const file = input.files && input.files[0] ? input.files[0] : null;
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function (event) {
                    preview.style.backgroundImage = "url('" + event.target.result + "')";
                    preview.style.backgroundSize = 'cover';
                    preview.style.backgroundPosition = 'center';
                    hint.textContent = 'Aperçu local: ce visuel sera utilisé après enregistrement.';
                };
                reader.readAsDataURL(file);
            });
        });
    </script>
</x-app-layout>
