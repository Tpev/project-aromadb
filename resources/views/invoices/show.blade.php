<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#647a0b;">
            {{ __('Détails de la facture') }} – #{{ $invoice->invoice_number }}
        </h2>
    </x-slot>

    {{-- Alpine (Jetstream already includes it). If not, uncomment: --}}
    {{-- <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}

    <div class="container-fluid mt-5" x-data="{ showPayModal:false, showReceipts:false }">
        <div class="details-container mx-auto p-4">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="flex-between">
                <h1 class="details-title">{{ __('Facture n°') }} {{ $invoice->invoice_number }}</h1>

                {{-- Actions à droite --}}
                <div class="actions-bar">
                    @if($invoice->status !== 'Payée' || $invoice->solde_restant > 0)
                        <button type="button" class="btn-primary" @click="showPayModal=true">
                            <i class="fas fa-check"></i> {{ __('Enregistrer un paiement') }}
                        </button>
                    @endif

                    @if($invoice->status !== 'Payée')
                        @if($invoice->payment_link)
                            <a href="{{ $invoice->payment_link }}" target="_blank" class="btn-secondary">
                                <i class="fas fa-link mr-2"></i> {{ __('Voir le Lien de Paiement') }}
                            </a>
                        @else
                            <form action="{{ route('invoices.createPaymentLink', $invoice->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                <button type="submit" class="btn-secondary">
                                    <i class="fas fa-link mr-2"></i> {{ __('Créer un Lien de Paiement') }}
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Chips de statut + KPI paiements --}}
            <div class="kpi-row">
                <span class="chip {{ $invoice->status === 'Payée' ? 'chip-success' : 'chip-warning' }}">
                    {{ ucfirst($invoice->status) }}
                </span>

                <div class="kpis">
                    <div class="kpi">
                        <p class="kpi-label">Total TTC</p>
                        <p class="kpi-value">{{ number_format($invoice->total_amount_with_tax, 2, ',', ' ') }} €</p>
                    </div>
                    <div class="kpi">
                        <p class="kpi-label">Encaissé TTC</p>
                        <p class="kpi-value">{{ number_format($invoice->total_encaisse, 2, ',', ' ') }} €</p>
                    </div>
                    <div class="kpi">
                        <p class="kpi-label">Solde restant</p>
                        <p class="kpi-value {{ $invoice->solde_restant > 0 ? 'text-danger' : 'text-ok' }}">
                            {{ number_format($invoice->solde_restant, 2, ',', ' ') }} €
                        </p>
                    </div>
                </div>
            </div>

            @php
                $cp      = $invoice->clientProfile;
                $company = $cp->company ?? null;
                $billingFirst = $cp->first_name_billing ?: $cp->first_name;
                $billingLast  = $cp->last_name_billing  ?: $cp->last_name;
            @endphp

            {{-- Infos facture --}}
            <div class="invoice-info-boxes row mt-4">
                {{-- Client / Bénéficiaire --}}
                <div class="col-md-4">
                    <div class="invoice-box d-flex align-items-center">
                        <i class="fas fa-user icon"></i>
                        <div class="invoice-details">
                            <p class="invoice-label">{{ __('Client / Bénéficiaire') }}</p>
                            <p class="invoice-value">
                                @if($company)
                                    <span class="font-semibold block">{{ $company->name }}</span>
                                    <span class="text-sm text-gray-600 block">
                                        Bénéficiaire (profil client) :
                                        {{ $cp->first_name }} {{ $cp->last_name }}
                                    </span>
                                @else
                                    <span class="block">
                                        Bénéficiaire (profil client) :
                                        {{ $cp->first_name }} {{ $cp->last_name }}
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Facturé à (entreprise ou normal) --}}
                <div class="col-md-4">
                    <div class="invoice-box d-flex align-items-center">
                        <i class="fas fa-file-invoice icon"></i>
                        <div class="invoice-details">
                            <p class="invoice-label">{{ __('Facturé à') }}</p>
                            <p class="invoice-value">
                                @if($company)
                                    {{ $company->name }} – À l’attention de {{ $billingFirst }} {{ $billingLast }}
                                @else
                                    {{ $billingFirst }} {{ $billingLast }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Date de facture --}}
                <div class="col-md-4">
                    <div class="invoice-box d-flex align-items-center">
                        <i class="fas fa-calendar-alt icon"></i>
                        <div class="invoice-details">
                            <p class="invoice-label">{{ __('Date de Facture') }}</p>
                            <p class="invoice-value">{{ $invoice->invoice_date->translatedFormat('d F Y') }}</p>
                        </div>
                    </div>
                </div>

                @if($invoice->due_date)
                    <div class="col-md-4">
                        <div class="invoice-box d-flex align-items-center">
                            <i class="fas fa-calendar-check icon"></i>
                            <div class="invoice-details">
                                <p class="invoice-label">{{ __('Date d\'échéance') }}</p>
                                <p class="invoice-value">{{ $invoice->due_date->translatedFormat('d F Y') }}</p>
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

                @if($invoice->sent_at)
                    <div class="col-md-4">
                        <div class="invoice-box d-flex align-items-center">
                            <i class="fas fa-envelope icon"></i>
                            <div class="invoice-details">
                                <p class="invoice-label">{{ __('Facture envoyée le') }}</p>
                                <p class="invoice-value">{{ $invoice->sent_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Articles --}}
            <div class="row mt-4">
                <div class="col-md-12">
                    <h2 class="details-subtitle">{{ __('Articles de la facture') }}</h2>

                    @if($invoice->items->isEmpty())
                        <p class="text-muted">Aucun article dans cette facture.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover am-table" id="invoiceItemsTable">
                                <thead>
                                    <tr>
                                        <th style="width:20%">{{ __('Produit') }}</th>
                                        <th style="width:30%">{{ __('Description') }}</th>
                                        <th style="width:10%">{{ __('Quantité') }}</th>
                                        <th style="width:10%">{{ __('P.U. (€)') }}</th>
                                        <th style="width:10%">{{ __('Taxe (%)') }}</th>
                                        <th style="width:10%">{{ __('Total HT (€)') }}</th>
                                        <th style="width:10%">{{ __('Montant Taxe (€)') }}</th>
                                        <th style="width:10%">{{ __('Total TTC (€)') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($invoice->items as $item)
                                    <tr>
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
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ number_format($item->unit_price * (1 + $item->tax_rate / 100), 2, ',', ' ') }} €</td>
                                        <td>{{ number_format($item->tax_rate, 2, ',', ' ') }}%</td>
                                        <td>{{ number_format($item->total_price, 2, ',', ' ') }} €</td>
                                        <td>{{ number_format($item->tax_amount, 2, ',', ' ') }} €</td>
                                        <td>{{ number_format($item->total_price_with_tax, 2, ',', ' ') }} €</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Totaux --}}
                        <div class="row mt-4">
                            <div class="col-md-6"></div>
                            <div class="col-md-6">
                                <div class="totals-container">
                                    <p class="total"><strong>{{ __('Total HT') }} :</strong>
                                        {{ number_format($invoice->total_amount, 2, ',', ' ') }} €</p>
                                    <p class="total"><strong>{{ __('Total Taxe') }} :</strong>
                                        {{ number_format($invoice->total_tax_amount, 2, ',', ' ') }} €</p>
                                    <p class="total"><strong>{{ __('Total TTC') }} :</strong>
                                        {{ number_format($invoice->total_amount_with_tax, 2, ',', ' ') }} €</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Paiements — avec contrôle strict de l’éligibilité à la contre-passation --}}
            @if($invoice->receipts->count() > 0)
            <div class="row mt-4">
              <div class="col-md-12">
                <div class="d-flex align-items-center justify-content-between">
                  <h2 class="details-subtitle m-0">{{ __('Historique des paiements') }}</h2>
                  <button type="button" class="btn-secondary" @click="showReceipts = !showReceipts">
                    <i class="fas" :class="showReceipts ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    <span x-text="showReceipts ? 'Masquer' : 'Afficher'"></span>
                  </button>
                </div>

