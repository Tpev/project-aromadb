<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight">
            {{ __('Gestion de l\'inventaire') }}
        </h2>
    </x-slot>

    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>

    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-12 space-y-6">

            <h1 class="text-3xl font-bold text-[#647a0b] text-center">
                {{ __('Inventaire des Articles') }}
            </h1>

@php
    $user = auth()->user();
    $canUseInventory = $user->canUseFeature('inventory');

    // Determine required license family
    $plansConfig = config('license_features.plans', []);
    $familyOrder = ['free', 'starter', 'pro', 'premium']; // trial ignored

    $requiredFamily = null;
    foreach ($familyOrder as $family) {
        if (in_array('inventory', $plansConfig[$family] ?? [], true)) {
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
        ? ($familyLabels[$requiredFamily] ?? ucfirst($requiredFamily))
        : __('une formule supérieure');
@endphp


<div class="flex flex-col md:flex-row md:justify-between items-center mb-4 space-y-4 md:space-y-0">

    <!-- Filter by Brand -->
    <select id="brandFilter"
            class="form-control mb-2"
            onchange="filterByBrand()"
            style="width: 200px;">
        <option value="">Filtrer par Marque</option>
        @php
            $brands = $inventoryItems->pluck('brand')->unique()->filter()->sort();
        @endphp
        @foreach($brands as $brand)
            <option value="{{ trim($brand) }}">{{ trim($brand) }}</option>
        @endforeach
    </select>

    <!-- Search -->
    <input type="text"
           id="search"
           class="border border-[#854f38] rounded-md py-2 px-4 w-full md:w-80
                  focus:outline-none focus:ring-2 focus:ring-[#854f38]"
           placeholder="Recherche par nom ou référence..."
           onkeyup="filterTable()">

    <!-- Button Wrapper -->
    <div class="relative inline-flex w-full md:w-auto">

        @if($canUseInventory)
            {{-- Normal usable button --}}
            <a href="{{ route('inventory_items.create') }}"
               class="bg-[#647a0b] text-white px-4 py-2 rounded-md
                      hover:bg-[#854f38] transition duration-200
                      flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-5 w-5 mr-2" fill="currentColor"
                     viewBox="0 0 20 20">
                    <path d="M10 5a1 1 0 011 1v3h3a1 1 0 
                             110 2h-3v3a1 1 0 
                             11-2 0v-3H6a1 1 0 
                             110-2h3V6a1 1 
                             0 011-1z" />
                </svg>
                {{ __('Ajouter un Article') }}
            </a>

        @else
            {{-- Greyed-out button + redirect --}}
            <a href="/license-tiers/pricing"
               class="px-4 py-2 rounded-md flex items-center justify-center
                      bg-gray-200 text-gray-600 border border-gray-300
                      hover:bg-gray-300 transition duration-200 cursor-pointer"
               style="white-space: nowrap;">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-5 w-5 mr-2" fill="currentColor"
                     viewBox="0 0 20 20">
                    <path d="M10 5a1 1 0 011 1v3h3a1 1 0 
                             110 2h-3v3a1 1 0 
                             11-2 0v-3H6a1 1 
                             0 110-2h3V6a1 1 
                             0 011-1z" />
                </svg>
                {{ __('Ajouter un Article') }}
            </a>

            {{-- Floating upgrade pill --}}
            <div class="absolute -top-3 -right-2 bg-[#fff1d6]
                        border border-[#facc15]/40 rounded-full
                        px-2.5 py-0.5 text-[10px] font-semibold
                        text-[#854f38] shadow-sm flex items-center gap-1">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-3 w-3" fill="currentColor"
                     viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 2a4 4 0 
                           00-4 4v2H5a2 2 0 
                           00-2 2v6a2 2 0 
                           002 2h10a2 2 0 
                           002-2v-6a2 2 0 
                           00-2-2h-1V6a4 4 
                           0 00-4-4zm0 6a2 2 
                           0 00-2 2v2a2 2 
                           0 104 0v-2a2 2 
                           0 00-2-2z"
                        clip-rule="evenodd" />
                </svg>

                {{ __('À partir de :') }} <strong>{{ $requiredLabel }}</strong>
            </div>
        @endif

    </div>
</div>

<div class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="min-w-full divide-y divide-gray-200" id="inventoryTable">

<thead class="bg-[#647a0b] text-white">
    <tr>
        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Nom</th>
        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Référence</th>
        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Description</th>
		<th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Prix Achat TTC</th>
		<th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Prix Vente TTC</th>


        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Prix/ML Achat</th>
        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Prix/ML Vente</th>
        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Stock</th>
        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Unité</th>
        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Marque</th>
        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
    </tr>
</thead>

<tbody>
    @foreach($inventoryItems as $item)
        @php
            $lowStock = false;
            if (in_array($item->unit_type, ['ml', 'drop']) && $item->quantity_per_unit > 0) {
                $percentRemaining = ($item->quantity_remaining / $item->quantity_per_unit) * 100;
                $lowStock = $percentRemaining <= 10;
            } elseif ($item->unit_type === 'unit' && $item->quantity_in_stock <= 0) {
                $lowStock = true;
            }
        @endphp
        <tr class="border-b border-gray-200">

                            <td class="px-4 py-2">{{ $item->name }}</td>

                            <td class="px-4 py-2">{{ $item->reference }}</td>
                            <td class="px-4 py-2">{{ Str::limit($item->description, 50) }}</td>
<td class="px-4 py-2">
    {{ number_format($item->price, 2) }}€ <br>
    <small class="text-muted">
        HT: {{ number_format($item->price_ht, 2) }}€ | TVA {{ $item->vat_rate_purchase }}%
    </small>
</td>

<td class="px-4 py-2">
    {{ number_format($item->selling_price, 2) }}€ <br>
    <small class="text-muted">
        HT: {{ number_format($item->selling_price_ht, 2) }}€ | TVA {{ $item->vat_rate_sale }}%
    </small>
</td>


<td class="px-4 py-2">
    @if(in_array($item->unit_type, ['ml', 'drop']) && $item->quantity_per_unit > 0)
        {{ number_format($item->price / $item->quantity_per_unit, 2) }} €/ml<br>
        <small class="text-muted">
            HT: {{ number_format($item->price_ht / $item->quantity_per_unit, 2) }} €/ml
        </small>
    @else
        –
    @endif
</td>

<td class="px-4 py-2">
    @if(in_array($item->unit_type, ['ml', 'drop']) && $item->quantity_per_unit > 0)
        {{ number_format($item->selling_price / $item->quantity_per_unit, 2) }} €/ml<br>
        <small class="text-muted">
            HT: {{ number_format($item->selling_price_ht / $item->quantity_per_unit, 2) }} €/ml
        </small>
    @else
        –
    @endif
</td>


<td>
    @if(in_array($item->unit_type, ['ml', 'drop', 'gramme']))

        {{ number_format($item->quantity_remaining, 2) }} {{ $item->unit_type }}
        @php
            $percentRemaining = $item->quantity_per_unit > 0
                ? ($item->quantity_remaining / $item->quantity_per_unit) * 100
                : 0;

            $barColor = 'bg-success';
            if ($percentRemaining <= 10) {
                $barColor = 'bg-danger';
            } elseif ($percentRemaining <= 30) {
                $barColor = 'bg-warning';
            }
        @endphp
        <div class="progress mt-2" style="height: 10px;">
            <div class="progress-bar {{ $barColor }}" role="progressbar"
                 style="width: {{ number_format($percentRemaining, 0) }}%;"
                 aria-valuenow="{{ number_format($percentRemaining, 0) }}"
                 aria-valuemin="0" aria-valuemax="100"
                 title="{{ number_format($percentRemaining, 1) }}% restant">
            </div>
        </div>
    @else
        {{ $item->quantity_in_stock }}
        @if($item->quantity_in_stock <= 0)
            <i class="fas fa-exclamation-triangle ms-2" style="color: #e3342f;" title="Hors Stock"></i>
        @endif
    @endif
</td>

                            <td class="px-4 py-2">{{ ucfirst($item->unit_type) }}</td>
                            <td class="px-4 py-2">{{ $item->brand }}</td>
							<td>
@if($item->unit_type === 'unit')
    <button
        class="btn btn-outline-primary btn-sm"
        data-bs-toggle="modal"
        data-bs-target="#consumeUnitModal{{ $item->id }}"
        @if($item->quantity_in_stock < 1) disabled @endif
    >
        <i class="fas fa-box-open me-1"></i>
        Consommer 1 unité
    </button>
@endif


															@if(in_array($item->unit_type, ['ml', 'drop', 'gramme']))
									<button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#consumeModal{{ $item->id }}">
										<i class="fas fa-vial me-1"></i> Consommer
									</button>
								@endif
								<a href="{{ route('inventory_items.edit', $item->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
								<form action="{{ route('inventory_items.destroy', $item->id) }}" method="POST" style="display: inline-block;">
									@csrf
									@method('DELETE')
									<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cet article ?');">
										<i class="fas fa-trash"></i>
									</button>
								</form>

							</td>

                            <td class="d-none">{{ $item->brand }}</td>
                        </tr>
                    @endforeach
                </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@if($inventoryItems->isNotEmpty())	
        <!-- All modals below table -->
        @foreach($inventoryItems as $item)
 @if(in_array($item->unit_type, ['ml', 'drop', 'gramme']))
    <div class="modal fade" id="consumeModal{{ $item->id }}" tabindex="-1" aria-labelledby="consumeModalLabel{{ $item->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-start">
                <form action="{{ route('inventory_items.consume', $item->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="consumeModalLabel{{ $item->id }}">
                            Consommer 
                            @if($item->unit_type === 'ml') des ml
                            @elseif($item->unit_type === 'drop') des gouttes
                            @else des grammes
                            @endif
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        @if($item->unit_type === 'gramme')
                            <div class="mb-3">
                                <label for="amount_gramme_{{ $item->id }}" class="form-label">Quantité (g)</label>
                                <input type="number" step="0.01" name="amount_gramme" id="amount_gramme_{{ $item->id }}" class="form-control">
                            </div>
                        @else
                            <p>Vous pouvez consommer une quantité en gouttes ou en millilitres :</p>

                            <div class="mb-3">
                                <label for="amount_ml_{{ $item->id }}" class="form-label">Quantité (ml)</label>
                                <input type="number" step="0.01" name="amount_ml" id="amount_ml_{{ $item->id }}" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="amount_drops_{{ $item->id }}" class="form-label">Quantité (gouttes)</label>
                                <input type="number" step="1" name="amount_drops" id="amount_drops_{{ $item->id }}" class="form-control">
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Confirmer</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

        @endforeach
    </div>
@if($item->unit_type === 'unit')
    <div class="modal fade" id="consumeUnitModal{{ $item->id }}" tabindex="-1" aria-labelledby="consumeUnitModalLabel{{ $item->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-start">
                <form action="{{ route('inventory_items.consume.unit', $item->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="consumeUnitModalLabel{{ $item->id }}">Confirmer la consommation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <p>Voulez-vous consommer une unité de <strong>{{ $item->name }}</strong> ?</p>
                        <p class="text-muted">Il reste actuellement {{ $item->quantity_in_stock }} unité(s).</p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Oui, consommer</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endif
    <style>
        .rotate-180 {
            transform: rotate(180deg);
        }
        .form-control {
            border: 1px solid #647a0b;
        }
		        .btn-add {
            background-color: #647a0b;
            border: none;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: background-color 0.3s;
            font-size: 1rem;
        }

        .btn-add:hover {
            background-color: #854f38;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 600;
            color: #647a0b;
            margin-bottom: 20px;
            text-align: center;
        }

    </style>

    <script>
        function filterTable() {
            const input = document.getElementById('search');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('inventoryTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const name = rows[i].getElementsByTagName('td')[0]?.innerText.toLowerCase();
                const ref = rows[i].getElementsByTagName('td')[1]?.innerText.toLowerCase();
                rows[i].style.display = (name.includes(filter) || ref.includes(filter)) ? '' : 'none';
            }
        }

        function filterByBrand() {
            const select = document.getElementById('brandFilter');
            const filter = select.value.toLowerCase();
            const table = document.getElementById('inventoryTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const brand = rows[i].getElementsByTagName('td')[7]?.innerText.toLowerCase().trim();
                rows[i].style.display = (brand === filter || filter === '') ? '' : 'none';
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</x-app-layout>