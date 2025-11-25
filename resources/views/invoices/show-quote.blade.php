<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Détails du devis') }} – #{{ $quote->quote_number ?? '—' }}
        </h2>
    </x-slot>

    <div class="container-fluid mt-5">
        <div class="details-container mx-auto p-4">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <h1 class="details-title">{{ __('Devis n°') }} {{ $quote->quote_number ?? '—' }}</h1>

            @php
                $cp      = $quote->clientProfile;
                $company = $cp->company ?? null;

                $billingFirst = $cp->first_name_billing ?: $cp->first_name;
                $billingLast  = $cp->last_name_billing  ?: $cp->last_name;
            @endphp

            {{-- Infos en-tête --}}
            <div class="invoice-info-boxes row mt-4">

                {{-- Bénéficiaire (toujours la fiche client) --}}
                <div class="col-md-4">
                    <div class="invoice-box d-flex align-items-center">
                        <i class="fas fa-user icon"></i>
                        <div class="invoice-details">
                            <p class="invoice-label">{{ __('Bénéficiaire') }}</p>
                            <p class="invoice-value">
                                {{ $cp->first_name }} {{ $cp->last_name }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Facturé à (entreprise + contact de facturation le cas échéant) --}}
                <div class="col-md-4">
                    <div class="invoice-box d-flex align-items-center">
                        <i class="fas fa-building icon"></i>
                        <div class="invoice-details">
                            <p class="invoice-label">{{ __('Facturé à') }}</p>
                            <p class="invoice-value">
                                @if($company)
                                    <strong>{{ $company->name }}</strong><br>
                                    <span>À l’attention de {{ $billingFirst }} {{ $billingLast }}</span>
                                @else
                                    {{ $billingFirst }} {{ $billingLast }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Date du devis --}}
                <div class="col-md-4">
                    <div class="invoice-box d-flex align-items-center">
                        <i class="fas fa-calendar-alt icon"></i>
                        <div class="invoice-details">
                            <p class="invoice-label">{{ __('Date du Devis') }}</p>
                            <p class="invoice-value">{{ $quote->invoice_date->translatedFormat('d F Y') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Valable jusqu'au --}}
                @if($quote->due_date)
                    <div class="col-md-4">
                        <div class="invoice-box d-flex align-items-center">
                            <i class="fas fa-calendar-check icon"></i>
                            <div class="invoice-details">
                                <p class="invoice-label">{{ __('Valable Jusqu\'au') }}</p>
                                <p class="invoice-value">{{ $quote->due_date->translatedFormat('d F Y') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Totaux --}}
                <div class="col-md-4">
                    <div class="invoice-box d-flex align-items-center">
                        <i class="fas fa-money-bill-wave icon"></i>
                        <div class="invoice-details">
                            <p class="invoice-label">{{ __('Total HT') }}</p>
                            <p class="invoice-value">
                                {{ number_format($quote->total_amount,2,',',' ') }} €
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="invoice-box d-flex align-items-center">
                        <i class="fas fa-money-bill-wave icon"></i>
                        <div class="invoice-details">
                            <p class="invoice-label">{{ __('Total TVA') }}</p>
                            <p class="invoice-value">
                                {{ number_format($quote->total_tax_amount,2,',',' ') }} €
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="invoice-box d-flex align-items-center">
                        <i class="fas fa-money-bill-wave icon"></i>
                        <div class="invoice-details">
                            <p class="invoice-label">{{ __('Total TTC') }}</p>
                            <p class="invoice-value">
                                {{ number_format($quote->total_amount_with_tax,2,',',' ') }} €
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Statut --}}
                <div class="col-md-4">
                    <div class="invoice-box d-flex align-items-center">
                        <i class="fas fa-info-circle icon"></i>
                        <div class="invoice-details">
                            <p class="invoice-label">{{ __('Statut') }}</p>
                            <p class="invoice-value">{{ ucfirst($quote->status) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tableau des articles --}}
            <div class="row mt-4">
                <div class="col-md-12">
                    <h2 class="details-subtitle">{{ __('Articles du devis') }}</h2>
                    @if($quote->items->isEmpty())
                        <p>{{ __('Aucun article dans ce devis.') }}</p>
                    @else
                        <div class="table-responsive">
                            <table id="invoiceItemsTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Produit / Article') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Quantité') }}</th>
                                        <th>{{ __('P.U. HT (€)') }}</th>
                                        <th>{{ __('TVA (%)') }}</th>
                                        <th>{{ __('Total HT (€)') }}</th>
                                        <th>{{ __('Montant TVA (€)') }}</th>
                                        <th>{{ __('Total TTC (€)') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($quote->items as $item)
                                        <tr>
                                            {{-- Colonne Type --}}
                                            <td>
                                                @if($item->type === 'inventory')
                                                    Inv.
                                                @elseif($item->type === 'product')
                                                    Prest.
                                                @else
                                                    Autre
                                                @endif
                                            </td>

                                            {{-- Nom : produit ou inventaire ou fallback description --}}
                                            <td>
                                                @if($item->type === 'product' && $item->product)
                                                    {{ $item->product->name }}
                                                @elseif($item->type === 'inventory' && $item->inventoryItem)
                                                    {{ $item->inventoryItem->name }}
                                                @else
                                                    {{ $item->description }}
                                                @endif
                                            </td>

                                            {{-- Autres champs --}}
                                            <td>{{ $item->description }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ number_format($item->unit_price,2,',',' ') }} €</td>
                                            <td>{{ number_format($item->tax_rate,2,',',' ') }}%</td>
                                            <td>{{ number_format($item->total_price,2,',',' ') }} €</td>
                                            <td>{{ number_format($item->tax_amount,2,',',' ') }} €</td>
                                            <td>{{ number_format($item->total_price_with_tax,2,',',' ') }} €</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Notes --}}
            @if($quote->notes)
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h2 class="details-subtitle">{{ __('Notes') }}</h2>
                        <p>{{ $quote->notes }}</p>
                    </div>
                </div>
            @endif

            {{-- Boutons d’action --}}
            <div class="row mt-4 text-center">
                <div class="col-md-12">
                    <a href="{{ route('invoices.index',['type'=>'quote']) }}" class="btn-primary">
                        {{ __('Retour à la liste') }}
                    </a>
                    <a href="{{ route('invoices.editQuote',$quote->id) }}" class="btn-secondary">
                        {{ __('Modifier le devis') }}
                    </a>

                    {{-- Envoi par email --}}
                    @if(is_null($quote->sent_at))
                        <form action="{{ route('quotes.send.email',$quote->id) }}" method="POST" style="display:inline">
                            @csrf
                            <button type="submit" class="btn-secondary">
                                <i class="fas fa-envelope"></i> {{ __('Envoyer par Email') }}
                            </button>
                        </form>
                    @else
                        <span class="email-sent-indicator">
                            <i class="fas fa-check-circle"></i>
                            {{ __('Envoyé le') }} {{ $quote->sent_at->format('d/m/Y H:i') }}
                        </span>
                    @endif

                    {{-- PDF --}}
                    <a href="{{ route('invoices.quotePdf',$quote->id) }}" class="btn-primary">
                        {{ __('Télécharger le PDF') }}
                    </a>

                    {{-- Accepter / Refuser --}}
                    @unless(in_array($quote->status, ['Devis Accepté','Devis Refusé']))
                        <form action="{{ route('quotes.updateStatus',$quote) }}" method="POST" class="inline-block">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="Devis Accepté">
                            <button type="submit" class="btn-primary">{{ __('Accepter') }}</button>
                        </form>
                        <form action="{{ route('quotes.updateStatus',$quote) }}" method="POST" class="inline-block">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="Devis Refusé">
                            <button type="submit" class="btn-secondary">{{ __('Refuser') }}</button>
                        </form>
                    @endunless
                </div>
            </div>
        </div>
    </div>

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

        .email-sent-indicator {
            font-size: 1rem;
            color: #28a745;
            display: flex;
            align-items: center;
        }
        .email-sent-indicator i {
            margin-right: 5px;
        }

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

            .email-sent-indicator {
                font-size: 0.9rem;
            }
        }
    </style>
</x-app-layout>
