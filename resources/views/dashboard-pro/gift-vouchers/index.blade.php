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

            @if($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-900">
                    <div class="text-sm font-extrabold">Erreur de configuration</div>
                    <ul class="mt-1 text-sm list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="am-card p-4 mb-4">
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
