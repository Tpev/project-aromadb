<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl" style="color:#647a0b;">Livre de recettes</h2>
  </x-slot>

  @php
      $user = auth()->user();
      $canViewLivreRecettes = $user->canUseFeature('livre_recettes');

      // Determine required plan for this feature (ignoring trial)
      $plansConfig   = config('license_features.plans', []);
      $familyOrder   = ['free', 'starter', 'pro', 'premium'];
      $requiredFamily = null;

      foreach ($familyOrder as $family) {
          if (in_array('livre_recettes', $plansConfig[$family] ?? [], true)) {
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

  <div class="container-fluid mt-5 details-container mx-auto p-4">

    {{-- Title --}}
    <div class="flex-between mb-4">
      <h1 class="details-title">Livre de recettes</h1>
      <span class="chip chip-success">Mises à jour automatiques</span>
    </div>
    <p class="text-muted mb-4">
      Ce registre reprend automatiquement toutes les factures encaissées.  
      Il permet de suivre vos recettes chronologiquement et de calculer le chiffre d’affaires mensuel ou annuel.
    </p>

    {{-- Filtre --}}
    <form method="GET" class="filter-form mb-4">
      <div class="form-group">
        <label>Du</label>
        <input type="date" name="from" value="{{ request('from') }}" class="form-control">
      </div>
      <div class="form-group">
        <label>Au</label>
        <input type="date" name="to" value="{{ request('to') }}" class="form-control">
      </div>

      <button class="btn-secondary" type="submit">Filtrer</button>

      @if($canViewLivreRecettes)
        <a class="btn-primary" href="{{ route('receipts.export', request()->all()) }}">Exporter CSV</a>
        <a class="btn-secondary" href="{{ route('receipts.caMonthly') }}">Voir CA mensuel</a>
      @else
        {{-- Greyed-out actions when locked --}}
        <a class="btn-primary btn-disabled-link" href="/license-tiers/pricing">
          Exporter CSV
        </a>
        <a class="btn-secondary btn-disabled-link" href="/license-tiers/pricing">
          Voir CA mensuel
        </a>
      @endif
    </form>

    {{-- Bloc blur + overlay --}}
    <div class="blur-wrapper {{ $canViewLivreRecettes ? '' : 'is-blurred' }}">
      {{-- Tableau principal --}}
      <div class="table-responsive">
        <table class="table table-bordered table-hover am-table" id="livreRecettesTable">
          <thead>
            <tr>
              <th style="width:10%">N° d’enregistrement</th>
              <th style="width:10%">Date</th>
              <th style="width:10%">N° Facture</th>
              <th style="width:15%">Client</th>
              <th style="width:10%">Nature</th>
              <th style="width:10%" class="text-right">HT (€)</th>
              <th style="width:10%" class="text-right">TTC (€)</th>
              <th style="width:10%">Mode</th>
              <th style="width:8%">Direction</th>
              <th style="width:10%">Source</th>
              <th style="width:15%">Note</th>
            </tr>
          </thead>
          <tbody>
          @php
            $PAYMENT_METHOD_FR = [
                'transfer' => 'Virement',
                'card'     => 'Carte',
                'check'    => 'Chèque',
                'cash'     => 'Espèces',
                'other'    => 'Autre',
            ];
          @endphp
          @foreach($receipts as $r)
            <tr>
              <td>{{ $r->record_number }}</td>
              <td>{{ \Carbon\Carbon::parse($r->encaissement_date)->format('d/m/Y') }}</td>
              <td>{{ $r->invoice_number }}</td>
              <td>{{ $r->client_name }}</td>
              <td>{{ ucfirst($r->nature) }}</td>
              <td class="text-right">{{ number_format($r->amount_ht, 2, ',', ' ') }}</td>
              <td class="text-right">{{ number_format($r->amount_ttc, 2, ',', ' ') }}</td>
              <td>{{ $PAYMENT_METHOD_FR[$r->payment_method] ?? ucfirst($r->payment_method) }}</td>
              <td>{{ ucfirst($r->direction) }}</td>
              <td>{{ $r->source }}</td>
              <td>{{ $r->note }}</td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>

      {{-- Total période --}}
      <div class="totals-container mt-4">
        <p class="total">
          <strong>Total net période (TTC) :</strong>
          {{ number_format($total, 2, ',', ' ') }} €
        </p>
      </div>

      <div class="mt-3">
        {{ $receipts->withQueryString()->links() }}
      </div>

      @unless($canViewLivreRecettes)
        {{-- Overlay above blurred content --}}
        <div class="blur-overlay">
          <div class="blur-overlay-inner">
            <h3 class="blur-title">Livre de recettes bloqué</h3>
            <p class="blur-text">
              L’accès complet au livre de recettes (détail des lignes et totaux) est réservé aux formules supérieures.
            </p>
            <p class="blur-text-small">
              Disponible à partir de : <span class="blur-plan">{{ $requiredLabel }}</span>
            </p>

            <a href="/license-tiers/pricing" class="btn-primary mt-3">
              Voir les formules & débloquer le livre de recettes
            </a>
          </div>
        </div>
      @endunless
    </div>

  </div>

  <style>
    .container-fluid { max-width: 1200px; }

    .details-container {
      background:#f9f9f9;
      border-radius:10px;
      padding:30px;
      box-shadow:0 5px 15px rgba(0,0,0,0.08);
      margin:0 auto;
    }

    .flex-between {
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:16px;
    }

    .details-title {
      font-size:2rem;
      font-weight:700;
      color:#647a0b;
      margin:0;
    }

    .chip {
      padding:6px 10px;
      border-radius:999px;
      font-weight:600;
      font-size:.9rem;
      background:#fff;
      border:1px solid #e3e3e3;
      color:#555;
    }
    .chip-success {
      background:#e9f7ef;
      color:#1e7e34;
      border-color:#cfe9d8;
    }

    .text-muted {
      color:#555;
      font-size:.95rem;
    }

    /* Filtre form */
    .filter-form {
      display:flex;
      align-items:flex-end;
      flex-wrap:wrap;
      gap:12px;
      background:#fff;
      padding:16px 20px;
      border-radius:10px;
      box-shadow:0 2px 10px rgba(0,0,0,.05);
    }
    .filter-form .form-group {
      display:flex;
      flex-direction:column;
      gap:4px;
    }
    .filter-form label {
      font-weight:600;
      color:#647a0b;
      font-size:.95rem;
    }

    /* Table design (same as invoices) */
    .table-responsive {
      background:#fff;
      border-radius:8px;
      padding:20px;
      box-shadow:0 2px 10px rgba(0,0,0,.05);
      margin-top:20px;
    }
    .am-table {
      width:100%;
      border-collapse:collapse;
    }
    .am-table th,
    .am-table td {
      padding:12px 15px;
      text-align:left;
      border-bottom:1px solid #eee;
    }
    .am-table thead {
      background:#647a0b;
      color:#fff;
    }
    .am-table tbody tr:hover {
      background:#f9faf5;
    }
    .text-right { text-align:right; }

    /* Totaux */
    .totals-container {
      background:#fff;
      border-radius:8px;
      padding:20px;
      box-shadow:0 2px 10px rgba(0,0,0,.05);
    }
    .totals-container p.total {
      font-size:1.1rem;
      font-weight:700;
      color:#333;
      margin:8px 0;
    }

    /* Buttons reuse theme */
    .btn-primary, .btn-secondary {
      padding:10px 16px;
      border-radius:8px;
      text-decoration:none;
      display:inline-flex;
      align-items:center;
      gap:8px;
      cursor:pointer;
      transition:all .25s ease;
      font-weight:600;
    }
    .btn-primary {
      background:#647a0b;
      color:#fff;
      border:none;
    }
    .btn-primary:hover {
      background:#566f09;
    }
    .btn-secondary {
      background:transparent;
      color:#854f38;
      border:1px solid #854f38;
    }
    .btn-secondary:hover {
      background:#854f38;
      color:#fff;
    }

    /* Greyed link style for locked actions */
    .btn-disabled-link {
      background:#e5e7eb !important;
      color:#6b7280 !important;
      border-color:#d1d5db !important;
    }
    .btn-disabled-link:hover {
      background:#d1d5db !important;
      color:#4b5563 !important;
    }

    /* Blur wrapper + overlay */
    .blur-wrapper {
      position:relative;
      margin-top:20px;
    }
    .blur-wrapper.is-blurred > .table-responsive,
    .blur-wrapper.is-blurred > .totals-container,
    .blur-wrapper.is-blurred > .mt-3 {
      filter: blur(4px);
      pointer-events:none;
      user-select:none;
    }

    .blur-overlay {
      position:absolute;
      inset:0;
      display:flex;
      align-items:center;
      justify-content:center;
      pointer-events:none; /* only inner content clickable */
    }
    .blur-overlay-inner {
      pointer-events:auto;
      max-width:420px;
      background:rgba(255,255,255,0.96);
      border-radius:14px;
      padding:20px 24px;
      box-shadow:0 10px 30px rgba(0,0,0,0.12);
      text-align:center;
      border:1px solid #e5e7eb;
    }
    .blur-title {
      font-size:1.15rem;
      font-weight:700;
      color:#647a0b;
      margin-bottom:8px;
    }
    .blur-text {
      font-size:.95rem;
      color:#4b5563;
      margin-bottom:6px;
    }
    .blur-text-small {
      font-size:.85rem;
      color:#6b7280;
      margin-bottom:10px;
    }
    .blur-plan {
      font-weight:700;
      color:#854f38;
    }

    /* Responsive tweaks */
    @media (max-width:768px){
      .filter-form { flex-direction:column; align-items:stretch; }
      .filter-form .form-group { width:100%; }
      .btn-primary, .btn-secondary { width:100%; justify-content:center; }
      .am-table th, .am-table td { font-size:.9rem; padding:10px; }

      .blur-overlay-inner {
        margin:0 10px;
        padding:16px 18px;
      }
    }
  </style>
</x-app-layout>
