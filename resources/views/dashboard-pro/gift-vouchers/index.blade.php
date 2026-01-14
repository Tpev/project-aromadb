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

            {{-- ✅ Header Create button --}}
            <div class="flex flex-col sm:flex-row gap-2">
                <a href="{{ route('pro.gift-vouchers.create') }}"
                   class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-95 transition"
                   style="background:#647a0b;">
                    + Nouveau bon cadeau
                </a>
            </div>
        </div>
    </x-slot>

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

                    {{-- ✅ Top-right Create button inside the card --}}
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('pro.gift-vouchers.create') }}"
                           class="am-btn am-btn-brand">
                            + Nouveau
                        </a>
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
                                    <a href="{{ route('pro.gift-vouchers.show', $v) }}"
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
                                <a href="{{ route('pro.gift-vouchers.pdf', $v) }}" class="am-btn am-btn-brown">PDF</a>

                                <form action="{{ route('pro.gift-vouchers.resend', $v) }}" method="POST">
                                    @csrf
                                    <button class="am-btn am-btn-soft" style="color: var(--brand);">Renvoyer</button>
                                </form>

                                <a href="{{ route('pro.gift-vouchers.show', $v) }}" class="am-btn am-btn-brand">Voir</a>
                            </div>
                        </div>
                    @empty
                        <div class="p-10 text-center">
                            <div class="text-lg font-extrabold text-slate-900">Aucun bon cadeau</div>
                            <div class="mt-1 text-sm text-slate-600">Crée ton premier bon cadeau.</div>
                            <div class="mt-4">
                                <a href="{{ route('pro.gift-vouchers.create') }}" class="am-btn am-btn-brand px-4 py-2">
                                    + Nouveau bon cadeau
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
                                        <a href="{{ route('pro.gift-vouchers.show', $v) }}"
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
                                            <a href="{{ route('pro.gift-vouchers.pdf', $v) }}" class="am-btn am-btn-brown">PDF</a>

                                            <form action="{{ route('pro.gift-vouchers.resend', $v) }}" method="POST">
                                                @csrf
                                                <button class="am-btn am-btn-soft" style="color: var(--brand);">
                                                    Renvoyer
                                                </button>
                                            </form>

                                            <a href="{{ route('pro.gift-vouchers.show', $v) }}" class="am-btn am-btn-brand">Voir</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center">
                                        <div class="mx-auto max-w-md">
                                            <div class="text-lg font-extrabold text-slate-900">Aucun bon cadeau</div>
                                            <div class="mt-1 text-sm text-slate-600">
                                                Crée ton premier bon cadeau en 30 secondes.
                                            </div>
                                            <div class="mt-4">
                                                <a href="{{ route('pro.gift-vouchers.create') }}"
                                                   class="am-btn am-btn-brand px-4 py-2">
                                                    + Nouveau bon cadeau
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
    </div>
</x-app-layout>
