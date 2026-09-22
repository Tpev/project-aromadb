<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl" style="color:#647a0b;">Livre de recettes</h2>
  </x-slot>

  @php
      $user = auth()->user();
      $canViewLivreRecettes = $user->canUseFeature('livre_recettes');

      $plansConfig    = config('license_features.plans', []);
      $familyOrder    = ['free', 'starter', 'pro', 'premium'];
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

      $PAYMENT_METHOD_FR = [
          'transfer' => 'Virement',
          'card'     => 'Carte',
          'check'    => 'Chèque',
          'cash'     => 'Espèces',
          'other'    => 'Autre',
      ];

      $NATURE_FR = [
          'service' => 'Service / Prestation',
          'goods'   => 'Vente de biens',
          'other'   => 'Autre',
      ];

      $SOURCE_FR = [
          'payment'    => 'Paiement',
          'correction' => 'Correction',
          'refund'     => 'Remboursement',
          'manual'     => 'Manuel',
      ];

      /**
       * ✅ IMPORTANT:
       * Cache les boutons "Contre-pass" sur les lignes déjà contre-passées.
       * On récupère tous les reversal_of_id présents (dans la page affichée),
       * et on cache le bouton sur la ligne originale correspondante.
       */
      $reversedOriginalIds = $receipts
          ->pluck('reversal_of_id')
          ->filter()
          ->unique()
          ->values()
          ->all();
  @endphp

  <div class="container-fluid mt-5 details-container mx-auto p-4">

    <div class="flex-between mb-3">
      <div>
        <h1 class="details-title">Livre de recettes</h1>
        <p class="text-muted mb-0">
          Ce registre reprend automatiquement toutes les factures encaissées, et permet aussi l’ajout d’écritures manuelles.
        </p>
      </div>
      <span class="chip chip-success">Mises à jour automatiques</span>
    </div>

    {{-- Flash --}}
    @if(session('success'))
      <div class="alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert-error">{{ session('error') }}</div>
    @endif
    @if($errors->any())
      <div class="alert-error">
        <strong>Erreur :</strong>
        <ul style="margin:8px 0 0 18px;">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

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
        <a class="btn-primary" href="{{ route('receipts.create') }}">+ Ajouter une écriture</a>
      @else
        <a class="btn-primary btn-disabled-link" href="/license-tiers/pricing">Exporter CSV</a>
        <a class="btn-secondary btn-disabled-link" href="/license-tiers/pricing">Voir CA mensuel</a>
        <a class="btn-primary btn-disabled-link" href="/license-tiers/pricing">+ Ajouter une écriture</a>
      @endif
    </form>

    {{-- Bloc blur + overlay --}}
    <div class="blur-wrapper {{ $canViewLivreRecettes ? '' : 'is-blurred' }}">

      <div class="table-card">
        <div class="table-card-header">
          <div class="table-card-title">Écritures</div>
          <div class="table-card-subtitle">
            {{ $receipts->total() }} ligne(s) • Page {{ $receipts->currentPage() }}/{{ $receipts->lastPage() }}
          </div>
        </div>

        <div class="table-scroll">
          <table class="am-table" id="livreRecettesTable">
            <thead>
              <tr>
                <th class="col-sm">N°</th>
                <th class="col-date">Date</th>
                <th class="col-invoice">N° Facture</th>
                <th class="col-client">Client</th>
                <th class="col-nature">Nature</th>
                <th class="col-amt text-right">HT</th>
                <th class="col-amt text-right">TTC</th>
                <th class="col-mode">Mode</th>
                <th class="col-dir">Direction</th>
                <th class="col-source">Source</th>
                <th class="col-note">Note</th>
                <th class="col-actions">Actions</th>
              </tr>
            </thead>

            <tbody>
            @forelse($receipts as $r)

              @php
                // ✅ bouton caché si:
                // - la ligne est une contre-passation
                // - la ligne est liée à une ligne originale (reversal_of_id)
                // - la ligne originale a déjà une contre-passation (son id est dans $reversedOriginalIds)
                $hideReverseButton =
                    ($r->is_reversal ?? false)
                    || !empty($r->reversal_of_id)
                    || in_array($r->id, $reversedOriginalIds, true);
              @endphp

              <tr>
                <td class="nowrap">{{ $r->record_number }}</td>
                <td class="nowrap">{{ \Carbon\Carbon::parse($r->encaissement_date)->format('d/m/Y') }}</td>

                <td class="nowrap">{{ $r->invoice_number ?: '—' }}</td>

                <td class="truncate">
                  <span title="{{ $r->client_name }}">{{ $r->client_name ?: '—' }}</span>
                </td>

                <td class="nowrap">
                  <span class="badge-soft">
                    {{ $NATURE_FR[$r->nature] ?? ucfirst((string)$r->nature) }}
                  </span>
                </td>

                <td class="text-right nowrap">{{ number_format($r->amount_ht, 2, ',', ' ') }}</td>
                <td class="text-right nowrap"><strong>{{ number_format($r->amount_ttc, 2, ',', ' ') }}</strong></td>

                <td class="nowrap">{{ $PAYMENT_METHOD_FR[$r->payment_method] ?? ucfirst((string)$r->payment_method) }}</td>

                <td class="nowrap">
                  @if($r->direction === 'credit')
                    <span class="badge-green">Crédit</span>
                  @else
                    <span class="badge-red">Débit</span>
                  @endif
                </td>

                <td class="nowrap">
                  <span class="badge-muted" title="{{ $r->source }}">
                    {{ $SOURCE_FR[$r->source] ?? (string)$r->source }}
                  </span>
                </td>

                <td class="truncate">
                  <span title="{{ $r->note }}">{{ $r->note ?: '—' }}</span>
                </td>

                <td class="nowrap">
                  @if($hideReverseButton)
                    <span class="badge-muted">CP</span>
                  @else
                    <form method="POST" action="{{ route('receipts.reverse', $r) }}"
                          class="reverse-inline-form"
                          onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerText='...';">
                      @csrf
                      <input type="date" name="encaissement_date"
                             value="{{ now()->format('Y-m-d') }}"
                             class="mini-input" required>
                      <button class="btn-mini" type="submit">Contre-pass</button>
                    </form>
                  @endif
                </td>
              </tr>

            @empty
              <tr>
                <td colspan="12" class="empty-cell">
                  Aucune écriture sur la période sélectionnée.
                </td>
              </tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>

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
      background:#f9f9f9; border-radius:10px; padding:30px;
      box-shadow:0 5px 15px rgba(0,0,0,0.08); margin:0 auto;
    }

    .flex-between { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; }
    .details-title { font-size:2rem; font-weight:700; color:#647a0b; margin:0; }
    .chip { padding:6px 10px; border-radius:999px; font-weight:600; font-size:.9rem; background:#fff; border:1px solid #e3e3e3; color:#555; white-space:nowrap; }
    .chip-success { background:#e9f7ef; color:#1e7e34; border-color:#cfe9d8; }
    .text-muted { color:#555; font-size:.95rem; }

    .alert-success, .alert-error {
      padding:12px 14px; border-radius:10px; margin:14px 0; background:#fff;
      box-shadow:0 2px 10px rgba(0,0,0,.05); border:1px solid #e5e7eb;
    }
    .alert-success { border-color:#cfe9d8; }
    .alert-error { border-color:#f3c7c7; }

    .filter-form {
      display:flex; align-items:flex-end; flex-wrap:wrap; gap:12px; background:#fff;
      padding:16px 20px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.05);
    }
    .filter-form .form-group { display:flex; flex-direction:column; gap:4px; }
    .filter-form label { font-weight:600; color:#647a0b; font-size:.95rem; }
    .form-control {
      padding:10px 12px; border-radius:10px; border:1px solid #e5e7eb; outline:none; background:#fff;
    }
    .form-control:focus { border-color:#647a0b; box-shadow:0 0 0 3px rgba(100,122,11,.12); }

    .table-card {
      background:#fff; border:1px solid #e5e7eb; border-radius:12px;
      box-shadow:0 2px 10px rgba(0,0,0,.05); overflow:hidden; margin-top:18px;
    }
    .table-card-header{
      padding:14px 16px; border-bottom:1px solid #eef2f7;
      display:flex; align-items:baseline; justify-content:space-between; gap:12px;
      background:linear-gradient(180deg, #ffffff, #fbfbfb);
    }
    .table-card-title{ font-weight:800; color:#647a0b; font-size:1.05rem; }
    .table-card-subtitle{ color:#6b7280; font-size:.9rem; white-space:nowrap; }

    .table-scroll{ width:100%; overflow-x:auto; overflow-y:hidden; -webkit-overflow-scrolling:touch; }

    .am-table { width:100%; border-collapse:separate; border-spacing:0; min-width:1150px; font-size:.92rem; }
    .am-table thead th{
      position:sticky; top:0; z-index:2;
      background:#647a0b; color:#fff; text-align:left;
      padding:12px 12px; font-weight:800; border-bottom:1px solid rgba(255,255,255,.18);
      white-space:nowrap;
    }
    .am-table tbody td{ padding:12px 12px; border-bottom:1px solid #eef2f7; vertical-align:middle; background:#fff; }
    .am-table tbody tr:hover td{ background:#f9faf5; }

    .nowrap{ white-space:nowrap; }
    .truncate{ max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .text-right{ text-align:right; }

    .col-sm{ width:70px; }
    .col-date{ width:110px; }
    .col-invoice{ width:120px; }
    .col-client{ width:190px; }
    .col-nature{ width:170px; }
    .col-amt{ width:120px; }
    .col-mode{ width:120px; }
    .col-dir{ width:110px; }
    .col-source{ width:110px; }
    .col-note{ width:240px; }
    .col-actions{ width:210px; }

    .empty-cell{ text-align:center; color:#6b7280; padding:22px 10px !important; font-weight:600; }

    .totals-container { background:#fff; border-radius:8px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,.05); }
    .totals-container p.total { font-size:1.1rem; font-weight:700; color:#333; margin:8px 0; }

    .btn-primary, .btn-secondary {
      padding:10px 16px; border-radius:8px; text-decoration:none;
      display:inline-flex; align-items:center; gap:8px;
      cursor:pointer; transition:all .25s ease; font-weight:600; border:none; white-space:nowrap;
    }
    .btn-primary { background:#647a0b; color:#fff; }
    .btn-primary:hover { background:#566f09; }
    .btn-secondary { background:transparent; color:#854f38; border:1px solid #854f38; }
    .btn-secondary:hover { background:#854f38; color:#fff; }

    .btn-disabled-link {
      background:#e5e7eb !important; color:#6b7280 !important;
      border:1px solid #d1d5db !important; pointer-events:auto;
    }

    .badge-muted {
      display:inline-block; padding:4px 8px; border-radius:999px;
      background:#f3f4f6; border:1px solid #e5e7eb; color:#6b7280;
      font-weight:800; font-size:.78rem; line-height:1;
    }
    .badge-soft {
      display:inline-block; padding:5px 10px; border-radius:999px;
      background:#f9faf5; border:1px solid #e3e8d1; color:#647a0b;
      font-weight:800; font-size:.78rem; line-height:1; white-space:nowrap;
    }
    .badge-green{
      display:inline-block; padding:5px 10px; border-radius:999px;
      background:#e9f7ef; border:1px solid #cfe9d8; color:#1e7e34;
      font-weight:900; font-size:.78rem; line-height:1; white-space:nowrap;
    }
    .badge-red{
      display:inline-block; padding:5px 10px; border-radius:999px;
      background:#fdecec; border:1px solid #f3c7c7; color:#b42318;
      font-weight:900; font-size:.78rem; line-height:1; white-space:nowrap;
    }

    .reverse-inline-form { display:flex; gap:8px; align-items:center; justify-content:flex-start; }
    .mini-input {
      padding:7px 8px; border-radius:10px; border:1px solid #e5e7eb;
      font-size:.85rem; background:#fff; min-width:135px;
    }
    .btn-mini {
      padding:8px 10px; border-radius:10px; border:1px solid #854f38;
      background:transparent; color:#854f38; font-weight:800;
      cursor:pointer; transition:all .2s ease; font-size:.85rem; white-space:nowrap;
    }
    .btn-mini:hover { background:#854f38; color:#fff; }
    .btn-mini:disabled { opacity:.6; cursor:not-allowed; }

    .blur-wrapper { position:relative; margin-top:20px; }
    .blur-wrapper.is-blurred > .table-card,
    .blur-wrapper.is-blurred > .totals-container,
    .blur-wrapper.is-blurred > .mt-3 { filter: blur(4px); pointer-events:none; user-select:none; }

    .blur-overlay { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; pointer-events:none; }
    .blur-overlay-inner {
      pointer-events:auto; max-width:420px; background:rgba(255,255,255,0.96);
      border-radius:14px; padding:20px 24px; box-shadow:0 10px 30px rgba(0,0,0,0.12);
      text-align:center; border:1px solid #e5e7eb;
    }
    .blur-title { font-size:1.15rem; font-weight:800; color:#647a0b; margin-bottom:8px; }
    .blur-text { font-size:.95rem; color:#4b5563; margin-bottom:6px; }
    .blur-text-small { font-size:.85rem; color:#6b7280; margin-bottom:10px; }
    .blur-plan { font-weight:800; color:#854f38; }

    @media (max-width:768px){
      .filter-form { flex-direction:column; align-items:stretch; }
      .filter-form .form-group { width:100%; }
      .btn-primary, .btn-secondary { width:100%; justify-content:center; }
      .flex-between{ flex-direction:column; align-items:flex-start; }
      .chip{ align-self:flex-start; }
      .reverse-inline-form { flex-direction:column; align-items:stretch; }
      .mini-input { min-width:100%; }
      .btn-mini { width:100%; justify-content:center; }
      .table-card-subtitle{ white-space:normal; }
    }
  </style>
</x-app-layout>
