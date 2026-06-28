<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Finance Stripe') - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        :root {
            --bg: #f6f7f9;
            --surface: #ffffff;
            --surface-soft: #f0f4f7;
            --ink: #17202a;
            --muted: #607080;
            --line: #d9e0e7;
            --blue: #2563eb;
            --green: #047857;
            --amber: #b45309;
            --red: #dc2626;
            --violet: #7c3aed;
            --radius: 8px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            letter-spacing: 0;
        }

        a { color: inherit; text-decoration: none; }

        .finance-shell {
            width: min(1540px, calc(100% - 32px));
            margin: 0 auto;
            padding: 24px 0 48px;
        }

        .finance-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .breadcrumb {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            color: var(--muted);
            font-size: 13px;
            margin-bottom: 8px;
        }

        .breadcrumb a { color: #34495e; font-weight: 700; }

        h1 {
            margin: 0;
            font-size: 30px;
            line-height: 1.15;
            letter-spacing: 0;
        }

        .page-subtitle {
            margin: 8px 0 0;
            max-width: 780px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.5;
        }

        .header-actions,
        .nav-tabs,
        .button-row,
        .inline-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .nav-tabs {
            position: sticky;
            top: 0;
            z-index: 20;
            padding: 10px 0 16px;
            margin-bottom: 12px;
            background: var(--bg);
        }

        .nav-tab,
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 38px;
            padding: 9px 12px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: var(--surface);
            color: #263442;
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
            cursor: pointer;
        }

        .nav-tab.active,
        .btn-primary {
            border-color: #1d4ed8;
            background: #1d4ed8;
            color: #ffffff;
        }

        .btn-danger {
            border-color: #fecaca;
            color: #b91c1c;
            background: #fff7f7;
        }

        .btn-small {
            min-height: 32px;
            padding: 7px 10px;
            font-size: 12px;
        }

        .flash,
        .error-box {
            border-radius: var(--radius);
            padding: 12px 14px;
            margin: 12px 0;
            font-size: 14px;
            line-height: 1.45;
        }

        .flash { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .error-box { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        .forecast-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin: 16px 0;
            padding: 14px;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
        }

        .toolbar-summary {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            color: var(--muted);
            font-size: 13px;
        }

        .toolbar-summary strong { color: var(--ink); }

        .metric-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(185px, 1fr));
            gap: 12px;
            margin: 16px 0;
        }

        .metric-card,
        .panel,
        .table-panel,
        .board-column,
        .sub-card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
        }

        .metric-card {
            padding: 14px;
            min-height: 104px;
        }

        .metric-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .metric-value {
            margin-top: 10px;
            font-size: 24px;
            font-weight: 900;
            line-height: 1.15;
            overflow-wrap: anywhere;
        }

        .metric-note {
            margin-top: 6px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.35;
        }

        .content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) minmax(300px, 0.75fr);
            gap: 14px;
            align-items: start;
        }

        .panel,
        .table-panel {
            padding: 16px;
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .panel-title {
            margin: 0;
            font-size: 17px;
            line-height: 1.25;
        }

        .panel-subtitle {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        .filters {
            display: grid;
            grid-template-columns: minmax(220px, 1fr) minmax(150px, 220px) auto;
            gap: 10px;
            align-items: end;
            margin: 12px 0 16px;
        }

        label {
            display: grid;
            gap: 6px;
            color: #334155;
            font-size: 12px;
            font-weight: 800;
        }

        input,
        select,
        textarea {
            width: 100%;
            min-height: 38px;
            border: 1px solid #cbd5e1;
            border-radius: var(--radius);
            padding: 9px 10px;
            color: var(--ink);
            background: #ffffff;
            font: inherit;
            font-size: 14px;
        }

        textarea {
            min-height: 110px;
            resize: vertical;
            line-height: 1.45;
        }

        .board-scroll {
            overflow-x: auto;
            padding-bottom: 8px;
        }

        .board {
            display: grid;
            grid-template-columns: repeat(8, minmax(275px, 1fr));
            gap: 12px;
            min-width: 2280px;
        }

        .board-column {
            min-height: 320px;
            padding: 12px;
            border-top: 4px solid var(--accent, var(--blue));
        }

        .column-title {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 10px;
        }

        .column-title h2 {
            margin: 0;
            font-size: 15px;
            line-height: 1.2;
        }

        .count-pill,
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border-radius: 999px;
            padding: 5px 8px;
            font-size: 12px;
            font-weight: 900;
            background: #eef2f7;
            color: #334155;
            white-space: nowrap;
        }

        .status-pill.green { background: #dcfce7; color: #166534; }
        .status-pill.blue { background: #dbeafe; color: #1d4ed8; }
        .status-pill.amber { background: #fef3c7; color: #92400e; }
        .status-pill.red { background: #fee2e2; color: #991b1b; }
        .status-pill.violet { background: #ede9fe; color: #6d28d9; }

        .sub-card {
            padding: 12px;
            margin-top: 10px;
        }

        .sub-title {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }

        .sub-title strong {
            display: block;
            min-width: 0;
            font-size: 14px;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }

        .sub-meta {
            margin-top: 8px;
            display: grid;
            gap: 6px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.35;
        }

        .sub-meta span,
        .mini-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .sub-meta b,
        .mini-line b {
            color: #273444;
            font-weight: 900;
            text-align: right;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 760px;
            border-collapse: collapse;
        }

        table.assumption-table { min-width: 520px; }
        table.forecast-table { min-width: 980px; }

        .scenario-line {
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr);
            align-items: center;
            gap: 8px;
            min-height: 22px;
        }

        .scenario-line b {
            color: #273444;
            font-weight: 900;
            text-align: right;
        }

        .scenario-tag {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            min-height: 20px;
            padding: 2px 6px;
            border-radius: 999px;
            background: #eef2f7;
            color: #334155;
            font-size: 11px;
            font-weight: 900;
        }

        .finance-modal {
            width: min(920px, calc(100% - 28px));
            max-height: min(82vh, 900px);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 0;
            color: var(--ink);
            background: var(--surface);
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.22);
        }

        .finance-modal::backdrop {
            background: rgba(15, 23, 42, 0.42);
        }

        .modal-shell {
            display: grid;
            max-height: min(82vh, 900px);
            grid-template-rows: auto minmax(0, 1fr) auto;
        }

        .modal-header,
        .modal-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
        }

        .modal-footer {
            border-top: 1px solid var(--line);
            border-bottom: 0;
        }

        .modal-body {
            padding: 16px;
            overflow: auto;
        }

        .icon-btn {
            width: 36px;
            min-width: 36px;
            min-height: 36px;
            padding: 0;
        }

        th,
        td {
            padding: 11px 10px;
            border-bottom: 1px solid #e5e9ef;
            text-align: left;
            vertical-align: top;
            font-size: 13px;
            line-height: 1.35;
        }

        th {
            color: #475569;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0;
            background: #f8fafc;
        }

        td strong { overflow-wrap: anywhere; }

        .chart-list {
            display: grid;
            gap: 10px;
        }

        .chart-row {
            display: grid;
            grid-template-columns: 92px minmax(120px, 1fr) minmax(220px, 0.7fr);
            align-items: center;
            gap: 12px;
            min-height: 42px;
        }

        .bar-track {
            height: 12px;
            border-radius: 999px;
            background: #edf2f7;
            overflow: hidden;
        }

        .bar-fill {
            display: block;
            height: 100%;
            width: var(--bar, 0%);
            background: #2563eb;
            border-radius: inherit;
        }

        .money-list {
            display: grid;
            gap: 4px;
            color: var(--muted);
            font-size: 12px;
        }

        .money-list b { color: #1f2937; }

        .detail-header {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            align-items: start;
        }

        .identity {
            display: grid;
            gap: 5px;
        }

        .identity h2 {
            margin: 0;
            font-size: 24px;
            line-height: 1.15;
            overflow-wrap: anywhere;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .empty-state {
            padding: 18px;
            color: var(--muted);
            background: var(--surface-soft);
            border-radius: var(--radius);
            font-size: 14px;
        }

        @media (max-width: 980px) {
            .finance-shell { width: min(100% - 20px, 1540px); padding-top: 16px; }
            .finance-header,
            .forecast-toolbar,
            .content-grid,
            .detail-header {
                grid-template-columns: 1fr;
                display: grid;
            }

            .header-actions,
            .button-row {
                justify-content: flex-start;
            }

            .filters,
            .form-grid {
                grid-template-columns: 1fr;
            }

            .board {
                min-width: 0;
                grid-template-columns: 1fr;
            }

            .chart-row {
                grid-template-columns: 1fr;
                align-items: stretch;
            }

            .nav-tabs {
                position: static;
                overflow-x: auto;
                flex-wrap: nowrap;
                padding-bottom: 10px;
            }

            .nav-tab { flex: 0 0 auto; }
        }
    </style>
</head>
<body>
    @php
        $navItems = [
            ['route' => 'admin.finance.overview', 'icon' => 'tachometer-alt', 'label' => 'Vue finance'],
            ['route' => 'admin.finance.customers', 'icon' => 'columns', 'label' => 'Clients & licences'],
            ['route' => 'admin.finance.failures', 'icon' => 'exclamation-circle', 'label' => 'Paiements échoués'],
            ['route' => 'admin.finance.payouts', 'icon' => 'university', 'label' => 'Payouts & frais'],
            ['route' => 'admin.finance.forecast', 'icon' => 'chart-line', 'label' => 'Prévisions'],
        ];
    @endphp

    <main class="finance-shell">
        <header class="finance-header">
            <div>
                <div class="breadcrumb">
                    <a href="{{ route('admin.welcome') }}">Admin</a>
                    <span>/</span>
                    <span>Finance Stripe</span>
                </div>
                <h1>@yield('page-title', 'Finance Stripe')</h1>
                @hasSection('page-subtitle')
                    <p class="page-subtitle">@yield('page-subtitle')</p>
                @endif
            </div>
            <div class="header-actions">
                <form method="POST" action="{{ route('admin.finance.sync') }}" class="inline-actions">
                    @csrf
                    <input type="hidden" name="days" value="365">
                    <input type="hidden" name="max" value="1500">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-sync-alt"></i>Synchroniser</button>
                </form>
                <a href="{{ route('admin.crm.index') }}" class="btn"><i class="fas fa-handshake"></i>CRM</a>
                <a href="{{ route('admin.welcome') }}" class="btn"><i class="fas fa-arrow-left"></i>Admin</a>
            </div>
        </header>

        <nav class="nav-tabs" aria-label="Navigation finance">
            @foreach($navItems as $item)
                <a class="nav-tab {{ request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*') ? 'active' : '' }}" href="{{ route($item['route']) }}">
                    <i class="fas fa-{{ $item['icon'] }}"></i>{{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        @if(session('success'))
            <div class="flash">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="error-box">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>
    <script>
        document.querySelectorAll('[data-dialog-open]').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                const dialog = document.getElementById(trigger.dataset.dialogOpen);
                if (!dialog) {
                    return;
                }

                if (typeof dialog.showModal === 'function') {
                    dialog.showModal();
                } else {
                    dialog.setAttribute('open', 'open');
                }
            });
        });

        document.querySelectorAll('[data-dialog-close]').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                trigger.closest('dialog')?.close();
            });
        });

        document.querySelectorAll('dialog[data-open-on-load="true"]').forEach((dialog) => {
            if (typeof dialog.showModal === 'function' && !dialog.open) {
                dialog.showModal();
            }
        });
    </script>
</body>
</html>
