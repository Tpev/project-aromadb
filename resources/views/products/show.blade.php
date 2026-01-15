<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Détails de la Prestation') }}
        </h2>
    </x-slot>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">

            {{-- Image --}}
            @if($product->image)
                <div class="product-image-container mb-4">
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="product-image">
                </div>
            @endif

            <h1 class="details-title flex items-center justify-center gap-3">
                {{ $product->name }}

                {{-- Badge: fiche d’émargement requise (only if true) --}}
                @if($product->requires_emargement)
                    <span class="badge badge-green" title="Fiche d’émargement requise">
                        <i class="fas fa-file-signature"></i> {{ __('Émargement requis') }}
                    </span>
                @endif
            </h1>

            <!-- Info grid -->
            <div class="product-info-boxes">
                @php
                    // Legacy-safe: if null, treat as visible
                    $isVisibleInPortal = is_null($product->visible_in_portal) ? true : (bool)$product->visible_in_portal;
                @endphp

                <!-- Nom -->
                <div class="product-box">
                    <i class="fas fa-tag icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Nom de la Prestation') }}</p>
                        <p class="product-value">{{ $product->name }}</p>
                    </div>
                </div>

                <!-- Description -->
                <div class="product-box">
                    <i class="fas fa-info-circle icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Description') }}</p>
                        <p class="product-value">{{ $product->description ?: 'N/A' }}</p>
                    </div>
                </div>

                <!-- Prix -->
                <div class="product-box">
                    <i class="fas fa-euro-sign icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Prix (€)') }}</p>
                        <p class="product-value">{{ number_format((float)$product->price, 2, ',', ' ') }}</p>
                    </div>
                </div>

                <!-- TVA -->
                <div class="product-box">
                    <i class="fas fa-percentage icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('TVA (%)') }}</p>
                        <p class="product-value">{{ number_format((float)$product->tax_rate, 2, ',', ' ') }}%</p>
                    </div>
                </div>

                <!-- Durée -->
                <div class="product-box">
                    <i class="fas fa-clock icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Durée (en minutes)') }}</p>
                        <p class="product-value">{{ $product->duration ?: 'N/A' }}</p>
                    </div>
                </div>

                <!-- Réservable en ligne -->
                <div class="product-box">
                    <i class="fas fa-calendar-alt icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Réservable en ligne') }}</p>
                        <p class="product-value">
                            @if($product->can_be_booked_online)
                                <span class="chip chip-green">{{ __('Oui') }}</span>
                            @else
                                <span class="chip chip-gray">{{ __('Non') }}</span>
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Visible sur le portail (legacy-safe) -->
                <div class="product-box">
                    <i class="fas fa-eye icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Visible sur le portail') }}</p>
                        <p class="product-value">
                            @if($isVisibleInPortal)
                                <span class="chip chip-green">
                                    <i class="fas fa-eye"></i> {{ __('Visible') }}
                                </span>
                                @if(is_null($product->visible_in_portal))
                                    <small class="text-gray-500" style="margin-left:.5rem;">
                                        ({{ __('hérité par défaut') }})
                                    </small>
                                @endif
                            @else
                                <span class="chip chip-gray">
                                    <i class="fas fa-eye-slash"></i> {{ __('Masqué') }}
                                </span>
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Mode de prestation -->
                <div class="product-box">
                    <i class="fas fa-map-marker-alt icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Mode de Prestation') }}</p>
                        <p class="product-value">
{{ method_exists($product, 'getConsultationModes') ? $product->getConsultationModes() : implode(', ', array_filter([
    $product->visio ? __('Visio') : null,
    $product->adomicile ? __('À domicile') : null,
    ($product->en_entreprise ?? false) ? __('En entreprise') : null,
    $product->dans_le_cabinet ? __('Dans le cabinet') : null,
])) }}

                        </p>
                    </div>
                </div>

                <!-- Max séances / jour -->
                <div class="product-box">
                    <i class="fas fa-users icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Nombre maximum de séances par jour') }}</p>
                        <p class="product-value">{{ $product->max_per_day ?: 'N/A' }}</p>
                    </div>
                </div>

                <!-- Brochure -->
                @if($product->brochure)
                    <div class="product-box">
                        <i class="fas fa-file-pdf icon"></i>
                        <div class="product-details">
                            <p class="product-label">{{ __('Brochure') }}</p>
                            <p class="product-value">
                                <a href="{{ asset('storage/' . $product->brochure) }}" target="_blank" class="link">
                                    {{ __('Télécharger la brochure') }}
                                </a>
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="action-buttons mt-4">
                <a href="{{ route('products.edit', $product->id) }}" class="btn-primary">
                    <i class="fas fa-edit"></i> {{ __('Modifier') }}
                </a>

                <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-secondary"
                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette prestation ?')">
                        <i class="fas fa-trash-alt"></i> {{ __('Supprimer') }}
                    </button>
                </form>

                <a href="{{ route('products.index') }}" class="btn-secondary">
                    <i class="fas fa-list"></i> {{ __('Retour à la liste') }}
                </a>

                <a href="{{ route('products.duplicate', $product->id) }}" class="btn-primary mt-4">
                    <i class="fas fa-clone"></i> {{ __('Dupliquer la Prestation') }}
                </a>
            </div>

            <!-- Factures associées -->
            @if($invoices->count() > 0)
                <h2 class="details-subtitle mt-5">{{ __('Factures associées') }}</h2>
                <div class="table-responsive mx-auto">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Facture') }}</th>
                                <th>{{ __('Client') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Montant Total (€)') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                                <tr onclick="window.location='{{ route('invoices.show', $invoice->id) }}';">
                                    <td>{{ $invoice->invoice_number }}</td>
                                    <td>
                                        @php $cp = $invoice->clientProfile ?? null; @endphp
                                        {{ $cp ? trim(($cp->first_name ?? '').' '.($cp->last_name ?? '')) : '—' }}
                                    </td>
                                    <td>
                                        @if($invoice->invoice_date instanceof \Illuminate\Support\Carbon || $invoice->invoice_date instanceof \Carbon\Carbon)
                                            {{ $invoice->invoice_date->format('d/m/Y') }}
                                        @elseif(!empty($invoice->invoice_date))
                                            {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ number_format((float)$invoice->total_amount, 2, ',', ' ') }}</td>
                                    <td>
                                        <a href="{{ route('invoices.show', $invoice->id) }}" class="btn-primary btn-sm">
                                            {{ __('Voir') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="mt-3">{{ __('Aucune facture associée à cette prestation.') }}</p>
            @endif
        </div>
    </div>

    <!-- Styles -->
    <style>
        .container { max-width: 900px; text-align: center; }

        .details-container {
            background-color: #f9f9f9; border-radius: 10px; padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin: 0 auto;
        }

        .details-title {
            font-size: 2rem; font-weight: bold; color: #647a0b;
            margin-bottom: 12px; text-align: center;
        }

        .badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 10px; border-radius: 9999px; font-size: 0.8rem; font-weight: 600;
        }
        .badge-green { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }

        .product-image-container { text-align: center; }
        .product-image { max-width: 100%; height: auto; border-radius: 10px; object-fit: cover; }

        .product-info-boxes { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; margin-top: 10px; }

        .product-box {
            display: flex; align-items: center; background-color: #ffffff;
            border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            padding: 18px; transition: transform 0.2s; width: 45%; min-width: 300px;
        }
        .product-box:hover { transform: translateY(-2px); }

        .icon { font-size: 1.6rem; color: #854f38; margin-right: 14px; }
        .product-details { text-align: left; }
        .product-label { font-weight: bold; color: #647a0b; margin: 0 0 4px; }
        .product-value { color: #333333; font-size: 1rem; word-wrap: break-word; }

        .chip { display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:9999px; font-weight:600; }
        .chip-green { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
        .chip-gray  { background:#f3f4f6; color:#374151; border:1px solid #e5e7eb; }

        .link { color: #4f46e5; text-decoration: underline; }
        .link:hover { color: #3730a3; }

        .action-buttons { margin-top: 20px; text-align: center; }
        .btn-primary, .btn-secondary, .btn-primary.btn-sm {
            padding: 10px 16px; border-radius: 6px; text-decoration: none; display: inline-block; cursor: pointer;
        }
        .btn-primary { background-color: #647a0b; color: #fff; border: none; }
        .btn-primary:hover { background-color: #854f38; }
        .btn-primary.btn-sm { font-size: 0.9rem; padding: 8px 12px; }

        .btn-secondary { background-color: transparent; color: #854f38; border: 1px solid #854f38; }
        .btn-secondary:hover { background-color: #854f38; color: #fff; }

        .details-subtitle {
            font-size: 1.5rem; font-weight: bold; color: #647a0b;
            margin-top: 40px; margin-bottom: 12px; text-align: left;
        }

        .table-responsive {
            background-color: #ffffff; border-radius: 8px; padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin: 0 auto;
        }

        .table { width: 100%; text-align: center; border-collapse: collapse; }
        .table thead { background-color: #647a0b; color: #ffffff; }
        .table th, .table td { padding: 10px 8px; vertical-align: middle; }
        .table tbody tr { cursor: pointer; transition: background-color 0.2s, color 0.2s, transform 0.2s; }
        .table tbody tr:hover { background-color: #854f38; color: #ffffff; transform: scale(1.01); }
    </style>
</x-app-layout>
