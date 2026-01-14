{{-- resources/views/dashboard-pro/gift-vouchers/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-semibold text-xl" style="color:#647a0b;">
                    Bon cadeau
                </h2>
                <p class="mt-1 text-xs text-slate-600">
                    Code : <span class="font-extrabold text-slate-900">{{ $voucher->code }}</span>
                    • créé le {{ $voucher->created_at->timezone('Europe/Paris')->format('d/m/Y') }}
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-2">
                <a href="{{ route('pro.gift-vouchers.index') }}"
                   class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold ring-1 ring-slate-200 bg-white hover:bg-slate-50 transition"
                   style="color:#6b4f2a;">
                    ← Retour
                </a>

                <a href="{{ route('pro.gift-vouchers.pdf', $voucher) }}"
                   class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-95 transition"
                   style="background:#647a0b;">
                    Télécharger PDF
                </a>
            </div>
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
        .am-btn{
            border-radius: .95rem;
            padding: .6rem .9rem;
            font-size: .8rem;
            font-weight: 900;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:.45rem;
            transition:.15s ease;
            white-space: nowrap;
        }
        .am-btn-soft{ background: white; border: 1px solid rgba(15,23,42,0.10); }
        .am-btn-soft:hover{ background: rgba(100,122,11,0.06); }
        .am-btn-brand{ background: var(--brand); color:white; box-shadow: 0 12px 26px rgba(100,122,11,0.18); }
        .am-btn-brand:hover{ opacity:.95; }
        .am-btn-brown{
            background: rgba(107,79,42,0.10);
            color: var(--brown);
            border: 1px solid rgba(107,79,42,0.20);
        }
        .am-btn-brown:hover{ background: rgba(107,79,42,0.14); }
        .am-btn-danger{
            background: rgba(185,28,28,0.06);
            color: #b91c1c;
            border: 1px solid rgba(185,28,28,0.20);
        }
        .am-btn-danger:hover{ background: rgba(185,28,28,0.10); }
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

            @if($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-900">
                    <div class="text-sm font-extrabold">Erreur</div>
                    <ul class="mt-1 text-sm list-disc pl-5">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $label = $voucher->statusLabel();
                $badgeStyle = 'color: #334155; background: rgba(15,23,42,0.04);';
                if ($label === 'Actif') $badgeStyle = 'color: var(--brand); border-color: rgba(100,122,11,0.25); background: rgba(100,122,11,0.06);';
                if ($label === 'Expiré') $badgeStyle = 'color: var(--brown); border-color: rgba(107,79,42,0.25); background: rgba(107,79,42,0.07);';
                if ($label === 'Épuisé') $badgeStyle = 'color: #1d4ed8; border-color: rgba(29,78,216,0.20); background: rgba(29,78,216,0.06);';
                if ($label === 'Désactivé') $badgeStyle = 'color: #b91c1c; border-color: rgba(185,28,28,0.20); background: rgba(185,28,28,0.06);';
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                {{-- Main --}}
                <div class="lg:col-span-2 space-y-4">

                    <div class="am-card p-4 md:p-6">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="am-badge" style="{{ $badgeStyle }}">
                                        <span class="am-dot" style="background: currentColor;"></span>
                                        {{ $label }}
                                    </span>

                                    @if($voucher->expires_at)
                                        <span class="am-badge" style="color: var(--brown); border-color: rgba(107,79,42,0.20); background: rgba(107,79,42,0.07);">
                                            ⏳ Valable jusqu’au {{ $voucher->expiresAtStr() }}
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-3">
                                    <div class="text-xs font-semibold text-slate-500">Code secret</div>
                                    <div class="mt-1 flex items-center gap-2">
                                        <div class="text-lg md:text-xl font-extrabold text-slate-900 tracking-wide">
                                            {{ $voucher->code }}
                                        </div>
                                        <button type="button"
                                                class="am-btn am-btn-soft"
                                                style="color: var(--brand);"
                                                onclick="navigator.clipboard.writeText('{{ $voucher->code }}')">
                                            Copier
                                        </button>
                                    </div>
                                    <div class="text-xs text-slate-600 mt-1">Utilisez ce code pour retrouver / vérifier le bon cadeau.</div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200/70 bg-white p-4 min-w-[220px]">
                                <div class="text-xs font-semibold text-slate-500">Montant initial</div>
                                <div class="text-xl font-extrabold text-slate-900">{{ $voucher->originalAmountStr() }}</div>

                                <div class="mt-3 text-xs font-semibold text-slate-500">Solde restant</div>
                                <div class="text-2xl font-extrabold text-slate-900">{{ $voucher->remainingAmountStr() }}</div>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <a href="{{ route('pro.gift-vouchers.pdf', $voucher) }}" class="am-btn am-btn-brown">
                                PDF
                            </a>

                            <form action="{{ route('pro.gift-vouchers.resend', $voucher) }}" method="POST">
                                @csrf
                                <button class="am-btn am-btn-soft" style="color: var(--brand);">
                                    Renvoyer emails
                                </button>
                            </form>

                            <form action="{{ route('pro.gift-vouchers.disable', $voucher) }}" method="POST"
                                  onsubmit="return confirm('Désactiver ce bon cadeau ?');">
                                @csrf
                                <button class="am-btn am-btn-danger">
                                    Désactiver
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="am-card">
                        <div class="p-4 md:p-6 border-b border-slate-200/70">
                            <div class="text-sm font-extrabold text-slate-900">Personnes</div>
                            <div class="text-xs text-slate-600 mt-0.5">Emails envoyés à l’acheteur, et au bénéficiaire si renseigné.</div>
                        </div>

                        <div class="p-4 md:p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="rounded-2xl border border-slate-200/70 bg-white p-4">
                                <div class="text-xs font-bold text-slate-500 uppercase tracking-wide">Acheteur</div>
                                <div class="mt-1 text-sm font-extrabold text-slate-900">{{ $voucher->buyer_name ?: '—' }}</div>
                                <div class="text-sm text-slate-700">{{ $voucher->buyer_email }}</div>
                            </div>

                            <div class="rounded-2xl border border-slate-200/70 bg-white p-4">
                                <div class="text-xs font-bold text-slate-500 uppercase tracking-wide">Bénéficiaire</div>
                                <div class="mt-1 text-sm font-extrabold text-slate-900">{{ $voucher->recipient_name ?: '—' }}</div>
                                <div class="text-sm text-slate-700">{{ $voucher->recipient_email ?: '—' }}</div>
                            </div>

                            <div class="md:col-span-2 rounded-2xl border border-slate-200/70 bg-slate-50 p-4">
                                <div class="text-xs font-bold text-slate-500 uppercase tracking-wide">Message</div>
                                <div class="mt-1 text-sm text-slate-800">
                                    {{ $voucher->message ?: '—' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="am-card">
                        <div class="p-4 md:p-6 border-b border-slate-200/70">
                            <div class="text-sm font-extrabold text-slate-900">Déduire un montant</div>
                            <div class="text-xs text-slate-600 mt-0.5">Utilisable en plusieurs fois. L’historique est conservé.</div>
                        </div>

                        <div class="p-4 md:p-6">
                            @if(!$voucher->isUsable())
                                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900">
                                    <div class="text-sm font-extrabold">Bon non utilisable</div>
                                    <div class="text-sm mt-0.5">
                                        Statut : {{ $label }}.
                                    </div>
                                </div>
                            @endif

                            <form action="{{ route('pro.gift-vouchers.redeem', $voucher) }}" method="POST" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                                @csrf

                                <div>
                                    <div class="am-label">Montant à déduire (€)</div>
                                    <input class="am-input" name="amount_eur" type="number" step="0.01" min="0.01"
                                           @if(!$voucher->isUsable()) disabled @endif
                                           placeholder="Ex: 35">
                                </div>

                                <div class="md:col-span-2">
                                    <div class="am-label">Note (optionnel)</div>
                                    <input class="am-input" name="note" type="text"
                                           @if(!$voucher->isUsable()) disabled @endif
                                           placeholder="Ex: séance du 12/01, réglé en espèces…">
                                </div>

                                <div class="md:col-span-3 flex justify-end">
                                    <button class="am-btn am-btn-brand"
                                            @if(!$voucher->isUsable()) disabled style="opacity:.5;cursor:not-allowed;" @endif>
                                        Déduire
                                    </button>
                                </div>
                            </form>

                            <div class="mt-2 text-xs text-slate-600">
                                Solde restant actuel : <span class="font-extrabold text-slate-900">{{ $voucher->remainingAmountStr() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- History --}}
                <div class="am-card">
                    <div class="p-4 md:p-6 border-b border-slate-200/70">
                        <div class="text-sm font-extrabold text-slate-900">Historique</div>
                        <div class="text-xs text-slate-600 mt-0.5">Toutes les déductions sont enregistrées.</div>
                    </div>

                    <div class="p-4 md:p-6 space-y-3">
                        @forelse($voucher->redemptions as $r)
                            <div class="rounded-2xl border border-slate-200/70 bg-white p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-extrabold text-slate-900">
                                            -{{ number_format($r->amount_cents / 100, 2, ',', ' ') }} €
                                        </div>
                                        @if($r->note)
                                            <div class="text-sm text-slate-700 mt-1">{{ $r->note }}</div>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-500 whitespace-nowrap">
                                        {{ $r->created_at->timezone('Europe/Paris')->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-slate-200/70 bg-slate-50 p-6 text-center">
                                <div class="text-sm font-extrabold text-slate-900">Aucune utilisation</div>
                                <div class="text-sm text-slate-600 mt-1">Les déductions apparaîtront ici.</div>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
