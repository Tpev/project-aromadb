<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CRM Admin - Leads</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        :root {
            --bg: #f4f7fb;
            --panel: #fff;
            --soft: #f8fafc;
            --line: #d8e1ee;
            --text: #142033;
            --muted: #64748b;
            --blue: #1d4ed8;
            --green: #15803d;
            --red: #b91c1c;
            --shadow: 0 14px 36px rgba(15, 23, 42, .08);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            letter-spacing: 0;
        }
        a { color: inherit; }
        .page { width: min(1880px, calc(100% - 32px)); margin: 0 auto; padding: 22px 0 42px; }
        .topbar { display: flex; align-items: flex-start; justify-content: space-between; gap: 18px; margin-bottom: 16px; }
        .crumb { display: flex; gap: 8px; color: var(--muted); font-size: 13px; font-weight: 700; margin-bottom: 7px; }
        h1 { margin: 0; font-size: 32px; line-height: 1.1; }
        .subtitle { margin: 8px 0 0; color: var(--muted); font-size: 14px; max-width: 760px; }
        .actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            min-height: 38px; padding: 9px 13px; border-radius: 8px; border: 1px solid var(--line);
            background: #fff; color: var(--text); font-size: 13px; font-weight: 800; text-decoration: none; cursor: pointer;
        }
        .btn:hover { border-color: #94a3b8; }
        .btn-primary { background: var(--blue); border-color: var(--blue); color: #fff; }
        .btn-small { min-height: 32px; padding: 6px 9px; font-size: 12px; }
        .alert { border-radius: 8px; padding: 12px 14px; margin-bottom: 14px; border: 1px solid; font-weight: 750; font-size: 13px; }
        .alert-success { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
        .alert-error { background: #fff1f2; color: #9f1239; border-color: #fecaca; }
        .metrics { display: grid; grid-template-columns: repeat(6, minmax(148px, 1fr)); gap: 10px; margin-bottom: 14px; }
        .metric { background: var(--panel); border: 1px solid var(--line); border-radius: 8px; padding: 14px; box-shadow: 0 1px 2px rgba(15,23,42,.04); }
        .metric-label { color: var(--muted); font-size: 11px; font-weight: 900; text-transform: uppercase; display: flex; gap: 7px; align-items: center; }
        .metric-value { margin-top: 8px; font-size: 24px; font-weight: 900; line-height: 1; }
        .panel { background: var(--panel); border: 1px solid var(--line); border-radius: 8px; padding: 14px; box-shadow: var(--shadow); }
        .panel-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 12px; }
        .panel-head h2, .panel-head h3 { margin: 0; font-size: 16px; }
        .toolbar { display: grid; grid-template-columns: minmax(0, 1.55fr) minmax(360px, .8fr); gap: 12px; margin-bottom: 14px; align-items: start; }
        .form-grid { display: grid; grid-template-columns: repeat(4, minmax(150px, 1fr)); gap: 10px; }
        .form-grid.two { grid-template-columns: repeat(2, minmax(150px, 1fr)); }
        .field { min-width: 0; }
        .wide { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 5px; color: #334155; font-size: 12px; font-weight: 850; }
        input, select, textarea {
            width: 100%; min-height: 38px; border: 1px solid #cbd5e1; border-radius: 8px;
            background: #fff; color: var(--text); font: inherit; font-size: 13px; padding: 8px 10px; outline: none;
        }
        textarea { min-height: 76px; resize: vertical; }
        input:focus, select:focus, textarea:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(29,78,216,.12); }
        details.quick { background: var(--soft); border: 1px solid var(--line); border-radius: 8px; padding: 10px; }
        details.quick summary {
            cursor: pointer; list-style: none; display: flex; gap: 8px; align-items: center;
            color: var(--text); font-size: 14px; font-weight: 900;
        }
        details.quick summary::-webkit-details-marker { display: none; }
        .arr-preview { margin-top: 8px; padding: 8px 10px; border-radius: 8px; background: #eff6ff; color: #1e40af; font-size: 12px; font-weight: 850; }
        .workspace { display: grid; grid-template-columns: minmax(0, 1fr) 330px; gap: 12px; align-items: start; }
        .kanban-shell { overflow-x: auto; padding-bottom: 8px; }
        .kanban { display: grid; grid-template-columns: repeat(9, minmax(286px, 1fr)); gap: 10px; min-width: 2640px; }
        .column { background: #eef3f8; border: 1px solid #d7e0ec; border-radius: 8px; min-height: 520px; display: flex; flex-direction: column; }
        .column-head { border-top: 4px solid var(--stage); padding: 12px; background: rgba(255,255,255,.82); border-radius: 8px 8px 0 0; border-bottom: 1px solid #d7e0ec; }
        .column-kpi { display: flex; justify-content: space-between; gap: 8px; color: var(--muted); font-size: 11px; font-weight: 900; text-transform: uppercase; }
        .column-title { display: flex; align-items: center; gap: 8px; margin-top: 7px; font-size: 16px; font-weight: 900; }
        .column-desc { min-height: 30px; margin-top: 3px; color: var(--muted); font-size: 12px; line-height: 1.35; }
        .drop-zone { flex: 1; display: grid; align-content: start; gap: 10px; min-height: 380px; padding: 10px; }
        .drop-zone.is-over { background: rgba(29,78,216,.08); }
        .lead-card { background: #fff; border: 1px solid #d8e2ee; border-left: 4px solid var(--lead); border-radius: 8px; padding: 12px; box-shadow: 0 5px 18px rgba(15,23,42,.06); overflow: hidden; }
        .lead-card.dragging { opacity: .65; }
        .lead-main { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; }
        .lead-name { font-size: 15px; font-weight: 900; line-height: 1.25; overflow-wrap: anywhere; }
        .lead-company { margin-top: 2px; color: var(--muted); font-size: 12px; overflow-wrap: anywhere; }
        .arr-pill { flex: 0 0 auto; padding: 5px 8px; border-radius: 8px; background: #ecfdf5; color: #166534; font-size: 12px; font-weight: 950; white-space: nowrap; }
        .meta { display: grid; gap: 6px; margin: 10px 0; color: #475569; font-size: 12px; }
        .meta-line { display: flex; align-items: center; gap: 7px; min-width: 0; }
        .meta-line span { min-width: 0; overflow-wrap: anywhere; }
        .tags { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 10px; }
        .tag { border: 1px solid #dbeafe; background: #eff6ff; color: #1d4ed8; border-radius: 8px; padding: 3px 7px; font-size: 11px; font-weight: 850; }
        .card-actions { display: grid; grid-template-columns: 1fr auto; gap: 8px; align-items: end; border-top: 1px solid #e2e8f0; padding-top: 10px; margin-top: 10px; }
        .stage-form label { margin-bottom: 4px; }
        .empty { border: 1px dashed #cbd5e1; border-radius: 8px; color: var(--muted); padding: 14px; text-align: center; font-size: 12px; font-weight: 850; }
        .rail { position: sticky; top: 16px; }
        .rail-list { display: grid; gap: 10px; max-height: calc(100vh - 112px); overflow-y: auto; padding-right: 2px; }
        .rail-item { background: #fff; border: 1px solid var(--line); border-radius: 8px; padding: 10px; }
        .rail-kicker { display: flex; justify-content: space-between; gap: 8px; color: var(--muted); font-size: 11px; font-weight: 900; text-transform: uppercase; }
        .rail-lead { margin: 6px 0; font-size: 13px; font-weight: 900; overflow-wrap: anywhere; }
        .muted { color: var(--muted); }
        @media (max-width: 1280px) {
            .metrics { grid-template-columns: repeat(3, minmax(150px, 1fr)); }
            .toolbar, .workspace { grid-template-columns: 1fr; }
            .rail { position: static; }
            .rail-list { max-height: none; }
        }
        @media (max-width: 760px) {
            .page { width: min(100% - 20px, 100%); padding-top: 14px; }
            .topbar { flex-direction: column; }
            h1 { font-size: 26px; }
            .actions { width: 100%; align-items: stretch; }
            .btn { flex: 1 1 auto; }
            .metrics, .form-grid, .form-grid.two { grid-template-columns: 1fr; }
            .kanban-shell { overflow: visible; }
            .kanban { min-width: 0; grid-template-columns: 1fr; }
            .column { min-height: 0; }
            .drop-zone { min-height: 96px; }
            .card-actions { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
@php
    $money = fn ($value) => number_format((float) ($value ?? 0), 0, ',', ' ');
    $dateTimeLocal = fn ($value) => $value ? $value->format('Y-m-d\TH:i') : '';
@endphp

<main class="page">
    <div class="topbar">
        <div>
            <div class="crumb">
                <a href="{{ route('admin.welcome') }}">Admin</a>
                <span>/</span>
                <span>CRM</span>
            </div>
            <h1>CRM leads</h1>
            <p class="subtitle">Suivi commercial des prospects, de la première qualification jusqu'à la conversion. La valeur affichée est l'ARR calculé depuis la licence visée ou réelle.</p>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.export', request()->query()) }}" class="btn"><i class="fas fa-file-export"></i>Exporter</a>
            <a href="{{ route('admin.crm.import-template') }}" class="btn"><i class="fas fa-download"></i>Modèle CSV</a>
            <a href="{{ route('admin.welcome') }}" class="btn"><i class="fas fa-arrow-left"></i>Admin</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <section class="metrics" aria-label="Indicateurs CRM">
        <div class="metric"><div class="metric-label"><i class="fas fa-address-book"></i>Leads</div><div class="metric-value">{{ $metrics['total'] }}</div></div>
        <div class="metric"><div class="metric-label"><i class="fas fa-stream"></i>Ouverts</div><div class="metric-value">{{ $metrics['open'] }}</div></div>
        <div class="metric"><div class="metric-label"><i class="fas fa-euro-sign"></i>ARR pipeline</div><div class="metric-value">{{ $money($metrics['pipeline_value']) }} €</div></div>
        <div class="metric"><div class="metric-label"><i class="fas fa-bell"></i>Relances dues</div><div class="metric-value">{{ $metrics['due_followups'] }}</div></div>
        <div class="metric"><div class="metric-label"><i class="fas fa-check-circle"></i>Gagnés</div><div class="metric-value">{{ $metrics['won'] }}</div></div>
        <div class="metric"><div class="metric-label"><i class="fas fa-cash-register"></i>ARR gagné</div><div class="metric-value">{{ $money($metrics['won_value']) }} €</div></div>
    </section>

    <section class="toolbar">
        <div class="panel">
            <div class="panel-head">
                <h2>Filtres</h2>
                <a href="{{ route('admin.crm.index') }}" class="btn btn-small"><i class="fas fa-times"></i>Réinitialiser</a>
            </div>
            <form method="GET" action="{{ route('admin.crm.index') }}" class="form-grid">
                <div class="field">
                    <label for="q">Recherche</label>
                    <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nom, email, téléphone, notes">
                </div>
                <div class="field">
                    <label for="stage">Étape</label>
                    <select id="stage" name="stage">
                        <option value="">Toutes</option>
                        @foreach($stageLabels as $stageKey => $label)
                            <option value="{{ $stageKey }}" @selected(($filters['stage'] ?? '') === $stageKey)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="source">Source acquisition</label>
                    <select id="source" name="source">
                        <option value="">Toutes</option>
                        @foreach($sourceOptions as $source)
                            <option value="{{ $source }}" @selected(($filters['source'] ?? '') === $source)>{{ $source }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="referral_source">Source parrainage</label>
                    <select id="referral_source" name="referral_source">
                        <option value="">Toutes</option>
                        @foreach($referralSourceOptions as $source)
                            <option value="{{ $source }}" @selected(($filters['referral_source'] ?? '') === $source)>{{ $source }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="expected_license_type">Licence visée</label>
                    <select id="expected_license_type" name="expected_license_type">
                        <option value="">Toutes</option>
                        @foreach($licenseOptions as $licenseKey => $license)
                            <option value="{{ $licenseKey }}" @selected(($filters['expected_license_type'] ?? '') === $licenseKey)>{{ $license['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="actual_license_type">Licence réelle</label>
                    <select id="actual_license_type" name="actual_license_type">
                        <option value="">Toutes</option>
                        @foreach($licenseOptions as $licenseKey => $license)
                            <option value="{{ $licenseKey }}" @selected(($filters['actual_license_type'] ?? '') === $licenseKey)>{{ $license['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="from">Créé du</label>
                    <input id="from" type="date" name="from" value="{{ $filters['from'] ?? '' }}">
                </div>
                <div class="field">
                    <label for="to">Créé au</label>
                    <input id="to" type="date" name="to" value="{{ $filters['to'] ?? '' }}">
                </div>
                <div class="field">
                    <label for="follow_to">Relance avant</label>
                    <input id="follow_to" type="date" name="follow_to" value="{{ $filters['follow_to'] ?? '' }}">
                </div>
                <div class="field">
                    <label for="owner_user_id">Responsable</label>
                    <select id="owner_user_id" name="owner_user_id">
                        <option value="">Tous</option>
                        @foreach($adminUsers as $adminUser)
                            <option value="{{ $adminUser->id }}" @selected((string)($filters['owner_user_id'] ?? '') === (string)$adminUser->id)>{{ $adminUser->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field" style="align-self:end;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i>Appliquer</button>
                </div>
            </form>
        </div>

        <details class="quick" open>
            <summary><i class="fas fa-plus-circle"></i>Nouveau lead</summary>
            <form method="POST" action="{{ route('admin.crm.leads.store') }}" class="form-grid two" style="margin-top:12px;">
                @csrf
                <div class="field"><label for="new_full_name">Nom du lead</label><input id="new_full_name" name="full_name" required></div>
                <div class="field"><label for="new_company">Cabinet / société</label><input id="new_company" name="company"></div>
                <div class="field"><label for="new_email">Email</label><input id="new_email" type="email" name="email"></div>
                <div class="field"><label for="new_phone">Téléphone</label><input id="new_phone" name="phone"></div>
                <div class="field"><label for="new_source">Source acquisition</label><input id="new_source" name="source" list="source-list"></div>
                <div class="field"><label for="new_referral_source">Source parrainage</label><input id="new_referral_source" name="referral_source" list="referral-source-list"></div>
                <div class="field">
                    <label for="new_stage">Étape</label>
                    <select id="new_stage" name="stage">
                        @foreach($stageLabels as $stageKey => $label)
                            <option value="{{ $stageKey }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="new_expected_license_type">Licence visée</label>
                    <select id="new_expected_license_type" name="expected_license_type" data-license-select>
                        <option value="" data-arr="0">Non définie</option>
                        @foreach($licenseOptions as $licenseKey => $license)
                            <option value="{{ $licenseKey }}" data-arr="{{ $license['arr'] }}">{{ $license['label'] }} - {{ $money($license['arr']) }} € ARR</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="new_actual_license_type">Licence réelle</label>
                    <select id="new_actual_license_type" name="actual_license_type" data-license-select>
                        <option value="" data-arr="0">Non définie</option>
                        @foreach($licenseOptions as $licenseKey => $license)
                            <option value="{{ $licenseKey }}" data-arr="{{ $license['arr'] }}">{{ $license['label'] }} - {{ $money($license['arr']) }} € ARR</option>
                        @endforeach
                    </select>
                    <div class="arr-preview" data-arr-preview>ARR estimé : 0 €</div>
                </div>
                <div class="field"><label for="new_next_follow_up_at">Prochaine relance</label><input id="new_next_follow_up_at" type="datetime-local" name="next_follow_up_at"></div>
                <div class="field wide"><label for="new_tags">Tags</label><input id="new_tags" name="tags" placeholder="chaud, partenaire, urgent"></div>
                <div class="field wide"><label for="new_notes">Notes</label><textarea id="new_notes" name="notes"></textarea></div>
                <div class="field wide"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i>Créer le lead</button></div>
            </form>
        </details>
    </section>

    <datalist id="source-list">
        @foreach($sourceOptions as $source)<option value="{{ $source }}"></option>@endforeach
    </datalist>
    <datalist id="referral-source-list">
        @foreach($referralSourceOptions as $source)<option value="{{ $source }}"></option>@endforeach
    </datalist>

    <section class="workspace">
        <div class="kanban-shell">
            <div class="kanban" aria-label="Tunnel commercial">
                @foreach($columns as $column)
                    <section class="column" style="--stage: {{ $column['accent'] }};">
                        <header class="column-head">
                            <div class="column-kpi"><span>{{ $column['count'] }} leads</span><span>{{ $money($column['value']) }} € ARR</span></div>
                            <div class="column-title"><i class="fas fa-circle" style="color: {{ $column['accent'] }};"></i>{{ $stageLabels[$column['key']] ?? $column['label'] }}</div>
                            <div class="column-desc">{{ $column['description'] }}</div>
                        </header>
                        <div class="drop-zone" data-stage="{{ $column['key'] }}">
                            @forelse($column['leads'] as $lead)
                                <article class="lead-card" draggable="true" data-lead-id="{{ $lead->id }}" data-stage-url="{{ route('admin.crm.leads.stage', $lead) }}" style="--lead: {{ $lead->stage_accent }};">
                                    <div class="lead-main">
                                        <div>
                                            <div class="lead-name">{{ $lead->full_name }}</div>
                                            @if($lead->company)<div class="lead-company">{{ $lead->company }}</div>@endif
                                        </div>
                                        <div class="arr-pill">{{ $money($lead->arr) }} €</div>
                                    </div>
                                    <div class="meta">
                                        @if($lead->email)<div class="meta-line"><i class="fas fa-envelope"></i><span>{{ $lead->email }}</span></div>@endif
                                        @if($lead->phone)<div class="meta-line"><i class="fas fa-phone"></i><span>{{ $lead->phone }}</span></div>@endif
                                        <div class="meta-line"><i class="fas fa-id-card"></i><span>Visée : {{ $lead->expected_license_label }}</span></div>
                                        <div class="meta-line"><i class="fas fa-award"></i><span>Réelle : {{ $lead->actual_license_label }}</span></div>
                                        @if($lead->source)<div class="meta-line"><i class="fas fa-bullseye"></i><span>{{ $lead->source }}</span></div>@endif
                                        @if($lead->referral_source)<div class="meta-line"><i class="fas fa-share-alt"></i><span>{{ $lead->referral_source }}</span></div>@endif
                                        @if($lead->next_follow_up_at)<div class="meta-line"><i class="fas fa-bell"></i><span>{{ $lead->next_follow_up_at->format('d/m/Y H:i') }}</span></div>@endif
                                    </div>
                                    @if($lead->tag_list)
                                        <div class="tags">
                                            @foreach($lead->tag_list as $tag)<span class="tag">{{ $tag }}</span>@endforeach
                                        </div>
                                    @endif
                                    <div class="card-actions">
                                        <form method="POST" action="{{ route('admin.crm.leads.stage', $lead) }}" class="stage-form">
                                            @csrf
                                            @method('PATCH')
                                            <label for="stage_{{ $lead->id }}">Déplacer</label>
                                            <select id="stage_{{ $lead->id }}" name="stage" onchange="this.form.submit()">
                                                @foreach($stageLabels as $stageKey => $label)
                                                    <option value="{{ $stageKey }}" @selected($lead->stage === $stageKey)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                        <a class="btn btn-small" href="{{ route('admin.crm.leads.show', $lead) }}"><i class="fas fa-eye"></i>Voir</a>
                                    </div>
                                </article>
                            @empty
                                <div class="empty">Aucun lead</div>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </div>

        <aside class="rail panel" aria-label="Derniers points de contact">
            <div class="panel-head"><h3>Activité récente</h3></div>
            <div class="rail-list">
                @forelse($recentActivities as $activity)
                    <div class="rail-item">
                        <div class="rail-kicker">
                            <span>{{ $activity->type_label }}</span>
                            @if($activity->occurred_at)<span>{{ $activity->occurred_at->format('d/m H:i') }}</span>@endif
                        </div>
                        <div class="rail-lead">{{ $activity->lead?->full_name }}</div>
                        @if($activity->subject)<div><strong>{{ $activity->subject }}</strong></div>@endif
                        <div class="muted">{{ $activity->body }}</div>
                    </div>
                @empty
                    <div class="empty">Aucune activité pour ce filtre</div>
                @endforelse
            </div>
        </aside>
    </section>
</main>

<script>
    (function () {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let draggedCard = null;

        function updateArrPreview(scope) {
            const expected = scope.querySelector('[name="expected_license_type"]');
            const actual = scope.querySelector('[name="actual_license_type"]');
            const preview = scope.querySelector('[data-arr-preview]');
            if (!preview || !expected || !actual) return;
            const selected = actual.selectedOptions[0]?.value ? actual : expected;
            const arr = Number(selected.selectedOptions[0]?.dataset.arr || 0);
            preview.textContent = new Intl.NumberFormat('fr-FR').format(arr) + ' € ARR';
        }

        document.querySelectorAll('form').forEach((form) => {
            form.querySelectorAll('[data-license-select]').forEach((select) => {
                select.addEventListener('change', () => updateArrPreview(form));
            });
            updateArrPreview(form);
        });

        document.querySelectorAll('.lead-card').forEach((card) => {
            card.addEventListener('dragstart', (event) => {
                draggedCard = card;
                card.classList.add('dragging');
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', card.dataset.leadId);
            });
            card.addEventListener('dragend', () => {
                card.classList.remove('dragging');
                draggedCard = null;
            });
        });

        document.querySelectorAll('.drop-zone').forEach((zone) => {
            zone.addEventListener('dragover', (event) => {
                event.preventDefault();
                zone.classList.add('is-over');
            });
            zone.addEventListener('dragleave', () => zone.classList.remove('is-over'));
            zone.addEventListener('drop', async (event) => {
                event.preventDefault();
                zone.classList.remove('is-over');
                if (!draggedCard) return;

                const previousParent = draggedCard.parentElement;
                const stage = zone.dataset.stage;
                zone.appendChild(draggedCard);

                const select = draggedCard.querySelector('.stage-form select');
                if (select) select.value = stage;

                try {
                    const response = await fetch(draggedCard.dataset.stageUrl, {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({ stage })
                    });
                    if (!response.ok) throw new Error('stage update failed');
                } catch (error) {
                    previousParent.appendChild(draggedCard);
                    window.location.reload();
                }
            });
        });
    })();
</script>
</body>
</html>
