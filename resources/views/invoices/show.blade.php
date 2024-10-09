<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Détails de la facture') }} - #{{ $invoice->id }}
        </h2>
    </x-slot>

    <!-- Lien vers Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <div class="container-fluid mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Facture n°') }} {{ $invoice->invoice_number }}</h1>
            
            <!-- Bouton pour marquer comme payée -->
            @if($invoice->status !== 'Payée')
                <form action="{{ route('invoices.markAsPaid', $invoice->id) }}" method="POST" class="float-right">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-check"></i> {{ __('Marquer comme Payée') }}
                    </button>
                </form>
            @endif

            <!-- Informations de la facture -->
            <div class="invoice-info-boxes row mt-4">
                <div class="col-md-4">
                    <div class="invoice-box d-flex align-items-center">
                        <i class="fas fa-user icon"></i>
                        <div class="invoice-details">
                            <p class="invoice-label">{{ __('Client') }}</p>
                            <p class="invoice-value">{{ $invoice->clientProfile->first_name }} {{ $invoice->clientProfile->last_name }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="invoice-box d-flex align-items-center">
                        <i class="fas fa-calendar-alt icon"></i>
                        <div class="invoice-details">
                            <p class="invoice-label">{{ __('Date de Facture') }}</p>
                            <p class="invoice-value">{{ \Carbon\Carbon::parse($invoice->invoice_date)->translatedFormat('d F Y') }}</p>
                        </div>
                    </div>
                </div>
                @if($invoice->due_date)
                    <div class="col-md-4">
                        <div class="invoice-box d-flex align-items-center">
                            <i class="fas fa-calendar-check icon"></i>
                            <div class="invoice-details">
                                <p class="invoice-label">{{ __('Date d\'échéance') }}</p>
                                <p class="invoice-value">{{ \Carbon\Carbon::parse($invoice->due_date)->translatedFormat('d F Y') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="col-md-4">
                    <div class="invoice-box d-flex align-items-center">
                        <i class="fas fa-money-bill-wave icon"></i>
                        <div class="invoice-details">
                            <p class="invoice-label">{{ __('Montant Total TTC') }}</p>
                            <p class="invoice-value">{{ number_format($invoice->total_amount_with_tax, 2, ',', ' ') }} €</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="invoice-box d-flex align-items-center">
                        <i class="fas fa-info-circle icon"></i>
                        <div class="invoice-details">
                            <p class="invoice-label">{{ __('Statut') }}</p>
                            <p class="invoice-value">{{ ucfirst($invoice->status) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Articles de la facture -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h2 class="details-subtitle">{{ __('Articles de la facture') }}</h2>

                    @if($invoice->items->isEmpty())
                        <p>Aucun article dans cette facture.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="invoiceItemsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 20%;">{{ __('Produit') }}</th>
                                        <th style="width: 30%;">{{ __('Description') }}</th>
                                        <th style="width: 10%;">{{ __('Quantité') }}</th>
                                        <th style="width: 10%;">{{ __('P.U. (€)') }}</th>
                                        <th style="width: 10%;">{{ __('Taxe (%)') }}</th>
                                        <th style="width: 10%;">{{ __('Total HT (€)') }}</th>
                                        <th style="width: 10%;">{{ __('Montant Taxe (€)') }}</th>
                                        <th style="width: 10%;">{{ __('Total TTC (€)') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->items as $item)
                                        <tr>
                                            <td>{{ $item->product->name }}</td>
                                            <td>{{ $item->description }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ number_format($item->unit_price, 2, ',', ' ') }}</td>
                                            <td>{{ number_format($item->tax_rate, 2, ',', ' ') }}%</td>
                                            <td>{{ number_format($item->total_price, 2, ',', ' ') }} €</td>
                                            <td>{{ number_format($item->tax_amount, 2, ',', ' ') }} €</td>
                                            <td>{{ number_format($item->total_price_with_tax, 2, ',', ' ') }} €</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Totaux de la facture -->
                        <div class="row mt-4">
                            <div class="col-md-6"></div> <!-- Colonne vide pour l'alignement -->
                            <div class="col-md-6">
                                <div class="totals-container">
                                    <p class="total"><strong>{{ __('Total HT') }} :</strong> {{ number_format($invoice->total_amount, 2, ',', ' ') }} €</p>
                                    <p class="total"><strong>{{ __('Total Taxe') }} :</strong> {{ number_format($invoice->total_tax_amount, 2, ',', ' ') }} €</p>
                                    <p class="total"><strong>{{ __('Total TTC') }} :</strong> {{ number_format($invoice->total_amount_with_tax, 2, ',', ' ') }} €</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Notes -->
            @if($invoice->notes)
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h2 class="details-subtitle">{{ __('Notes') }}</h2>
                        <p>{{ $invoice->notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Boutons d'action -->
            <div class="row mt-4">
                <div class="col-md-12 text-center">
                    <a href="{{ route('invoices.index') }}" class="btn-primary">{{ __('Retour à la liste') }}</a>
                    <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn-secondary">{{ __('Modifier la facture') }}</a>
                    <a href="{{ route('invoices.pdf', $invoice->id) }}" class="btn-primary">{{ __('Télécharger le PDF') }}</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Styles personnalisés -->
    <style>
        .container-fluid {
            max-width: 1200px;
        }

        .details-container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }

        .details-title, .details-subtitle {
            font-size: 2rem;
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 20px;
            text-align: center;
        }

        .invoice-info-boxes {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .invoice-box {
            display: flex;
            align-items: center;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            transition: transform 0.3s;
            width: 100%;
        }

        .invoice-box:hover {
            transform: scale(1.02);
        }

        .icon {
            font-size: 2rem;
            color: #854f38;
            margin-right: 15px;
            min-width: 40px;
            text-align: center;
        }

        .invoice-details {
            text-align: left;
            flex: 1;
        }

        .invoice-label {
            font-weight: bold;
            color: #647a0b;
            margin: 0;
        }

        .invoice-value {
            color: #333333;
            font-size: 1rem;
            margin: 5px 0 0 0;
        }

        /* Styles pour la table des articles */
        .table-responsive {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-top: 20px;
        }

        #invoiceItemsTable {
            width: 100%;
            border-collapse: collapse;
        }

        #invoiceItemsTable th, #invoiceItemsTable td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        #invoiceItemsTable thead {
            background-color: #647a0b;
            color: #ffffff;
        }

        #invoiceItemsTable tbody tr:hover {
            background-color: #f1f1f1;
        }

        /* Totaux de la facture */
        .totals-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .totals-container p.total {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333333;
            margin: 10px 0;
        }

        /* Boutons d'action */
        .btn-primary, .btn-secondary {
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            margin: 5px;
            transition: background-color 0.3s, color 0.3s;
        }

        .btn-primary {
            background-color: #647a0b;
            color: #fff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #854f38;
        }

        .btn-secondary {
            background-color: transparent;
            color: #854f38;
            border: 1px solid #854f38;
        }

        .btn-secondary:hover {
            background-color: #854f38;
            color: #fff;
        }

        /* Responsiveness */
        @media (max-width: 992px) {
            .invoice-info-boxes .col-md-4 {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .invoice-box {
                flex-direction: column;
                align-items: flex-start;
            }

            .icon {
                margin-bottom: 10px;
            }

            .totals-container {
                text-align: right;
            }
        }

        @media (max-width: 576px) {
            .details-title, .details-subtitle {
                font-size: 1.5rem;
            }

            .btn-primary, .btn-secondary {
                width: 100%;
                text-align: center;
                margin-bottom: 10px;
            }

            #invoiceItemsTable th, #invoiceItemsTable td {
                padding: 10px;
                font-size: 0.9rem;
            }

            .totals-container p.total {
                font-size: 1rem;
            }
        }
    </style>
</x-app-layout>
