@php
    $money = fn (int $cents) => number_format($cents / 100, 2, ',', ' ') . ' EUR';
    $statusLabel = 'Actif';
    $statusClass = 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]';

    if (! $voucher->is_active) {
        $statusLabel = 'Desactive';
        $statusClass = 'border-red-200 bg-red-50 text-red-700';
    } elseif ($voucher->isExpired()) {
        $statusLabel = 'Expire';
        $statusClass = 'border-amber-200 bg-amber-50 text-amber-700';
    } elseif ((int) $voucher->remaining_amount_cents <= 0) {
        $statusLabel = 'Epuise';
        $statusClass = 'border-gray-200 bg-gray-50 text-gray-600';
    }
@endphp

<x-mobile-layout :title="$voucher->code">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4">
            <a href="{{ route('mobile.gift-vouchers.index') }}" class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Bons cadeaux
            </a>

            <div class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                        <i class="fas fa-gift text-sm"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                            <span class="rounded-full bg-[#f5f7eb] px-2 py-0.5 text-[10px] font-medium text-gray-600">
                                {{ $voucher->expires_at ? 'Expire le ' . $voucher->expiresAtStr() : 'Sans expiration' }}
                            </span>
                        </div>
                        <h1 class="mt-2 break-all text-xl font-semibold leading-tight text-gray-900">
                            {{ $voucher->code }}
                        </h1>
                        <p class="mt-1 line-clamp-2 text-sm leading-snug text-gray-600">
                            {{ $voucher->recipient_name ?: 'Beneficiaire non renseigne' }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Solde restant</div>
                        <div class="mt-0.5 truncate text-base font-semibold text-gray-900">{{ $money((int) $voucher->remaining_amount_cents) }}</div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Montant initial</div>
                        <div class="mt-0.5 truncate text-base font-semibold text-gray-900">{{ $money((int) $voucher->original_amount_cents) }}</div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <button type="button"
                            class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700"
                            onclick="navigator.clipboard && navigator.clipboard.writeText('{{ $voucher->code }}')">
                        <i class="fas fa-copy mr-1.5 text-[11px]"></i>
                        Copier
                    </button>
                    <a href="{{ route('mobile.gift-vouchers.pdf', $voucher) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        <i class="fas fa-file-pdf mr-1.5 text-[11px]"></i>
                        PDF
                    </a>
                </div>

                <div class="mt-2 grid grid-cols-2 gap-2">
                    <form method="POST" action="{{ route('mobile.gift-vouchers.resend', $voucher) }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex h-10 w-full items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                            <i class="fas fa-paper-plane mr-1.5 text-[11px]"></i>
                            Renvoyer
                        </button>
                    </form>
                    <a href="{{ route('pro.gift-vouchers.show', $voucher) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        <i class="fas fa-external-link-alt mr-1.5 text-[10px]"></i>
                        Vue web
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-[#d7dfaa] bg-[#647a0b]/10 p-3 text-sm font-medium text-[#4f6108]">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <div class="font-semibold">A corriger</div>
                <ul class="mt-1 list-disc pl-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="space-y-4">
            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Personnes</h2>

                <div class="mt-3 grid grid-cols-1 gap-2">
                    <div class="rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Acheteur</div>
                        <div class="mt-1 text-sm font-semibold text-gray-900">{{ $voucher->buyer_name ?: 'Nom non renseigne' }}</div>
                        <div class="mt-0.5 break-all text-xs text-gray-600">{{ $voucher->buyer_email ?: 'Email non renseigne' }}</div>
                        @if($voucher->buyer_phone)
                            <div class="mt-0.5 text-xs text-gray-600">{{ $voucher->buyer_phone }}</div>
                        @endif
                    </div>

                    <div class="rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Beneficiaire</div>
                        <div class="mt-1 text-sm font-semibold text-gray-900">{{ $voucher->recipient_name ?: 'Nom non renseigne' }}</div>
                        <div class="mt-0.5 break-all text-xs text-gray-600">{{ $voucher->recipient_email ?: 'Email non renseigne' }}</div>
                    </div>
                </div>

                @if($voucher->message)
                    <div class="mt-3 rounded-lg bg-[#f7f8f1] p-3">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Message</div>
                        <p class="mt-1 text-sm leading-snug text-gray-700">{{ $voucher->message }}</p>
                    </div>
                @endif
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Vente</h2>

                <div class="mt-3 grid grid-cols-2 gap-2">
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Canal</div>
                        <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $voucher->sale_channel ?: 'offline_manual' }}</div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Statut vente</div>
                        <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $voucher->sale_status ?: 'paid' }}</div>
                    </div>
                </div>

                @if($voucher->saleInvoice)
                    <a href="{{ route('mobile.invoices.show', $voucher->saleInvoice) }}"
                       class="mt-3 inline-flex h-10 w-full items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        <i class="fas fa-file-invoice mr-1.5 text-[11px]"></i>
                        Ouvrir la facture
                    </a>
                @endif
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Deduction</h2>

                @unless($voucher->isUsable())
                    <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                        Ce bon cadeau n est plus utilisable. Statut : {{ $statusLabel }}.
                    </div>
                @endunless

                <form method="POST" action="{{ route('mobile.gift-vouchers.redeem', $voucher) }}" class="mt-3 space-y-3">
                    @csrf

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Montant a deduire</span>
                        <input type="number"
                               name="amount_eur"
                               step="0.01"
                               min="0.01"
                               inputmode="decimal"
                               placeholder="35"
                               {{ $voucher->isUsable() ? '' : 'disabled' }}
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b] disabled:bg-gray-100">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Note</span>
                        <input type="text"
                               name="note"
                               placeholder="Seance du jour"
                               {{ $voucher->isUsable() ? '' : 'disabled' }}
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b] disabled:bg-gray-100">
                    </label>

                    <button type="submit"
                            {{ $voucher->isUsable() ? '' : 'disabled' }}
                            class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-[#647a0b] text-sm font-semibold text-white disabled:bg-gray-300">
                        Deduire du solde
                    </button>
                </form>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Historique</h2>

                @if($voucher->redemptions->isEmpty())
                    <p class="mt-3 text-sm text-gray-500">Aucune utilisation pour le moment.</p>
                @else
                    <div class="mt-3 space-y-2">
                        @foreach($voucher->redemptions as $redemption)
                            <article class="rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-900">
                                            -{{ $money((int) $redemption->amount_cents) }}
                                        </div>
                                        @if($redemption->note)
                                            <p class="mt-1 line-clamp-2 text-xs leading-snug text-gray-600">{{ $redemption->note }}</p>
                                        @endif
                                    </div>
                                    <span class="shrink-0 text-[11px] font-medium text-gray-500">
                                        {{ $redemption->created_at->timezone('Europe/Paris')->format('d/m/Y') }}
                                    </span>
                                </div>

                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    <span class="rounded-full bg-white px-2 py-1 text-[11px] text-gray-600">
                                        {{ $redemption->status ?: 'applied' }}
                                    </span>
                                    <span class="rounded-full bg-white px-2 py-1 text-[11px] text-gray-600">
                                        {{ $redemption->source ?: 'manual' }}
                                    </span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            @if($voucher->is_active)
                <form method="POST"
                      action="{{ route('mobile.gift-vouchers.disable', $voucher) }}"
                      onsubmit="return confirm('Desactiver ce bon cadeau ?');">
                    @csrf
                    <button type="submit"
                            class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-red-100 bg-red-50 text-sm font-semibold text-red-600">
                        <i class="fas fa-ban mr-1.5 text-[11px]"></i>
                        Desactiver le bon cadeau
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-mobile-layout>
