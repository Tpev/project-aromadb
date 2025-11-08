<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl" style="color:#647a0b;">CA encaissé par mois – {{ $year }}</h2>
  </x-slot>

  <div class="container-fluid mt-5 details-container mx-auto p-4">

    {{-- Title --}}
    <div class="flex-between mb-4">
      <h1 class="details-title">Chiffre d’affaires encaissé – {{ $year }}</h1>
      <span class="chip chip-success">Statistiques annuelles</span>
    </div>
    <p class="text-muted mb-4">
      Visualisez le total des encaissements mensuels pour l’année sélectionnée.  
      Ces données proviennent automatiquement de votre livre de recettes.
    </p>

    {{-- Filtre Année --}}
    <form method="GET" class="filter-form mb-4">
      <div class="form-group">
        <label>Année</label>
        <input type="number" name="year" value="{{ $year }}" class="form-control" style="max-width:140px;">
      </div>
      <button class="btn-secondary" type="submit">Afficher</button>
      <a href="{{ route('receipts.index') }}" class="btn-primary">Retour au livre</a>
    </form>

    {{-- Tableau principal --}}
    <div class="table-responsive">
      <table class="table table-bordered table-hover am-table" id="caMensuelTable">
        <thead>
          <tr>
            <th style="width:70%">Mois</th>
            <th style="width:30%" class="text-right">CA encaissé TTC (€)</th>
          </tr>
        </thead>
        <tbody>
          @php $totalAnnuel = 0; @endphp
          @for($m=1; $m<=12; $m++)
            @php
              $label = \Carbon\Carbon::create($year, $m, 1)->locale('fr')->isoFormat('MMMM');
              $moisTotal = $data[$m] ?? 0;
              $totalAnnuel += $moisTotal;
            @endphp
            <tr>
              <td class="capitalize">{{ ucfirst($label) }}</td>
              <td class="text-right">{{ number_format($moisTotal, 2, ',', ' ') }}</td>
            </tr>
          @endfor
        </tbody>
        <tfoot>
          <tr style="background:#f9faf5;font-weight:700;">
            <td>Total annuel encaissé</td>
            <td class="text-right">{{ number_format($totalAnnuel, 2, ',', ' ') }} €</td>
          </tr>
        </tfoot>
      </table>
    </div>

  </div>

  <style>
    .container-fluid { max-width: 800px; }

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

    /* Table design (same as invoices & livre) */
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

    /* Responsive tweaks */
    @media (max-width:768px){
      .filter-form { flex-direction:column; align-items:stretch; }
      .btn-primary, .btn-secondary { width:100%; justify-content:center; }
      .am-table th, .am-table td { font-size:.9rem; padding:10px; }
    }
  </style>
</x-app-layout>
