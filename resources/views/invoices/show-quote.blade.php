<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight" style="color:#647a0b;">
            {{ __('Devis') }} - #{{ $quote->quote_number ?? $quote->id }}
        </h2>
    </x-slot>

    <div class="container-fluid mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ __('Détails du devis') }} #{{ $quote->quote_number ?? $quote->id }}</h1>

            {{-- Infos --}}
            <div class="row invoice-info-boxes">
                <div class="col-md-4">
                    <div class="invoice-box d-flex align-items-center">
                        <i class="fas fa-user icon"></i>
                        <div class="invoice-details">
                            <p class="invoice-label">{{ __('Client') }}</p>
                            <p class="invoice-value">{{ $quote->clientProfile->first_name }} {{ $quote->clientProfile->last_name }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="invoice-box d-flex align-items-center">
                        <i class="fas fa-calendar-alt icon"></i>
                        <div class="invoice-details">
                            <p class="invoice-label">{{ __('Date du Devis') }}</p>
                            <p class="invoice-value">{{ optional($quote->invoice_date)->translatedFormat('d F Y') }}</p>
                        </div>
                    </div>
                </div>

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

                <div class="col-md-4">
                    <div class="invoice-box d-flex align-items-center">
                        <i class="fas fa-money-bill-wave icon"></i>
                        <div class="invoice-details">
                            <p class="invoice-label">{{ __('Total HT') }}</p>
                            <p class="invoice-value">{{ number_format($quote->total_amount,2,',',' ') }} €</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="invoice-box d-flex align-items-center">
                        <i class="fas fa-money-bill-wave icon"></i>
                        <div class="invoice-details">
                            <p class="invoice-label">{{ __('Total TVA') }}</p>
                            <p class="invoice-value">{{ number_format($quote->total_tax_amount,2,',',' ') }} €</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="invoice-box d-flex align-items-center">
                        <i class="fas fa-money-bill-wave icon"></i>
                        <div class="invoice-details">
                            <p class="invoice-label">{{ __('Total TTC') }}</p>
                            <p class="invoice-value">{{ number_format($quote->total_amount_with_tax,2,',',' ') }} €</p>
                        </div>
                    </div>
                </div>

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

            {{-- Articles --}}
            <div class="row mt-4">
                <div class="col-md-12">
                    <h2 class="details-subtitle">{{ __('Articles du devis') }}</h2>

                    @if($quote->items->isEmpty())
                        <p>{{ __('Aucun article dans ce devis.') }}</p>
                    @else
                        <div class="table-responsive">
                            <table id="quoteItemsTable" class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th style="width:70px">{{ __('Type') }}</th>
                                    <th style="width:20%">{{ __('Nom') }}</th>
                                    <th style="width:25%">{{ __('Description') }}</th>
                                    <th style="width:8%">{{ __('Qté') }}</th>
                                    <th style="width:10%">{{ __('P.U. HT (€)') }}</th>
                                    <th style="width:8%">{{ __('TVA (%)') }}</th>
                                    <th style="width:10%">{{ __('Remise HT (€)') }}</th>
                                    <th style="width:10%">{{ __('Total HT (€)') }}</th>
                                    <th style="width:10%">{{ __('Montant TVA (€)') }}</th>
                                    <th style="width:10%">{{ __('Total TTC (€)') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($quote->items as $item)
                                    @php
                                        $remiseHt = (float)($item->line_discount_amount_ht ?? 0) + (float)($item->global_discount_amount_ht ?? 0);
                                    @endphp
                                    <tr>
                                        <td>
                                            @if($item->type === 'inventory') Inv.
                                            @elseif($item->type === 'product') Prest.
                                            @else Autre @endif
                                        </td>
                                        <td>
                                            @if($item->type === 'product' && $item->product)
                                                {{ $item->product->name }}
                                            @elseif($item->type === 'inventory' && $item->inventoryItem)
                                                {{ $item->inventoryItem->name }}
                                            @else
                                                {{ $item->description }}
                                            @endif
                                        </td>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ number_format((float)$item->quantity, 2, ',', ' ') }}</td>
                                        <td>{{ number_format((float)$item->unit_price, 2, ',', ' ') }} €</td>
                                        <td>{{ number_format((float)$item->tax_rate, 2, ',', ' ') }}%</td>
                                        <td>{{ number_format($remiseHt, 2, ',', ' ') }} €</td>
                                        <td>{{ number_format((float)$item->total_price, 2, ',', ' ') }} €</td>
                                        <td>{{ number_format((float)$item->tax_amount, 2, ',', ' ') }} €</td>
                                        <td>{{ number_format((float)$item->total_price_with_tax, 2, ',', ' ') }} €</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Totaux (avec rappel remise globale si dispo) --}}
                        <div class="row mt-4">
                            <div class="col-md-6"></div>
                            <div class="col-md-6">
                                <div class="totals-container">
                                    @if((float)($quote->global_discount_amount_ht ?? 0) > 0)
                                        <p class="total">
                                            <strong>{{ __('Remise globale HT') }} :</strong>
                                            -{{ number_format((float)$quote->global_discount_amount_ht, 2, ',', ' ') }} €
                                        </p>
                                    @endif
                                    <p class="total"><strong>{{ __('Total HT') }} :</strong> {{ number_format($quote->total_amount, 2, ',', ' ') }} €</p>
                                    <p class="total"><strong>{{ __('Total TVA') }} :</strong> {{ number_format($quote->total_tax_amount, 2, ',', ' ') }} €</p>
                                    <p class="total"><strong>{{ __('Total TTC') }} :</strong> {{ number_format($quote->total_amount_with_tax, 2, ',', ' ') }} €</p>
                                </div>
                            </div>
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

            {{-- Actions --}}
            <div class="row mt-4 text-center">
                <div class="col-md-12">
                    <a href="{{ route('invoices.index',['type'=>'quote']) }}" class="btn-primary">{{ __('Retour à la liste') }}</a>
                    <a href="{{ route('invoices.editQuote',$quote->id) }}" class="btn-secondary">{{ __('Modifier le devis') }}</a>

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

                    <a href="{{ route('invoices.quotePdf',$quote->id) }}" class="btn-primary">{{ __('Télécharger le PDF') }}</a>

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
        .container-fluid { max-width: 1200px; }
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
        .invoice-info-boxes { display:flex; flex-wrap:wrap; gap:20px; }
        .invoice-box {
            display:flex; align-items:center; background:#fff; border-radius:10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            padding:20px; transition:transform .3s; width:100%;
        }
        .invoice-box:hover { transform: scale(1.02); }
        .icon { font-size:2rem; color:#854f38; margin-right:15px; min-width:40px; text-align:center; }
        .invoice-label { font-weight:bold; color:#647a0b; margin:0; }
        .invoice-value { color:#333; font-size:1rem; margin:5px 0 0 0; }
        .table-responsive { background:#fff; border-radius:8px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,.05); margin-top:20px; }
        #quoteItemsTable { width:100%; border-collapse:collapse; }
        #quoteItemsTable th, #quoteItemsTable td { padding:12px 15px; text-align:left; border-bottom:1px solid #ddd; }
        #quoteItemsTable thead th { background:#647a0b; color:#fff; }
        .btn-primary {
            background-color:#647a0b; color:#fff; padding:10px 20px; border:none; border-radius:5px;
            text-decoration:none; display:inline-block; cursor:pointer; margin:5px;
        }
        .btn-primary:hover { background-color:#854f38; }
        .btn-secondary {
            background:transparent; color:#854f38; padding:10px 20px; border:1px solid #854f38; border-radius:5px;
            text-decoration:none; display:inline-block; cursor:pointer; margin:5px;
        }
        .btn-secondary:hover { background:#854f38; color:#fff; }
        .totals-container { background:#fff; border-radius:10px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,.05); }
        .total { margin:6px 0; text-align:right; }
    </style>
</x-app-layout>
