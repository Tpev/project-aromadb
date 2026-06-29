<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $lead->full_name }} - CRM</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        :root {
            --bg: #f4f7fb; --panel: #fff; --soft: #f8fafc; --line: #d8e1ee;
            --text: #142033; --muted: #64748b; --blue: #1d4ed8; --red: #b91c1c;
            --shadow: 0 14px 36px rgba(15,23,42,.08);
        }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--bg); color: var(--text); font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; letter-spacing: 0; }
        a { color: inherit; }
        .page { width: min(1420px, calc(100% - 32px)); margin: 0 auto; padding: 22px 0 42px; }
        .topbar { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 14px; }
        .crumb { display: flex; gap: 8px; color: var(--muted); font-size: 13px; font-weight: 750; margin-bottom: 7px; }
        h1 { margin: 0; font-size: 31px; line-height: 1.12; overflow-wrap: anywhere; }
        .subtitle { margin: 7px 0 0; color: var(--muted); font-size: 14px; overflow-wrap: anywhere; }
        .actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 38px; padding: 9px 13px; border-radius: 8px; border: 1px solid var(--line); background: #fff; color: var(--text); font-size: 13px; font-weight: 850; text-decoration: none; cursor: pointer; }
        .btn-primary { background: var(--blue); border-color: var(--blue); color: #fff; }
        .btn-danger { background: #fff5f5; border-color: #fecaca; color: var(--red); }
        .btn-small { min-height: 32px; padding: 6px 9px; font-size: 12px; }
        .alert { border-radius: 8px; padding: 12px 14px; margin-bottom: 14px; border: 1px solid; font-weight: 750; font-size: 13px; }
        .alert-success { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
        .alert-error { background: #fff1f2; color: #9f1239; border-color: #fecaca; }
        .summary { display: grid; grid-template-columns: repeat(5, minmax(150px, 1fr)); gap: 10px; margin-bottom: 14px; }
        .stat { background: var(--panel); border: 1px solid var(--line); border-radius: 8px; padding: 13px; box-shadow: 0 1px 2px rgba(15,23,42,.04); min-width: 0; }
        .stat-label { color: var(--muted); font-size: 11px; font-weight: 900; text-transform: uppercase; display: flex; gap: 7px; align-items: center; }
        .stat-value { margin-top: 8px; font-size: 20px; line-height: 1.15; font-weight: 950; overflow-wrap: anywhere; }
        .layout { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(360px, .8fr); gap: 12px; align-items: start; }
        .panel { background: var(--panel); border: 1px solid var(--line); border-radius: 8px; padding: 14px; box-shadow: var(--shadow); }
        .panel + .panel { margin-top: 12px; }
        .panel-head { display: flex; justify-content: space-between; gap: 12px; align-items: center; margin-bottom: 12px; }
        .panel-head h2, .panel-head h3 { margin: 0; font-size: 16px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(180px, 1fr)); gap: 10px; }
        .field { min-width: 0; }
        .wide { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 5px; color: #334155; font-size: 12px; font-weight: 850; }
        input, select, textarea { width: 100%; min-height: 38px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; color: var(--text); font: inherit; font-size: 13px; padding: 8px 10px; outline: none; }
        textarea { min-height: 100px; resize: vertical; }
        input:focus, select:focus, textarea:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(29,78,216,.12); }
        .arr-preview { margin-top: 8px; padding: 8px 10px; border-radius: 8px; background: #eff6ff; color: #1e40af; font-size: 12px; font-weight: 850; }
        .timeline { display: grid; gap: 10px; }
        .activity { border: 1px solid var(--line); border-radius: 8px; padding: 11px; background: #fff; }
        .activity-head { display: flex; justify-content: space-between; gap: 8px; color: var(--muted); font-size: 11px; font-weight: 900; text-transform: uppercase; }
        .activity-title { margin: 7px 0 4px; font-weight: 900; overflow-wrap: anywhere; }
        .activity-body { color: #475569; font-size: 13px; line-height: 1.45; overflow-wrap: anywhere; }
        .chips { display: flex; flex-wrap: wrap; gap: 6px; }
        .chip { border: 1px solid #dbeafe; background: #eff6ff; color: #1d4ed8; border-radius: 8px; padding: 4px 8px; font-size: 12px; font-weight: 850; }
        .danger-zone { display: flex; justify-content: space-between; gap: 10px; align-items: center; border-top: 1px solid #fee2e2; padding-top: 12px; margin-top: 14px; }
        .muted { color: var(--muted); }
        @media (max-width: 980px) {
            .summary { grid-template-columns: repeat(2, minmax(150px, 1fr)); }
            .layout { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .page { width: min(100% - 20px, 100%); padding-top: 14px; }
            .topbar { flex-direction: column; }
            h1 { font-size: 25px; }
            .actions { width: 100%; align-items: stretch; }
            .btn { flex: 1 1 auto; }
            .summary, .form-grid { grid-template-columns: 1fr; }
            .wide { grid-column: auto; }
            .danger-zone { align-items: stretch; flex-direction: column; }
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
                <a href="{{ route('admin.crm.index') }}">CRM</a>
                <span>/</span>
                <span>Fiche lead</span>
            </div>
            <h1>{{ $lead->full_name }}</h1>
            <p class="subtitle">{{ $lead->company ?: 'Lead sans societe renseignee' }}</p>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.index') }}" class="btn"><i class="fas fa-arrow-left"></i>Retour CRM</a>
            @if($lead->email)<a href="mailto:{{ $lead->email }}" class="btn"><i class="fas fa-envelope"></i>Email</a>@endif
            @if($lead->phone)<a href="tel:{{ $lead->phone }}" class="btn"><i class="fas fa-phone"></i>Appeler</a>@endif
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif

    <section class="summary" aria-label="Synthese lead">
        <div class="stat"><div class="stat-label"><i class="fas fa-layer-group"></i>Etape</div><div class="stat-value">{{ $lead->stage_label }}</div></div>
        <div class="stat"><div class="stat-label"><i class="fas fa-euro-sign"></i>ARR</div><div class="stat-value">{{ $money($lead->arr) }} €</div></div>
        <div class="stat"><div class="stat-label"><i class="fas fa-id-card"></i>Licence visee</div><div class="stat-value">{{ $lead->expected_license_label }}</div></div>
        <div class="stat"><div class="stat-label"><i class="fas fa-award"></i>Licence reelle</div><div class="stat-value">{{ $lead->actual_license_label }}</div></div>
        <div class="stat"><div class="stat-label"><i class="fas fa-bell"></i>Prochaine relance</div><div class="stat-value">{{ $lead->next_follow_up_at ? $lead->next_follow_up_at->format('d/m/Y H:i') : 'A planifier' }}</div></div>
    </section>

    <section class="layout">
        <div>
            <div class="panel">
                <div class="panel-head"><h2>Informations commerciales</h2></div>
                <form method="POST" action="{{ route('admin.crm.leads.update', $lead) }}" class="form-grid">
                    @csrf
                    @method('PATCH')
                    <div class="field"><label for="full_name">Nom du lead</label><input id="full_name" name="full_name" value="{{ $lead->full_name }}" required></div>
                    <div class="field"><label for="company">Cabinet / societe</label><input id="company" name="company" value="{{ $lead->company }}"></div>
                    <div class="field"><label for="email">Email</label><input id="email" type="email" name="email" value="{{ $lead->email }}"></div>
                    <div class="field"><label for="phone">Telephone</label><input id="phone" name="phone" value="{{ $lead->phone }}"></div>
                    <div class="field"><label for="source">Source acquisition</label><input id="source" name="source" value="{{ $lead->source }}" list="source-list"></div>
                    <div class="field"><label for="referral_source">Source parrainage</label><input id="referral_source" name="referral_source" value="{{ $lead->referral_source }}" list="referral-source-list"></div>
                    <div class="field">
                        <label for="stage">Etape</label>
                        <select id="stage" name="stage">
                            @foreach($stageLabels as $stageKey => $label)
                                <option value="{{ $stageKey }}" @selected($lead->stage === $stageKey)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="owner_user_id">Responsable</label>
                        <select id="owner_user_id" name="owner_user_id">
                            <option value="">Non assigne</option>
                            @foreach($adminUsers as $adminUser)
                                <option value="{{ $adminUser->id }}" @selected((int) $lead->owner_user_id === (int) $adminUser->id)>{{ $adminUser->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="expected_license_type">Licence visee</label>
                        <select id="expected_license_type" name="expected_license_type" data-license-select>
                            <option value="" data-arr="0">Non definie</option>
                            @foreach($licenseOptions as $licenseKey => $license)
                                <option value="{{ $licenseKey }}" data-arr="{{ $license['arr'] }}" @selected($lead->expected_license_type === $licenseKey)>{{ $license['label'] }} - {{ $money($license['arr']) }} € ARR</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="actual_license_type">Licence reelle</label>
                        <select id="actual_license_type" name="actual_license_type" data-license-select>
                            <option value="" data-arr="0">Non definie</option>
                            @foreach($licenseOptions as $licenseKey => $license)
                                <option value="{{ $licenseKey }}" data-arr="{{ $license['arr'] }}" @selected($lead->actual_license_type === $licenseKey)>{{ $license['label'] }} - {{ $money($license['arr']) }} € ARR</option>
                            @endforeach
                        </select>
                        <div class="arr-preview" data-arr-preview>ARR : {{ $money($lead->arr) }} €</div>
                    </div>
                    <div class="field"><label for="probability">Probabilite</label><input id="probability" type="number" min="0" max="100" name="probability" value="{{ $lead->probability }}"></div>
                    <div class="field"><label for="expected_close_date">Date cible closing</label><input id="expected_close_date" type="date" name="expected_close_date" value="{{ $lead->expected_close_date ? $lead->expected_close_date->format('Y-m-d') : '' }}"></div>
                    <div class="field"><label for="next_follow_up_at">Prochaine relance</label><input id="next_follow_up_at" type="datetime-local" name="next_follow_up_at" value="{{ $dateTimeLocal($lead->next_follow_up_at) }}"></div>
                    <div class="field"><label for="lost_reason">Raison de perte</label><input id="lost_reason" name="lost_reason" value="{{ $lead->lost_reason }}"></div>
                    <div class="field wide"><label for="tags">Tags</label><input id="tags" name="tags" value="{{ $lead->tags_as_text }}" placeholder="chaud, partenaire, urgent"></div>
                    <div class="field wide"><label for="notes">Notes internes</label><textarea id="notes" name="notes">{{ $lead->notes }}</textarea></div>
                    <div class="field wide"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i>Enregistrer</button></div>
                </form>

                <datalist id="source-list">
                    @foreach($sourceOptions as $source)<option value="{{ $source }}"></option>@endforeach
                </datalist>
                <datalist id="referral-source-list">
                    @foreach($referralSourceOptions as $source)<option value="{{ $source }}"></option>@endforeach
                </datalist>
            </div>

            <div class="panel">
                <div class="panel-head"><h2>Ajouter un point de contact</h2></div>
                <form method="POST" action="{{ route('admin.crm.activities.store', $lead) }}" class="form-grid">
                    @csrf
                    <div class="field">
                        <label for="activity_type">Type</label>
                        <select id="activity_type" name="activity_type">
                            @foreach($activityTypes as $typeKey => $typeLabel)<option value="{{ $typeKey }}">{{ $typeLabel }}</option>@endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="activity_direction">Sens</label>
                        <select id="activity_direction" name="activity_direction">
                            @foreach($activityDirections as $dirKey => $dirLabel)<option value="{{ $dirKey }}">{{ $dirLabel }}</option>@endforeach
                        </select>
                    </div>
                    <div class="field"><label for="activity_subject">Sujet</label><input id="activity_subject" name="activity_subject"></div>
                    <div class="field"><label for="activity_outcome">Resultat</label><input id="activity_outcome" name="activity_outcome"></div>
                    <div class="field"><label for="activity_occurred_at">Date du contact</label><input id="activity_occurred_at" type="datetime-local" name="activity_occurred_at"></div>
                    <div class="field"><label for="activity_due_at">Prochaine action</label><input id="activity_due_at" type="datetime-local" name="activity_due_at"></div>
                    <div class="field wide"><label for="activity_body">Compte rendu</label><textarea id="activity_body" name="activity_body" required></textarea></div>
                    <div class="field wide"><button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i>Ajouter a la timeline</button></div>
                </form>
            </div>
        </div>

        <aside>
            <div class="panel">
                <div class="panel-head"><h3>Résumé</h3></div>
                <div class="timeline">
                    <div><strong>Email :</strong> <span class="muted">{{ $lead->email ?: 'Non renseigne' }}</span></div>
                    <div><strong>Telephone :</strong> <span class="muted">{{ $lead->phone ?: 'Non renseigne' }}</span></div>
                    <div><strong>Source :</strong> <span class="muted">{{ $lead->source ?: 'Non renseignee' }}</span></div>
                    <div><strong>Parrainage :</strong> <span class="muted">{{ $lead->referral_source ?: 'Non renseigne' }}</span></div>
                    <div><strong>Dernier contact :</strong> <span class="muted">{{ $lead->last_touch_at ? $lead->last_touch_at->format('d/m/Y H:i') : 'Aucun' }}</span></div>
                    @if($lead->tag_list)
                        <div class="chips">@foreach($lead->tag_list as $tag)<span class="chip">{{ $tag }}</span>@endforeach</div>
                    @endif
                </div>
                <div class="danger-zone">
                    <span class="muted">Archiver ce lead si le suivi n'est plus utile.</span>
                    <form method="POST" action="{{ route('admin.crm.leads.destroy', $lead) }}" onsubmit="return confirm('Archiver ce lead ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-small"><i class="fas fa-archive"></i>Archiver</button>
                    </form>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head"><h3>Timeline</h3></div>
                <div class="timeline">
                    @forelse($lead->activities as $activity)
                        <article class="activity">
                            <div class="activity-head">
                                <span>{{ $activity->type_label }} · {{ $activity->direction ? ($activityDirections[$activity->direction] ?? $activity->direction) : 'Interne' }}</span>
                                <span>{{ $activity->occurred_at ? $activity->occurred_at->format('d/m/Y H:i') : $activity->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            @if($activity->subject)<div class="activity-title">{{ $activity->subject }}</div>@endif
                            <div class="activity-body">{{ $activity->body }}</div>
                            @if($activity->outcome)<div class="activity-body"><strong>Resultat :</strong> {{ $activity->outcome }}</div>@endif
                            @if($activity->due_at)<div class="activity-body"><strong>Prochaine action :</strong> {{ $activity->due_at->format('d/m/Y H:i') }}</div>@endif
                        </article>
                    @empty
                        <div class="muted">Aucun point de contact pour le moment.</div>
                    @endforelse
                </div>
            </div>
        </aside>
    </section>
</main>

<script>
    (function () {
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
    })();
</script>
</body>
</html>