<div class="table-responsive mt-3" x-show="showReceipts" x-transition>
  <table class="table table-bordered table-hover am-table" id="receiptsTable">
    <thead>
      <tr>
        <th style="width: 12%;">{{ __('Date') }}</th>
        <th style="width: 14%;">{{ __('N° Facture') }}</th>
        <th>{{ __('Client') }}</th>
        <th style="width: 14%;">{{ __('Mode') }}</th>
        <th style="width: 10%;">{{ __('Direction') }}</th>
        <th class="text-right" style="width: 16%;">{{ __('Montant TTC (€)') }}</th>
        <th style="width: 20%;">{{ __('Note') }}</th>
        <th style="width: 12%;">{{ __('Actions') }}</th>
      </tr>
    </thead>

    @php
        // 0) Encaissé TTC global (si 0 => aucune CP possible)
        $encaisseTotal = (float) ($invoice->total_encaisse ?? 0);

        // 1) Set des lignes déjà contre-passées (celles qui ont un enfant reversal pointant vers elles)
        $alreadyReversedIds = $invoice->receipts
            ->filter(fn($x) => (int)$x->is_reversal === 1 && !is_null($x->reversal_of_id))
            ->pluck('reversal_of_id')
            ->map(fn($v) => (int)$v)
            ->all();

        // 2) Dernière ligne éligible (LIFO) : crédit, payment, >0, non reversal, sans reversal existant
        $lastReversible = $invoice->receipts
            ->filter(function ($x) use ($alreadyReversedIds) {
                return (int)$x->is_reversal !== 1
                    && !in_array((int)$x->id, $alreadyReversedIds, true)
                    && is_null($x->reversal_of_id)
                    && $x->direction === 'credit'
                    && $x->source === 'payment'
                    && (float)$x->amount_ttc > 0;
            })
            ->sortByDesc('id')
            ->first();

        $lastReversibleId = $lastReversible->id ?? null;
    @endphp

    <tbody>
    @foreach($invoice->receipts as $r)
      @php
          $isReversal       = (int)$r->is_reversal === 1;
          $hasBeenReversed  = in_array((int)$r->id, $alreadyReversedIds, true);

          $canReverse = $encaisseTotal > 0
                        && !$isReversal
                        && !$hasBeenReversed
                        && is_null($r->reversal_of_id)
                        && $r->direction === 'credit'
                        && $r->source === 'payment'
                        && (float)$r->amount_ttc > 0
                        && ((int)$r->id === (int)$lastReversibleId);
      @endphp

      <tr x-data="{ openReverse:false }">
        <td>{{ \Illuminate\Support\Carbon::parse($r->encaissement_date)->format('d/m/Y') }}</td>
        <td>{{ $r->invoice_number }}</td>
        <td>{{ $r->client_name }}</td>
        <td>{{ $r->payment_method_label }}</td>
        <td>
          {{ $r->direction === 'debit' ? 'Sortie' : 'Entrée' }}
          @if($isReversal)
            <span class="badge-reversal" title="Contre-passation">CP</span>
          @endif
        </td>
        <td class="text-right">{{ number_format($r->amount_ttc, 2, ',', ' ') }}</td>
        <td>{{ $r->note }}</td>
        <td>
          @if($canReverse)
            <button type="button" class="btn-secondary btn-xs" @click="openReverse=true">
              Contre-passer
            </button>
          @endif
        </td>

        {{-- Modal Contre-passation (par ligne) --}}
        <template x-teleport="body">
          <div class="modal" x-show="openReverse" x-transition>
            <div class="modal-overlay" @click="openReverse=false"></div>
            <div class="modal-card" @click.outside="openReverse=false">
              <div class="modal-header">
                <h3>Contre-passation de la ligne #{{ $r->id }}</h3>
                <button class="modal-close" @click="openReverse=false">&times;</button>
              </div>
              <form action="{{ route('receipts.reverse', $r->id) }}" method="POST" class="modal-body">
                @csrf
                <div class="form-grid">
                  <div class="form-field">
                    <label class="invoice-label">Date</label>
                    <input type="date" name="encaissement_date" class="form-control" value="{{ now()->toDateString() }}">
                  </div>
                  <div class="form-field">
                    <label class="invoice-label">Montant TTC (optionnel)</label>
                    <input type="number" step="0.01" min="0.01" name="amount_ttc" class="form-control"
                           placeholder="{{ number_format($r->amount_ttc, 2, ',', ' ') }}">
                    <small class="text-muted">Laisser vide = contre-passer la totalité.</small>
                  </div>
                  <div class="form-field col-span-2">
                    <label class="invoice-label">Note</label>
                    <input type="text" name="note" class="form-control" placeholder="Raison de la contre-passation…">
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn-secondary" @click="openReverse=false">Annuler</button>
                  <button type="submit" class="btn-primary">Valider</button>
                </div>
              </form>
            </div>
          </div>
        </template>
      </tr>
    @endforeach
    </tbody>
  </table>

  @if($encaisseTotal <= 0)
    <p class="text-muted mt-2" style="font-style:italic;">
      Aucun paiement réversible : encaissement total à 0.
    </p>
  @endif
