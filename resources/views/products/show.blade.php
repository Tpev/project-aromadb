<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Détails de la Prestation') }}
        </h2>
    </x-slot>

    <!-- Lien vers Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">

            <!-- Afficher l'image du produit si elle existe -->
            @if($product->image)
                <div class="product-image-container mb-4">
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="product-image">
                </div>
            @endif

            <h1 class="details-title">{{ $product->name }}</h1>

            <!-- Informations sur la prestation -->
            <div class="product-info-boxes">
                <!-- Nom de la Prestation -->
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
                        <p class="product-value">{{ $product->description ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- Prix -->
                <div class="product-box">
                    <i class="fas fa-euro-sign icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Prix (€)') }}</p>
                        <p class="product-value">{{ number_format($product->price, 2, ',', ' ') }}</p>
                    </div>
                </div>

                <!-- Taux de Taxe -->
                <div class="product-box">
                    <i class="fas fa-percentage icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Taux de Taxe (%)') }}</p>
                        <p class="product-value">{{ number_format($product->tax_rate, 2, ',', ' ') }}%</p>
                    </div>
                </div>

                <!-- Durée -->
                <div class="product-box">
                    <i class="fas fa-clock icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Durée (en minutes)') }}</p>
                        <p class="product-value">{{ $product->duration ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- Réservable en ligne -->
                <div class="product-box">
                    <i class="fas fa-calendar-alt icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Réservable en ligne') }}</p>
                        <p class="product-value">{{ $product->can_be_booked_online ? 'Oui' : 'Non' }}</p>
                    </div>
                </div>

                <!-- Collecter le Paiement -->
                <div class="product-box">
                    <i class="fas fa-money-check-alt icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Collecter le Paiement') }}</p>
                        <p class="product-value">
                            {{ $product->collect_payment ? 'Oui, un paiement est requis lors de la réservation.' : 'Non, aucun paiement n\'est requis.' }}
                        </p>
                    </div>
                </div>

                <!-- Mode de prestation -->
                <div class="product-box">
                    <i class="fas fa-map-marker-alt icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Mode de Prestation') }}</p>
                        <p class="product-value">{{ $product->getConsultationModes() }}</p>
                    </div>
                </div>

                <!-- Maximum séances par jour -->
                <div class="product-box">
                    <i class="fas fa-users icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Nombre maximum de séances par jour') }}</p>
                        <p class="product-value">{{ $product->max_per_day ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- Brochure Download Link -->
                @if($product->brochure)
                    <div class="product-box">
                        <i class="fas fa-file-pdf icon"></i>
                        <div class="product-details">
                            <p class="product-label">{{ __('Brochure') }}</p>
                            <p class="product-value">
                                <a href="{{ asset('storage/' . $product->brochure) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800">
                                    {{ __('Télécharger la brochure') }}
                                </a>
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Boutons d'action -->
            <div class="action-buttons mt-4">
                <a href="{{ route('products.edit', $product->id) }}" class="btn-primary">{{ __('Modifier') }}</a>
                <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-secondary" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette prestation ?')">{{ __('Supprimer') }}</button>
                </form>
                <a href="{{ route('products.index') }}" class="btn-secondary">{{ __('Retour à la liste') }}</a>
                <a href="{{ route('products.duplicate', $product->id) }}" class="btn-primary mt-4">{{ __('Dupliquer la Prestation') }}</a>
            </div>

            <!-- Factures associées -->
            @if($invoices->count() > 0)
                <h2 class="details-subtitle mt-5">{{ __('Factures Associées') }}</h2>
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
                                    <td>{{ $invoice->clientProfile->first_name }} {{ $invoice->clientProfile->last_name }}</td>
                                    <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                                    <td>{{ number_format($invoice->total_amount, 2, ',', ' ') }}</td>
                                    <td>
                                        <a href="{{ route('invoices.show', $invoice->id) }}" class="btn-primary">{{ __('Voir') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p>{{ __('Aucune facture associée à cette prestation.') }}</p>
            @endif
        </div>
    </div>

    <!-- Styles personnalisés -->
    <style>
        .container {
            max-width: 900px;
            text-align: center;
        }

        .details-container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }

        .details-title {
            font-size: 2rem;
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Product Image Styling */
        .product-image-container {
            text-align: center;
        }

        .product-image {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            object-fit: cover;
        }

        .product-info-boxes {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .product-box {
            display: flex;
            align-items: center;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            transition: transform 0.3s;
            width: 45%;
        }

        .product-box:hover {
            transform: scale(1.05);
        }

        .icon {
            font-size: 2rem;
            color: #854f38;
            margin-right: 15px;
        }

        .product-details {
            text-align: left;
        }

        .product-label {
            font-weight: bold;
            color: #647a0b;
            margin: 0;
        }

        .product-value {
            color: #333333;
            font-size: 1rem;
            word-wrap: break-word;
        }

        .action-buttons {
            margin-top: 20px;
            text-align: center;
        }

        .btn-primary {
            background-color: #647a0b;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            margin-right: 10px;
        }

        .btn-primary:hover {
            background-color: #854f38;
        }

        .btn-secondary {
            background-color: transparent;
            color: #854f38;
            padding: 10px 20px;
            border: 1px solid #854f38;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            margin-right: 10px;
        }

        .btn-secondary:hover {
            background-color: #854f38;
            color: #fff;
        }

        .details-subtitle {
            font-size: 1.5rem;
            font-weight: bold;
            color: #647a0b;
            margin-top: 40px;
            margin-bottom: 20px;
        }

        .table-responsive {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }

        .table {
            width: 100%;
            max-width: 1000px;
            text-align: center;
        }

        .table thead {
            background-color: #647a0b;
            color: #ffffff;
        }

        .table tbody tr {
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s, transform 0.3s;
        }

        .table tbody tr:hover {
            background-color: #854f38;
            color: #ffffff;
            transform: scale(1.02);
        }

        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }
    </style>
</x-app-layout>
