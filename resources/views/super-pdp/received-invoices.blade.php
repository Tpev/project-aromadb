<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Factures reçues SUPER PDP') }}
        </h2>
    </x-slot>

    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="rounded-2xl border border-lime-100 bg-white shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-lime-50 to-white">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <div class="text-xs uppercase tracking-[0.18em] font-bold" style="color:#854f38;">
                            {{ __('SUPER PDP sandbox') }}
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900 mt-1">
                            {{ __('Inbox des factures d’achat') }}
                        </h1>
                        <p class="text-sm text-gray-600 mt-2 max-w-2xl">
                            {{ __('Synchronisez les factures entrantes depuis SUPER PDP. Pour l’instant, cette page est réservée au compte de test.') }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('profile.editCompanyInfo') }}" class="btn-secondary">
                            {{ __('Retour aux connexions') }}
                        </a>
                        <a href="{{ route('super-pdp.received-invoices.index', ['sync' => 1]) }}" class="btn-primary">
                            {{ __('Synchroniser maintenant') }}
                        </a>
                    </div>
                </div>

                <div class="grid md:grid-cols-3 gap-3 mt-6">
                    <div class="rounded-xl border border-lime-100 bg-white p-4">
                        <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Connexion') }}</div>
                        <div class="font-semibold text-gray-900 mt-1">
                            {{ $connection->isConnected() ? __('Connectée') : __('Non connectée') }}
                        </div>
                    </div>
                    <div class="rounded-xl border border-lime-100 bg-white p-4">
                        <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Entreprise') }}</div>
                        <div class="font-semibold text-gray-900 mt-1">
                            {{ $connection->super_pdp_company_name ?: __('Non renseignée') }}
                        </div>
                    </div>
                    <div class="rounded-xl border border-lime-100 bg-white p-4">
                        <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Dernière synchro') }}</div>
                        <div class="font-semibold text-gray-900 mt-1">
                            {{ $connection->last_synced_at ? $connection->last_synced_at->format('d/m/Y H:i') : __('Jamais') }}
                        </div>
                    </div>
                </div>
            </div>

            @if(!$connection->receiving_invoices_enabled)
                <div class="m-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    {{ __('La réception dans AromaMade est désactivée. Activez-la depuis Informations de l’entreprise pour synchroniser les factures entrantes.') }}
                </div>
            @endif

            @if($connection->last_error)
                <div class="m-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    {{ __('Dernière erreur :') }} {{ $connection->last_error }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">{{ __('Date') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">{{ __('Facture') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">{{ __('Fournisseur') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">{{ __('Montant TTC') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">{{ __('Statut') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wide text-gray-500">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($receivedInvoices as $invoice)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900">
                                        {{ $invoice->invoice_number ?: '#' . $invoice->super_pdp_invoice_id }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        SUPER PDP ID {{ $invoice->super_pdp_invoice_id }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $invoice->seller_name ?: __('Fournisseur inconnu') }}
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                    @if($invoice->total_with_vat !== null)
                                        {{ number_format((float) $invoice->total_with_vat, 2, ',', ' ') }} {{ $invoice->currency_code ?: 'EUR' }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $invoice->latest_event_text ?: __('Synchronisée') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('super-pdp.received-invoices.download', $invoice) }}"
                                       class="text-sm font-semibold text-[#647a0b] hover:underline">
                                        {{ __('Télécharger') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    {{ __('Aucune facture reçue synchronisée pour l’instant.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($receivedInvoices->hasPages())
                <div class="p-6 border-t border-gray-100">
                    {{ $receivedInvoices->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