</div>


                <div class="totals-container mt-3" x-show="showReceipts" x-transition>
                  <p class="total">
                    <strong>{{ __('Total encaissé TTC') }} :</strong>
                    {{ number_format($invoice->total_encaisse, 2, ',', ' ') }} €
                  </p>
                  <p class="total">
                    <strong>{{ __('Solde restant TTC') }} :</strong>
                    {{ number_format($invoice->solde_restant, 2, ',', ' ') }} €
                  </p>
                </div>
              </div>
            </div>
            @endif

            {{-- Notes --}}
            @if($invoice->notes)
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h2 class="details-subtitle">{{ __('Notes') }}</h2>
                        <p>{{ $invoice->notes }}</p>
                    </div>
                </div>
            @endif

            {{-- Boutons bas de page --}}
            <div class="row mt-4">
                <div class="col-md-12 text-center">
                    <a href="{{ route('invoices.index') }}" class="btn-primary">{{ __('Retour à la liste') }}</a>
                    <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn-secondary">{{ __('Modifier la facture') }}</a>
                    <a href="{{ route('invoices.pdf', $invoice->id) }}" class="btn-primary">{{ __('Télécharger le PDF') }}</a>

                    @if(is_null($invoice->sent_at))
                        <form action="{{ route('invoices.sendEmail', $invoice->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            <button type="submit" class="btn-secondary" onclick="return confirm('Voulez-vous vraiment envoyer cette facture par email ?')">
                                <i class="fas fa-envelope"></i> {{ __('Envoyer par Email') }}
                            </button>
                        </form>
                    @else
                        <div class="email-sent-indicator" style="display:inline-block;margin-left:10px;">
                            <i class="fas fa-check-circle"></i>
                            {{ __('Facture envoyée') }} le {{ $invoice->sent_at->format('d/m/Y à H:i') }}
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- MODAL Enregistrer un paiement (caché par défaut) --}}
        <div class="modal" x-show="showPayModal" x-transition>
            <div class="modal-overlay" @click="showPayModal=false"></div>
            <div class="modal-card" @click.outside="showPayModal=false">
                <div class="modal-header">
                    <h3>{{ __('Enregistrer un paiement') }}</h3>
                    <button class="modal-close" @click="showPayModal=false">&times;</button>
                </div>

                <form action="{{ route('invoices.markAsPaid', $invoice->id) }}" method="POST" class="modal-body">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <div class="form-field">
                            <label class="invoice-label">Date d’encaissement</label>
                            <input type="date" name="encaissement_date" class="form-control" value="{{ now()->toDateString() }}">
                        </div>

                        <div class="form-field">
                            <label class="invoice-label">Mode de règlement</label>
                            <select name="payment_method" class="form-control">
                                <option value="transfer">Virement</option>
                                <option value="card">Carte</option>
                                <option value="check">Chèque</option>
                                <option value="cash">Espèces</option>
                                <option value="other">Autre</option>
                            </select>
                        </div>

                        <div class="form-field">
                            <label class="invoice-label">Montant TTC (optionnel)</label>
                            <input type="number" step="0.01" min="0" name="amount_ttc" class="form-control"
                                   placeholder="{{ number_format($invoice->solde_restant, 2, ',', ' ') }}">
                            <small class="text-muted">Laisser vide = encaissement du solde restant.</small>
                        </div>

                        <div class="form-field">
                            <label class="invoice-label">Nature</label>
                            <select name="nature" class="form-control">
                                <option value="service">Prestation</option>
                                <option value="goods">Vente</option>
                            </select>
                        </div>

                        <div class="form-field col-span-2">
                            <label class="invoice-label">Note (optionnel)</label>
                            <input type="text" name="note" class="form-control" placeholder="Ex. Règlement acompte, virement…">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" @click="showPayModal=false">Annuler</button>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-check"></i> Enregistrer
                        </button>
                    </div>

                    @if($invoice->receipts->count() > 0)
                        <div class="mt-3">
                            <button type="button" class="link-toggle" @click="showReceipts = !showReceipts">
                                <i class="fas" :class="showReceipts ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                <span x-text="showReceipts ? 'Masquer l’historique des paiements' : 'Afficher l’historique des paiements'"></span>
                            </button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <style>
        .container-fluid { max-width: 1200px; }
        .details-container {
            background:#f9f9f9; border-radius:10px; padding:30px;
            box-shadow:0 5px 15px rgba(0,0,0,0.08); margin:0 auto;
        }

        .flex-between{ display:flex; align-items:center; justify-content:space-between; gap:16px; }
        .actions-bar { display:flex; gap:8px; flex-wrap:wrap; }

        .details-title, .details-subtitle {
            font-size:2rem; font-weight:700; color:#647a0b; margin:0;
        }
        .details-subtitle { margin: 20px 0; text-align:left; }

        .kpi-row { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-top:16px; flex-wrap:wrap; }
        .chip{
            padding:6px 10px; border-radius:999px; font-weight:600; font-size:.9rem; background:#fff;
            border:1px solid #e3e3e3; color:#555;
        }
        .chip-success{ background:#e9f7ef; color:#1e7e34; border-color:#cfe9d8; }
        .chip-warning{ background:#fff7e6; color:#a15c00; border-color:#ffe3b3; }

        .kpis{ display:flex; gap:12px; flex-wrap:wrap; }
        .kpi{
            background:#fff; border-radius:10px; padding:12px 16px; box-shadow:0 2px 8px rgba(0,0,0,.05);
            min-width: 200px;
        }
        .kpi-label{ margin:0; color:#647a0b; font-weight:600; }
        .kpi-value{ margin:4px 0 0; font-weight:700; font-size:1.1rem; color:#333; }
        .text-danger{ color:#c0392b !important; }
        .text-ok{ color:#1e7e34 !important; }

        .invoice-info-boxes{ display:flex; flex-wrap:wrap; gap:20px; }
        .invoice-box{
            display:flex; align-items:center; background:#fff; border-radius:10px; box-shadow:0 3px 10px rgba(0,0,0,.05);
            padding:20px; transition:.3s; width:100%;
        }
        .invoice-box:hover{ transform: translateY(-2px); }
        .icon{ font-size:2rem; color:#854f38; margin-right:15px; min-width:40px; text-align:center; }
        .invoice-details{ text-align:left; flex:1; }
        .invoice-label{ font-weight:700; color:#647a0b; margin:0; }
        .invoice-value{ color:#333; font-size:1rem; margin:5px 0 0 0; }

        .table-responsive{ background:#fff; border-radius:8px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,.05); margin-top:20px; }

        .am-table{ width:100%; border-collapse:collapse; }
        .am-table th, .am-table td{ padding:12px 15px; text-align:left; border-bottom:1px solid #eee; }
        .am-table thead{ background:#647a0b; color:#fff; }
        .am-table tbody tr:hover{ background:#f9faf5; }
        .text-right{ text-align:right; }

        .badge-reversal{
            display:inline-block; font-size:.72rem; padding:2px 6px; border-radius:6px;
            background:#fff7e6; color:#a15c00; border:1px solid #ffe3b3; margin-left:6px;
        }

        .totals-container{ background:#fff; border-radius:8px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,.05); }
        .totals-container p.total{ font-size:1.1rem; font-weight:700; color:#333; margin:8px 0; }

        .btn-primary, .btn-secondary {
            padding:10px 16px; border-radius:8px; text-decoration:none; display:inline-flex; gap:8px; align-items:center;
            cursor:pointer; margin:5px; transition: all .25s ease; font-weight:600;
        }
        .btn-primary{ background:#647a0b; color:#fff; border:none; }
        .btn-primary:hover{ background:#566f09; }
        .btn-secondary{ background:transparent; color:#854f38; border:1px solid #854f38; }
        .btn-secondary:hover{ background:#854f38; color:#fff; }
        .btn-xs{ padding:6px 10px; font-size:.9rem; border-radius:6px; }

        .email-sent-indicator{ font-size:1rem; color:#28a745; display:inline-flex; align-items:center; gap:6px; }

        .link-toggle{
            background:transparent; border:none; color:#647a0b; font-weight:600; cursor:pointer; display:inline-flex; gap:8px; align-items:center;
            padding:6px 0;
        }

        /* Modal */
        .modal{ position:fixed; inset:0; display:flex; align-items:center; justify-content:center; z-index:50; }
        [x-cloak], .modal[style*="display: none"] { display:none !important; }
        .modal-overlay{ position:absolute; inset:0; background:rgba(0,0,0,.4); }
        .modal-card{
            position:relative; background:#fff; width:100%; max-width:720px; border-radius:12px; box-shadow:0 15px 40px rgba(0,0,0,.2);
            display:flex; flex-direction:column; max-height:85vh;
        }
        .modal-header{
            padding:16px 20px; border-bottom:1px solid #eee; display:flex; align-items:center; justify-content:space-between;
        }
        .modal-header h3{ margin:0; font-weight:800; color:#333; }
        .modal-close{
            background:transparent; border:none; font-size:1.8rem; line-height:1; cursor:pointer; color:#777;
        }
        .modal-body{ padding:16px 20px; overflow:auto; }
        .modal-footer{ padding:12px 20px; border-top:1px solid #eee; display:flex; justify-content:flex-end; gap:8px; }

        .form-grid{ display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        .form-field{ display:flex; flex-direction:column; gap:6px; }
        .form-field.col-span-2{ grid-column: span 2; }
        .form-control{
            border:1px solid #ddd; border-radius:8px; padding:10px 12px; background:#fff; outline:none;
        }
        .form-control:focus{ border-color:#647a0b; box-shadow:0 0 0 3px rgba(100,122,11,.15); }

        @media (max-width: 992px){
            .invoice-info-boxes .col-md-4{ flex:0 0 100%; max-width:100%; }
            .invoice-box{ flex-direction:column; align-items:flex-start; }
            .icon{ margin-bottom:10px; }
            .kpis{ width:100%; }
            .kpi{ flex:1 1 100%; }
            .form-grid{ grid-template-columns:1fr; }
        }
        @media (max-width: 576px){
            .details-title{ font-size:1.6rem; }
            .btn-primary, .btn-secondary{ width:100%; text-align:center; margin-bottom:10px; }
            .am-table th, .am-table td{ padding:10px; font-size:.92rem; }
        }
    </style>
</x-app-layout>
