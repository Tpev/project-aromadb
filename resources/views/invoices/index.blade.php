{{-- resources/views/invoices/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight">
            {{ __('Liste des Factures') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Titre de la Page -->
            <h1 class="text-3xl font-bold text-[#647a0b] text-center">
                {{ __('Liste des Factures') }}
            </h1>

@php
    $user = auth()->user();
    $canUseBilling = $user->canUseFeature('facturation'); // Feature key for invoices & quotes

    // Determine required license plan
    $plansConfig = config('license_features.plans', []);
    $familyOrder = ['free', 'starter', 'pro', 'premium']; // Ignore trial

    $requiredFamily = null;
    foreach ($familyOrder as $family) {
        if (in_array('facturation', $plansConfig[$family] ?? [], true)) {
            $requiredFamily = $family;
            break;
        }
    }

    $familyLabels = [
        'free'    => __('Gratuit'),
        'starter' => __('Starter'),
        'pro'     => __('PRO'),
        'premium' => __('Premium'),
    ];

    $requiredLabel = $requiredFamily
        ? ($familyLabels[$requiredFamily] ?? $requiredFamily)
        : __('une formule supérieure');
@endphp


{{-- ====================================================
     SECTION: Créer une FACTURE
   ==================================================== --}}
<div class="flex flex-col md:flex-row md:justify-between items-center mb-4 space-y-4 md:space-y-0">

    {{-- Search bar --}}
    <div class="w-full md:w-auto">
        <input type="text"
               id="search"
               class="border border-[#854f38] rounded-md py-2 px-4 w-full md:w-80 
                      focus:outline-none focus:ring-2 focus:ring-[#854f38]"
               placeholder="{{ __('Recherche par client ou statut...') }}"
               onkeyup="filterTable()">
    </div>

    {{-- Button wrapper to allow pill positioning --}}
    <div class="relative inline-flex">

        @if($canUseBilling)
            {{-- Green button (normal) --}}
            <a href="{{ route('invoices.create') }}"
               class="bg-[#647a0b] text-white px-4 py-2 rounded-md hover:bg-[#854f38]
                      transition duration-200 flex items-center justify-center">

                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2"
                     fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 5a1 1 0 011 1v3h3a1 1 0 
                             110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 
                             0 110-2h3V6a1 1 0 011-1z" />
                </svg>

                {{ __('Créer une facture') }}
            </a>

        @else
            {{-- Greyed-out billing button --}}
            <a href="/license-tiers/pricing"
               class="px-4 py-2 rounded-md bg-gray-200 border border-gray-300
                      text-gray-600 flex items-center justify-center cursor-pointer
                      transition hover:bg-gray-300">

                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500"
                     fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 5a1 1 0 011 1v3h3a1 1 
                             0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 
                             1 0 110-2h3V6a1 1 0 011-1z" />
                </svg>

                {{ __('Créer une facture') }}
            </a>

            {{-- Floating pill --}}
            <div class="absolute -top-2 -right-2 bg-[#fff1d6] border border-[#facc15]/40 
                        px-2 py-0.5 text-[10px] rounded-full font-semibold text-[#854f38]
                        shadow-sm flex items-center gap-1">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                          d="M10 2a4 4 0 00-4 4v2H5a2 
                             2 0 00-2 2v6a2 2 0 002 2h10a2 
                             2 0 002-2v-6a2 2 0 00-2-2h-1V6
                             a4 4 0 00-4-4zm0 6a2 2 0 00-2
                             2v2a2 2 0 104 0v-2a2 2 0 
                             00-2-2z" clip-rule="evenodd"/>
                </svg>

                {{ __('À partir de :') }}
                <strong>{{ $requiredLabel }}</strong>
            </div>
        @endif

    </div>
</div>


            <!-- Tableau -->
            <div class="bg-white shadow overflow-hidden rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="invoiceTable">
                        <thead class="bg-[#647a0b] text-white">
                            <tr>
                                <th onclick="sortTable(0)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer">
                                    {{ __('Numéro de Facture') }}
                                    <!-- Icône de Tri -->
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block ml-1 transform transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6 8l4 4 4-4H6z" />
                                    </svg>
                                </th>
                                <th onclick="sortTable(1)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer">
                                    {{ __('Client') }}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block ml-1 transform transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6 8l4 4 4-4H6z" />
                                    </svg>
                                </th>
                                <th onclick="sortTable(2)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer">
                                    {{ __('Date de Facture') }}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block ml-1 transform transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6 8l4 4 4-4H6z" />
                                    </svg>
                                </th>
                                <th onclick="sortTable(3)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer">
                                    {{ __('Montant Total TTC') }}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block ml-1 transform transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6 8l4 4 4-4H6z" />
                                    </svg>
                                </th>
                                <th onclick="sortTable(4)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer">
                                    {{ __('Statut') }}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block ml-1 transform transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6 8l4 4 4-4H6z" />
                                    </svg>
                                </th>
                                <th onclick="sortTable(5)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer">
                                    {{ __('Envoyée') }}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block ml-1 transform transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6 8l4 4 4-4H6z" />
                                    </svg>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($invoices as $invoice)
                                <tr class="hover:bg-gray-100 cursor-pointer" onclick="window.location='{{ route('invoices.show', $invoice->id) }}'">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $invoice->invoice_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $corp = null;
                                            if (!empty($invoice->corporate_client_id)) {
                                                $corp = $invoice->corporateClient ?? null;
                                                if (!$corp) {
                                                    $corp = \App\Models\CorporateClient::find($invoice->corporate_client_id);
                                                }
                                            }
                                            $client = $invoice->clientProfile ?? null;
                                        @endphp
                                        @if($invoice->corporate_client_id && $corp)
                                            {{ $corp->trade_name ?: $corp->name }}
                                        @else
                                            {{ $client?->first_name }} {{ $client?->last_name }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ number_format($invoice->total_amount_with_tax, 2, ',', ' ') }} €
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ ucfirst($invoice->status) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(is_null($invoice->sent_at))
                                            <span class="bg-[#854f38] text-white px-2 py-1 rounded-full text-xs font-semibold inline-flex items-center">
                                                <!-- Icône Non Envoyée -->
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 9a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V10a1 1 0 011-1z" />
                                                </svg>
                                                {{ __('Non Envoyée') }}
                                            </span>
                                        @else
                                            <span class="bg-green-500 text-white px-2 py-1 rounded-full text-xs font-semibold inline-flex items-center">
                                                <!-- Icône Envoyée -->
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M16.707 5.293a1 1 0 00-1.414 0L9 11.586 6.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l7-7a1 1 0 000-1.414z" />
                                                </svg>
                                                {{ __('Envoyée le') }} {{ \Carbon\Carbon::parse($invoice->sent_at)->format('d/m/Y à H:i') }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            @if($invoices->isEmpty())
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        {{ __('Aucune facture trouvée.') }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
			
			
            <h1 class="text-3xl font-bold text-[#647a0b] text-center">
                {{ __('Liste des Devis') }}
            </h1>

{{-- ====================================================
     SECTION: Créer un DEVIS
   ==================================================== --}}
<div class="flex flex-col md:flex-row md:justify-between items-center mb-4 space-y-4 md:space-y-0">

    {{-- Spacer left --}}
    <div class="w-full md:w-auto"></div>

    <div class="relative inline-flex">

        @if($canUseBilling)
            <a href="{{ route('invoices.createQuote') }}"
               class="bg-[#647a0b] text-white px-4 py-2 rounded-md hover:bg-[#854f38]
                      transition duration-200 flex items-center justify-center">

                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2"
                     fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 5a1 1 0 011 1v3h3a1 1 
                             0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 
                             1 0 110-2h3V6a1 1 0 011-1z" />
                </svg>

                {{ __('Créer un devis') }}
            </a>

        @else
            <a href="/license-tiers/pricing"
               class="px-4 py-2 rounded-md bg-gray-200 border border-gray-300
                      text-gray-600 flex items-center justify-center cursor-pointer
                      transition hover:bg-gray-300">

                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500"
                     fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 5a1 1 0 011 1v3h3a1 1 
                             0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 
                             1 0 110-2h3V6a1 1 0 011-1z" />
                </svg>

                {{ __('Créer un devis') }}
            </a>

            {{-- Pill --}}
            <div class="absolute -top-2 -right-2 bg-[#fff1d6] border border-[#facc15]/40 
                        px-2 py-0.5 text-[10px] rounded-full font-semibold text-[#854f38]
                        shadow-sm flex items-center gap-1">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                          d="M10 2a4 4 0 00-4 4v2H5a2 
                             2 0 00-2 2v6a2 2 0 002 2h10a2 
                             2 0 002-2v-6a2 2 0 00-2-2h-1V6
                             a4 4 0 00-4-4zm0 6a2 2 0 00-2
                             2v2a2 2 0 104 0v-2a2 2 0 
                             00-2-2z" clip-rule="evenodd"/>
                </svg>

                {{ __('À partir de :') }}
                <strong>{{ $requiredLabel }}</strong>
            </div>
        @endif

    </div>
</div>

<table class="min-w-full divide-y divide-gray-200" id="quoteTable">
    <thead class="bg-[#647a0b] text-white">
        <tr>
            <th onclick="sortQuoteTable(0)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer">
                {{ __('Numéro de Devis') }}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block ml-1 transform transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M6 8l4 4 4-4H6z" />
                </svg>
            </th>
            <th onclick="sortQuoteTable(1)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer">
                {{ __('Client') }}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block ml-1 transform transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M6 8l4 4 4-4H6z" />
                </svg>
            </th>
            <th onclick="sortQuoteTable(2)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer">
                {{ __('Date du Devis') }}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block ml-1 transform transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M6 8l4 4 4-4H6z" />
                </svg>
            </th>
            <th onclick="sortQuoteTable(3)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer">
                {{ __('Montant Total TTC') }}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block ml-1 transform transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M6 8l4 4 4-4H6z" />
                </svg>
            </th>
            <th onclick="sortQuoteTable(4)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer">
                {{ __('Statut') }}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block ml-1 transform transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M6 8l4 4 4-4H6z" />
                </svg>
            </th>
			                                <th onclick="sortTable(5)" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer">
                                    {{ __('Envoyée') }}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block ml-1 transform transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6 8l4 4 4-4H6z" />
                                    </svg>
                                </th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @foreach($quotes as $quote)
            <tr class="hover:bg-gray-100 cursor-pointer" onclick="window.location='{{ route('invoices.showQuote', $quote->id) }}'">
                <td class="px-6 py-4 whitespace-nowrap">
                    {{ $quote->quote_number ?? '—' }}

                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @php
                                            $corp = null;
                                            if (!empty($quote->corporate_client_id)) {
                                                $corp = $quote->corporateClient ?? null;
                                                if (!$corp) {
                                                    $corp = \App\Models\CorporateClient::find($quote->corporate_client_id);
                                                }
                                            }
                                            $client = $quote->clientProfile ?? null;
                                        @endphp
                                        @if($quote->corporate_client_id && $corp)
                                            {{ $corp->trade_name ?: $corp->name }}
                                        @else
                                            {{ $client?->first_name }} {{ $client?->last_name }}
                                        @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    {{ \Carbon\Carbon::parse($quote->invoice_date)->format('d/m/Y') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    {{ number_format($quote->total_amount_with_tax, 2, ',', ' ') }} €
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    {{ ucfirst($quote->status) }}
                </td>
				                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(is_null($quote->sent_at))
                                            <span class="bg-[#854f38] text-white px-2 py-1 rounded-full text-xs font-semibold inline-flex items-center">
                                                <!-- Icône Non Envoyée -->
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 9a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V10a1 1 0 011-1z" />
                                                </svg>
                                                {{ __('Non Envoyée') }}
                                            </span>
                                        @else
                                            <span class="bg-green-500 text-white px-2 py-1 rounded-full text-xs font-semibold inline-flex items-center">
                                                <!-- Icône Envoyée -->
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M16.707 5.293a1 1 0 00-1.414 0L9 11.586 6.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l7-7a1 1 0 000-1.414z" />
                                                </svg>
                                                {{ __('Envoyée le') }} {{ \Carbon\Carbon::parse($invoice->sent_at)->format('d/m/Y à H:i') }}
                                            </span>
                                        @endif
                                    </td>
            </tr>
        @endforeach

        @if($quotes->isEmpty())
            <tr>
                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                    {{ __('Aucun devis trouvé.') }}
                </td>
            </tr>
        @endif
    </tbody>
</table>






        </div>

    <!-- Scripts pour le tri et le filtrage -->
    <script>
        // Objet global pour mémoriser la direction de tri par colonne
        var sortDirections = {};

        // Variables pour les chaînes internationalisées
        var nonEnvoyeeText = @json(__('Non Envoyée'));
        var envoyeeLeText = @json(__('Envoyée le'));

function filterTable() {
    let input = document.getElementById('search');
    let filter = input.value.toLowerCase();

    // Filter invoice table
    let invoiceTable = document.getElementById('invoiceTable');
    if (invoiceTable) {
        let invoiceRows = invoiceTable.getElementsByTagName('tr');
        for (let i = 1; i < invoiceRows.length; i++) {
            let tdClient = invoiceRows[i].getElementsByTagName('td')[1];
            let tdStatus = invoiceRows[i].getElementsByTagName('td')[4];
            if (tdClient && tdStatus) {
                let txtValueClient = tdClient.textContent || tdClient.innerText;
                let txtValueStatus = tdStatus.textContent || tdStatus.innerText;
                if (txtValueClient.toLowerCase().includes(filter) || txtValueStatus.toLowerCase().includes(filter)) {
                    invoiceRows[i].style.display = '';
                } else {
                    invoiceRows[i].style.display = 'none';
                }
            }
        }
    }

    // Filter quote table
    let quoteTable = document.getElementById('quoteTable');
    if (quoteTable) {
        let quoteRows = quoteTable.getElementsByTagName('tr');
        for (let i = 1; i < quoteRows.length; i++) {
            let tdClient = quoteRows[i].getElementsByTagName('td')[1];
            let tdStatus = quoteRows[i].getElementsByTagName('td')[4];
            if (tdClient && tdStatus) {
                let txtValueClient = tdClient.textContent || tdClient.innerText;
                let txtValueStatus = tdStatus.textContent || tdStatus.innerText;
                if (txtValueClient.toLowerCase().includes(filter) || txtValueStatus.toLowerCase().includes(filter)) {
                    quoteRows[i].style.display = '';
                } else {
                    quoteRows[i].style.display = 'none';
                }
            }
        }
    }
}

        function sortTable(n) {
            let table = document.getElementById('invoiceTable');
            let tbody = table.tBodies[0];
            let rows = Array.from(tbody.rows);
            let dir = sortDirections[n] === 'asc' ? 'desc' : 'asc';
            sortDirections[n] = dir;

            rows.sort(function(a, b) {
                let x = a.cells[n].textContent.trim();
                let y = b.cells[n].textContent.trim();

                // Gestion des différents types de données
                if (n === 0 || n === 3) { // Numéro de facture ou Montant Total
                    x = parseFloat(x.replace(',', '.').replace(/[^0-9.-]+/g,""));
                    y = parseFloat(y.replace(',', '.').replace(/[^0-9.-]+/g,""));
                } else if (n === 2) { // Date de Facture
                    x = new Date(x.split('/').reverse().join('-'));
                    y = new Date(y.split('/').reverse().join('-'));
                } else if (n === 5) { // Envoyée (sent_at)
                    if (x.includes(nonEnvoyeeText)) {
                        x = 0;
                    } else {
                        let dateStr = x.split(envoyeeLeText + ' ')[1];
                        let [datePart, timePart] = dateStr.split(' à ');
                        x = new Date(datePart.split('/').reverse().join('-') + 'T' + timePart);
                    }

                    if (y.includes(nonEnvoyeeText)) {
                        y = 0;
                    } else {
                        let dateStr = y.split(envoyeeLeText + ' ')[1];
                        let [datePart, timePart] = dateStr.split(' à ');
                        y = new Date(datePart.split('/').reverse().join('-') + 'T' + timePart);
                    }
                } else {
                    x = x.toLowerCase();
                    y = y.toLowerCase();
                }

                if (dir === 'asc') {
                    if (x > y) return 1;
                    if (x < y) return -1;
                    return 0;
                } else {
                    if (x < y) return 1;
                    if (x > y) return -1;
                    return 0;
                }
            });

            // Replacer les lignes triées dans le tableau
            for (let i = 0; i < rows.length; i++) {
                tbody.appendChild(rows[i]);
            }

            // Mettre à jour les icônes de tri
            let ths = table.getElementsByTagName('th');
            for (let i = 0; i < ths.length; i++) {
                let icon = ths[i].getElementsByTagName('svg')[0];
                if (i === n) {
                    if (dir === 'asc') {
                        icon.classList.remove('rotate-180');
                    } else {
                        icon.classList.add('rotate-180');
                    }
                } else {
                    icon.classList.remove('rotate-180');
                    sortDirections[i] = null;
                }
            }
        }
		
		function sortQuoteTable(n) {
    let table = document.getElementById('quoteTable');
    let tbody = table.tBodies[0];
    let rows = Array.from(tbody.rows);
    let dir = sortDirections['q' + n] === 'asc' ? 'desc' : 'asc';
    sortDirections['q' + n] = dir;

    rows.sort(function(a, b) {
        let x = a.cells[n].textContent.trim();
        let y = b.cells[n].textContent.trim();

        if (n === 0 || n === 3) { // Numéro de devis or montant
            x = parseFloat(x.replace(',', '.').replace(/[^0-9.-]+/g,""));
            y = parseFloat(y.replace(',', '.').replace(/[^0-9.-]+/g,""));
        } else if (n === 2) { // Date
            x = new Date(x.split('/').reverse().join('-'));
            y = new Date(y.split('/').reverse().join('-'));
        } else {
            x = x.toLowerCase();
            y = y.toLowerCase();
        }

        if (dir === 'asc') {
            return x > y ? 1 : (x < y ? -1 : 0);
        } else {
            return x < y ? 1 : (x > y ? -1 : 0);
        }
    });

    rows.forEach(row => tbody.appendChild(row));

    // Update sort icons
    let ths = table.getElementsByTagName('th');
    for (let i = 0; i < ths.length; i++) {
        let icon = ths[i].getElementsByTagName('svg')[0];
        if (i === n) {
            if (dir === 'asc') {
                icon.classList.remove('rotate-180');
            } else {
                icon.classList.add('rotate-180');
            }
        } else {
            icon.classList.remove('rotate-180');
            sortDirections['q' + i] = null;
        }
    }
}

    </script>

    <!-- Styles Personnalisés -->
    <style>
        /* Styles pour les icônes de tri */
        .rotate-180 {
            transform: rotate(180deg);
        }

        /* Ajustements pour les petits écrans */
        @media (max-width: 640px) {
            .md\:flex-row {
                flex-direction: column;
            }
            .md\:justify-between {
                justify-content: flex-start;
            }
            .md\:space-y-0 > :not([hidden]) ~ :not([hidden]) {
                --tw-space-y-reverse: 0;
                margin-top: 1rem;
            }
            .md\:w-auto {
                width: 100%;
            }
        }
    </style>
</x-app-layout>
