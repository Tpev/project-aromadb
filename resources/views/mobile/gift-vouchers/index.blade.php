@php
    $money = fn (int $cents) => number_format($cents / 100, 2, ',', ' ') . ' EUR';
    $total = $allVouchers->count();
    $active = $allVouchers->filter->isUsable()->count();
    $remaining = (int) $allVouchers->sum('remaining_amount_cents');
    $expired = $allVouchers->filter->isExpired()->count();
    $statusOptions = [
        'all' => 'Tous',
        'active' => 'Actifs',
        'expired' => 'Expires',
        'exhausted' => 'Epuises',
        'disabled' => 'Desactives',
    ];
    $statusLabel = function ($voucher) {
        if (! $voucher->is_active) {
            return 'Desactive';
        }
        if ($voucher->isExpired()) {
            return 'Expire';
        }
        if ((int) $voucher->remaining_amount_cents <= 0) {
            return 'Epuise';
        }
        return 'Actif';
    };
    $statusClass = function ($voucher) {
        if ($voucher->isUsable()) {
            return 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]';
        }
        if (! $voucher->is_active) {
            return 'border-red-200 bg-red-50 text-red-700';
        }
        if ($voucher->isExpired()) {
            return 'border-amber-200 bg-amber-50 text-amber-700';
        }
        return 'border-gray-200 bg-gray-50 text-gray-600';
    };
@endphp

<x-mobile-layout title="Bons cadeaux">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-gift text-sm"></i>
                </div>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">Bons cadeaux</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Ventes, codes, soldes restants et utilisations.
                </p>
            </div>

            <a href="{{ route('mobile.menu') }}"
               class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-gray-500 shadow-sm"
               aria-label="Retour au menu">
                <i class="fas fa-bars text-xs"></i>
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-[#d7dfaa] bg-[#647a0b]/10 p-3 text-sm font-medium text-[#4f6108]">
                {{ session('success') }}
            </div>
        @endif

        @unless($canUseGiftVouchers)
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white text-amber-700">
                        <i class="fas fa-lock text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="font-semibold">Fonction verrouillee</h2>
                        <p class="mt-1 leading-snug">
                            Les bons cadeaux sont inclus dans les formules Pro, Premium, Trial et comptes historiques.
                        </p>
                    </div>
                </div>
                <a href="{{ route('pro.gift-vouchers.index') }}"
                   class="mt-3 inline-flex h-10 w-full items-center justify-center rounded-lg bg-white px-3 text-xs font-semibold text-amber-900 shadow-sm">
                    Voir les details web
                </a>
            </div>
        @endunless

        <div class="mb-4 grid grid-cols-3 gap-2">
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Bons</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $total }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Actifs</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $active }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Solde</div>
                <div class="mt-1 truncate text-sm font-semibold text-gray-900">{{ $money($remaining) }}</div>
            </div>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-2">
            @if($canUseGiftVouchers)
                <a href="{{ route('mobile.gift-vouchers.create') }}"
                   class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-sm font-semibold text-white shadow-sm active:scale-[0.99]">
                    Ajouter
                </a>
            @else
                <span class="inline-flex h-11 items-center justify-center rounded-lg bg-gray-100 px-3 text-sm font-semibold text-gray-400">
                    Ajouter
                </span>
            @endif
            <a href="{{ route('pro.gift-vouchers.index') }}"
               class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm active:scale-[0.99]">
                Vue web
            </a>
        </div>

        @if($allVouchers->isNotEmpty())
            <div class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm">
                <label class="flex h-10 items-center gap-2 rounded-lg bg-[#f7f8f1] px-3">
                    <i class="fas fa-search text-[11px] text-gray-400"></i>
                    <input type="search"
                           id="mobileGiftVoucherSearch"
                           placeholder="Code, acheteur, beneficiaire"
                           class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-gray-800 focus:ring-0"
                           oninput="filterMobileGiftVouchers()">
                </label>

                <div class="mt-3 grid grid-cols-3 gap-2">
                    @foreach($statusOptions as $key => $label)
                        <a href="{{ route('mobile.gift-vouchers.index', ['status' => $key]) }}"
                           class="inline-flex h-9 items-center justify-center rounded-full border px-2 text-[11px] font-semibold {{ $status === $key ? 'border-[#647a0b] bg-[#647a0b] text-white' : 'border-[#e4e8d5] bg-white text-gray-600' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if($vouchers->isEmpty())
            <div class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-5 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-gift text-sm"></i>
                </div>
                <h2 class="text-sm font-semibold text-gray-900">
                    {{ $total ? 'Aucun resultat' : 'Aucun bon cadeau' }}
                </h2>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    {{ $total ? 'Changez le filtre pour retrouver vos bons cadeaux.' : 'Creez un bon cadeau pour generer un code, un PDF et suivre le solde.' }}
                </p>
            </div>
        @else
            <div id="mobileGiftVoucherList" class="space-y-2">
                @foreach($vouchers as $voucher)
                    @php
                        $searchText = Str::lower(trim(
                            $voucher->code . ' ' .
                            ($voucher->buyer_name ?? '') . ' ' .
                            ($voucher->buyer_email ?? '') . ' ' .
                            ($voucher->recipient_name ?? '') . ' ' .
                            ($voucher->recipient_email ?? '')
                        ));
                    @endphp

                    <a href="{{ $canUseGiftVouchers ? route('mobile.gift-vouchers.show', $voucher) : route('pro.gift-vouchers.index') }}"
                       class="block rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm active:scale-[0.99]"
                       data-gift-voucher="{{ $searchText }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h2 class="truncate text-sm font-semibold text-gray-900">
                                    {{ $voucher->code }}
                                </h2>
                                <p class="mt-1 line-clamp-2 text-xs leading-snug text-gray-600">
                                    {{ $voucher->recipient_name ?: 'Beneficiaire non renseigne' }}
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $statusClass($voucher) }}">
                                {{ $statusLabel($voucher) }}
                            </span>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Solde</div>
                                <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $money((int) $voucher->remaining_amount_cents) }}</div>
                            </div>
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Initial</div>
                                <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $money((int) $voucher->original_amount_cents) }}</div>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                {{ $voucher->buyer_name ?: 'Acheteur non renseigne' }}
                            </span>
                            <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                {{ $voucher->expires_at ? 'Expire le ' . $voucher->expiresAtStr() : 'Sans expiration' }}
                            </span>
                            @if($voucher->sale_invoice_id)
                                <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                    Facture liee
                                </span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        function filterMobileGiftVouchers() {
            const input = document.getElementById('mobileGiftVoucherSearch');
            const filter = input ? input.value.toLowerCase() : '';
            const items = document.querySelectorAll('#mobileGiftVoucherList > a');

            items.forEach((item) => {
                const text = item.getAttribute('data-gift-voucher') || '';
                item.style.display = text.includes(filter) ? '' : 'none';
            });
        }
    </script>
</x-mobile-layout>
