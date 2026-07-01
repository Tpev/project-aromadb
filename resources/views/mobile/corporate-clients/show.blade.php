@php
    $formatMoney = fn ($value) => number_format((float) $value, 2, ',', ' ') . ' EUR';
    $contactName = trim(($company->main_contact_first_name ?? '') . ' ' . ($company->main_contact_last_name ?? ''));
    $addressLines = array_filter([
        $company->billing_address,
        trim(($company->billing_zip ?? '') . ' ' . ($company->billing_city ?? '')),
        $company->billing_country,
    ]);
    $invoiceTotal = $invoices->sum('total_amount_with_tax');
@endphp

<x-mobile-layout :title="$company->name">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4">
            <a href="{{ route('mobile.corporate-clients.index') }}" class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Entreprises
            </a>

            <div class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                        <i class="fas fa-building text-sm"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h1 class="truncate text-xl font-semibold leading-tight text-gray-900">
                            {{ $company->name }}
                        </h1>
                        <p class="mt-1 text-sm leading-snug text-gray-600">
                            {{ $company->trade_name ?: 'Entreprise cliente' }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2">
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Contacts</div>
                        <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $company->client_profiles_count }}</div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Factures</div>
                        <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $invoices->count() }}</div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Total</div>
                        <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $formatMoney($invoiceTotal) }}</div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <a href="{{ route('mobile.corporate-clients.edit', $company) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        <i class="fas fa-edit mr-1.5 text-[11px]"></i>
                        Modifier
                    </a>
                    <a href="{{ route('corporate-clients.show', $company) }}"
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

        <div class="space-y-4">
            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Facturation</h2>

                <div class="mt-3 space-y-2 text-sm text-gray-700">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-id-card mt-1 w-4 text-[11px] text-gray-400"></i>
                        <span class="min-w-0 break-words">
                            {{ $company->siret ?: 'SIRET non renseigne' }}
                            @if($company->vat_number)
                                <br><span class="text-xs text-gray-500">TVA {{ $company->vat_number }}</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="fas fa-map-marker-alt mt-1 w-4 text-[11px] text-gray-400"></i>
                        <span class="min-w-0 break-words">
                            {{ $addressLines ? implode(', ', $addressLines) : 'Adresse non renseignee' }}
                        </span>
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="fas fa-envelope mt-1 w-4 text-[11px] text-gray-400"></i>
                        <span class="min-w-0 break-all">
                            {{ $company->billing_email ?: 'Email facturation non renseigne' }}
                        </span>
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="fas fa-phone mt-1 w-4 text-[11px] text-gray-400"></i>
                        <span>{{ $company->billing_phone ?: 'Telephone facturation non renseigne' }}</span>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2">
                    @if($company->billing_email)
                        <a href="mailto:{{ $company->billing_email }}"
                           class="inline-flex h-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-xs font-semibold text-[#647a0b]">
                            <i class="fas fa-paper-plane mr-1.5 text-[11px]"></i>
                            Email
                        </a>
                    @endif

                    @if($company->billing_phone)
                        <a href="tel:{{ $company->billing_phone }}"
                           class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                            <i class="fas fa-phone-alt mr-1.5 text-[11px]"></i>
                            Appeler
                        </a>
                    @endif
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Contact principal</h2>

                <div class="mt-3 space-y-2 text-sm text-gray-700">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-user w-4 text-[11px] text-gray-400"></i>
                        <span>{{ $contactName ?: 'Contact non renseigne' }}</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="fas fa-envelope mt-1 w-4 text-[11px] text-gray-400"></i>
                        <span class="min-w-0 break-all">{{ $company->main_contact_email ?: 'Email non renseigne' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-phone w-4 text-[11px] text-gray-400"></i>
                        <span>{{ $company->main_contact_phone ?: 'Telephone non renseigne' }}</span>
                    </div>
                </div>
            </section>

            @if($company->notes)
                <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                    <h2 class="text-sm font-semibold text-gray-900">Notes internes</h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-snug text-gray-700">{{ $company->notes }}</p>
                </section>
            @endif

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-gray-900">Contacts rattaches</h2>
                    <a href="{{ route('mobile.clients.create', ['company_id' => $company->id]) }}"
                       class="text-xs font-semibold text-[#647a0b]">
                        Ajouter
                    </a>
                </div>

                @if($company->clientProfiles->isEmpty())
                    <p class="mt-3 text-sm text-gray-500">Aucun client rattache.</p>
                @else
                    <div class="mt-3 space-y-2">
                        @foreach($company->clientProfiles as $client)
                            @php
                                $clientName = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''));
                            @endphp
                            <a href="{{ route('mobile.clients.show', $client) }}"
                               class="block rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3 active:scale-[0.99]">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-semibold text-gray-900">{{ $clientName ?: 'Client sans nom' }}</div>
                                        <div class="mt-1 truncate text-xs text-gray-600">{{ $client->email ?: $client->phone ?: 'Coordonnees manquantes' }}</div>
                                    </div>
                                    <i class="fas fa-chevron-right mt-1 text-[10px] text-gray-300"></i>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-gray-900">Factures et devis</h2>
                    <a href="{{ route('invoices.create', ['company_id' => $company->id]) }}"
                       class="text-xs font-semibold text-[#647a0b]">
                        Facturer
                    </a>
                </div>

                @if($invoices->isEmpty())
                    <p class="mt-3 text-sm text-gray-500">Aucune facture ou devis associe.</p>
                @else
                    <div class="mt-3 space-y-2">
                        @foreach($invoices->take(8) as $invoice)
                            @php
                                $isQuote = ($invoice->type ?? 'invoice') === 'quote';
                                $invoiceHref = $isQuote
                                    ? route('mobile.quotes.show', $invoice)
                                    : route('mobile.invoices.show', $invoice);
                                $number = $isQuote
                                    ? ($invoice->quote_number ?: '#' . $invoice->id)
                                    : ($invoice->invoice_number ?: '#' . $invoice->id);
                            @endphp
                            <a href="{{ $invoiceHref }}"
                               class="block rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3 active:scale-[0.99]">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-semibold text-gray-900">
                                            {{ $isQuote ? 'Devis' : 'Facture' }} {{ $number }}
                                        </div>
                                        <div class="mt-1 text-xs text-gray-600">
                                            {{ optional($invoice->invoice_date)->format('d/m/Y') ?: 'Date manquante' }}
                                            <span class="text-gray-300">/</span>
                                            {{ $formatMoney($invoice->total_amount_with_tax) }}
                                        </div>
                                    </div>
                                    <span class="shrink-0 rounded-full border border-gray-200 bg-white px-2 py-0.5 text-[10px] font-medium text-gray-600">
                                        {{ $invoice->status ?: 'Statut' }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

            <form method="POST"
                  action="{{ route('mobile.corporate-clients.destroy', $company) }}"
                  onsubmit="return confirm('Supprimer cette entreprise ? Les clients rattaches seront detaches.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-red-100 bg-red-50 text-sm font-semibold text-red-600">
                    <i class="fas fa-trash-alt mr-1.5 text-[11px]"></i>
                    Supprimer l entreprise
                </button>
            </form>
        </div>
    </div>
</x-mobile-layout>
