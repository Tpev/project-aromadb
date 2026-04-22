<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl" style="color:#647a0b;">CA encaissé par mois - {{ $year }}</h2>
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
  @endphp

  <div class="container-fluid mt-5 details-container mx-auto p-4">

    <div class="flex-between mb-4">
      <h1 class="details-title">Chiffre d'affaires encaissé - {{ $year }}</h1>
      <span class="chip chip-success">Statistiques annuelles</span>
    </div>
    <p class="text-muted mb-4">
      Visualisez le total des encaissements mensuels ainsi que le détail entre prestations et marchandises pour l'année sélectionnée.
      Ces données proviennent automatiquement de votre livre de recettes.
    </p>

    <form method="GET" class="filter-form mb-4">
      <div class="form-group">
        <label>Année</label>
        <input type="number" name="year" value="{{ $year }}" class="form-control" style="max-width:140px;">
      </div>
      <button class="btn-secondary" type="submit">Afficher</button>
      <a href="{{ route('receipts.index') }}" class="btn-primary">Retour au livre</a>
    </form>

    <div class="blur-wrapper {{ $canViewLivreRecettes ? '' : 'is-blurred' }}">
      <div class="table-responsive">
        <table class="table table-bordered table-hover am-table" id="caMensuelTable">
          <thead>
            <tr>
              <th style="width:34%">Mois</th>
              <th style="width:22%" class="text-right">CA total TTC (€)</th>
              <th style="width:22%" class="text-right">Prestations (€)</th>
              <th style="width:22%" class="text-right">Marchandises (€)</th>
            </tr>
          </thead>
          <tbody>
            @php
              $totalAnnuel = 0;
              $totalServices = 0;
              $totalGoods = 0;
            @endphp
            @for($m = 1; $m <= 12; $m++)
              @php
                $label = \Carbon\Carbon::create($year, $m, 1)->locale('fr')->isoFormat('MMMM');
                $monthData = $data[$m] ?? ['total' => 0, 'service' => 0, 'goods' => 0];
                $moisTotal = (float) ($monthData['total'] ?? 0);
                $moisService = (float) ($monthData['service'] ?? 0);
                $moisGoods = (float) ($monthData['goods'] ?? 0);
                $totalAnnuel += $moisTotal;
                $totalServices += $moisService;
                $totalGoods += $moisGoods;
              @endphp
              <tr>
                <td class="capitalize">{{ ucfirst($label) }}</td>
                <td class="text-right">{{ number_format($moisTotal, 2, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($moisService, 2, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($moisGoods, 2, ',', ' ') }}</td>
              </tr>
            @endfor
          </tbody>
          <tfoot>
            <tr style="background:#f9faf5;font-weight:700;">
              <td>Total annuel encaissé</td>
              <td class="text-right">{{ number_format($totalAnnuel, 2, ',', ' ') }} €</td>
              <td class="text-right">{{ number_format($totalServices, 2, ',', ' ') }} €</td>
              <td class="text-right">{{ number_format($totalGoods, 2, ',', ' ') }} €</td>
            </tr>
          </tfoot>
        </table>
      </div>

      @unless($canViewLivreRecettes)
        <div class="blur-overlay">
          <div class="blur-overlay-inner">
            <h3 class="blur-title">Statistiques de CA bloquées</h3>
            <p class="blur-text">
              Le détail du chiffre d'affaires encaissé par mois est réservé aux formules incluant le livre de recettes.
            </p>
            <p class="blur-text-small">
              Disponible à partir de : <span class="blur-plan">{{ $requiredLabel }}</span>
            </p>

            <a href="/license-tiers/pricing" class="btn-primary mt-3">
              Voir les formules & débloquer les statistiques
            </a>
          </div>
        </div>
      @endunless
    </div>

  </div>

  <style>
    .container-fluid { max-width: 980px; }

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

    .table-responsive {
      background:#fff;
      border-radius:8px;
      padding:20px;
      box-shadow:0 2px 10px rgba(0,0,0,.05);
      margin-top:20px;
      overflow-x:auto;
    }
    .am-table {
      width:100%;
      min-width:700px;
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

    .blur-wrapper {
      position:relative;
      margin-top:20px;
    }
    .blur-wrapper.is-blurred > .table-responsive {
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
      pointer-events:none;
    }
    .blur-overlay-inner {
      pointer-events:auto;
      max-width:420px;
      background:rgba(255,255,255,0.96);
      border-radius:14px;
      padding:20px 24px;
      box-shadow:0 12px 40px rgba(0,0,0,.12);
      text-align:center;
      border:1px solid rgba(100,122,11,.12);
    }
    .blur-title {
      margin:0 0 8px;
      font-size:1.2rem;
      font-weight:800;
      color:#647a0b;
    }
    .blur-text {
      color:#4b5563;
      line-height:1.55;
      margin:0;
    }
    .blur-text-small {
      color:#6b7280;
      font-size:.92rem;
      margin-top:8px;
    }
    .blur-plan {
      font-weight:700;
      color:#854f38;
    }

    @media (max-width: 640px) {
      .details-title {
        font-size:1.55rem;
      }
      .flex-between {
        flex-direction:column;
        align-items:flex-start;
      }
      .filter-form {
        flex-direction:column;
        align-items:stretch;
      }
    }
  </style>
</x-app-layout>
