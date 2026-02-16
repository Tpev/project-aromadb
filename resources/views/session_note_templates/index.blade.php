{{-- resources/views/session_note_templates/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#647a0b;">
            {{ __('Templates de notes de séance') }}
        </h2>
    </x-slot>

    <div class="max-w-6xl mx-auto py-8 px-4">
        @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-100 border border-green-200 text-green-800 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="am-card">
            <div class="am-head">
                <div>
                    <h1 class="am-title">{{ __('Templates de notes de séance') }}</h1>
                    <p class="am-sub">{{ __('Créez vos modèles et réutilisez-les en 1 clic lors de la création d’une note.') }}</p>
                </div>

                <div class="am-actions">
                    <input
                        type="text"
                        id="search"
                        class="am-input"
                        placeholder="Rechercher par titre…"
                        onkeyup="filterTable()"
                    >

                    <a href="{{ route('session-note-templates.create') }}" class="am-btn am-btn-primary">
                        + {{ __('Créer un template') }}
                    </a>
                </div>
            </div>

            @if(($templates ?? collect())->isEmpty())
                <div class="am-empty">
                    <div class="am-empty-title">{{ __('Aucun template pour le moment') }}</div>
                    <div class="am-empty-sub">{{ __('Créez votre premier modèle pour accélérer vos notes de séance.') }}</div>

                    <a href="{{ route('session-note-templates.create') }}" class="am-btn am-btn-primary mt-3">
                        + {{ __('Créer un template') }}
                    </a>
                </div>
            @else
                <div class="am-tablewrap">
                    <table class="am-table" id="templatesTable" data-sort-dir="asc">
                        <thead>
                            <tr>
                                <th class="am-sort" onclick="sortTable(0)">
                                    {{ __('Titre') }} <span class="am-sort-icon">⇅</span>
                                </th>
                                <th class="am-th-actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $t)
                                <tr class="am-row" data-title="{{ strtolower($t->title) }}">
                                    <td class="am-td-title">
                                        <a class="am-link" href="{{ route('session-note-templates.show', $t->id) }}">
                                            {{ $t->title }}
                                        </a>
                                        <div class="am-meta">
                                            {{ __('Mis à jour le') }} {{ optional($t->updated_at)->format('d/m/Y') }}
                                        </div>
                                    </td>

                                    <td class="am-td-actions">
                                        <a class="am-btn am-btn-soft" href="{{ route('session-note-templates.show', $t->id) }}">
                                            {{ __('Voir') }}
                                        </a>
                                        <a class="am-btn am-btn-soft" href="{{ route('session-note-templates.edit', $t->id) }}">
                                            {{ __('Modifier') }}
                                        </a>
                                        <form action="{{ route('session-note-templates.destroy', $t->id) }}" method="POST" class="am-inline"
                                              onsubmit="return confirm('Supprimer ce template ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="am-btn am-btn-danger">
                                                {{ __('Supprimer') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="am-foot">
                        <div class="am-foot-left">
                            <span id="countLabel"></span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <style>
        .am-card{
            background:#fff;border-radius:14px;box-shadow:0 6px 20px rgba(15,23,42,.08);
            border:1px solid rgba(100,122,11,.15); overflow:hidden;
        }
        .am-head{
            display:flex;gap:16px;align-items:flex-end;justify-content:space-between;
            padding:18px;border-bottom:1px solid rgba(15,23,42,.08);flex-wrap:wrap;
        }
        .am-title{font-size:22px;font-weight:900;color:#0f172a;margin:0;}
        .am-sub{margin:6px 0 0;color:#6b7280;font-size:13px;}
        .am-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
        .am-input{
            width:280px;max-width:70vw;padding:10px 12px;border-radius:10px;border:1px solid rgba(133,79,56,.35);
            outline:none;background:#fff;
        }
        .am-input:focus{border-color:#854f38; box-shadow:0 0 0 3px rgba(133,79,56,.12);}

        .am-btn{display:inline-flex;align-items:center;justify-content:center;padding:9px 12px;border-radius:10px;text-decoration:none;border:1px solid transparent;font-weight:800;font-size:13px;cursor:pointer;white-space:nowrap;}
        .am-btn-primary{background:#647a0b;color:#fff;}
        .am-btn-primary:hover{background:#854f38;}
        .am-btn-soft{background:#f8fafc;color:#0f172a;border-color:rgba(15,23,42,.12);}
        .am-btn-soft:hover{border-color:rgba(133,79,56,.55); background:#fff;}
        .am-btn-danger{background:#dc2626;color:#fff;}
        .am-btn-danger:hover{background:#b91c1c;}
        .am-inline{display:inline;}

        .am-tablewrap{padding:12px 12px 16px;}
        .am-table{width:100%;border-collapse:separate;border-spacing:0;}
        .am-table thead th{
            text-align:left;padding:12px;border-bottom:1px solid rgba(15,23,42,.10);font-size:12px;color:#64748b;
        }
        .am-sort{cursor:pointer;user-select:none;}
        .am-sort-icon{opacity:.6;margin-left:6px;}
        .am-row td{padding:14px 12px;border-bottom:1px solid rgba(15,23,42,.06);vertical-align:top;}
        .am-row:hover{background:rgba(100,122,11,.05);}

        .am-td-actions{display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap;}
        .am-th-actions{text-align:right;}
        .am-link{color:#0f172a;text-decoration:none;font-weight:900;}
        .am-link:hover{color:#854f38;text-decoration:underline;}
        .am-meta{margin-top:4px;color:#6b7280;font-size:12px;}

        .am-empty{padding:28px;text-align:center;}
        .am-empty-title{font-weight:900;font-size:18px;color:#0f172a;}
        .am-empty-sub{margin-top:6px;color:#6b7280;}

        .am-foot{padding:10px 4px 0;display:flex;justify-content:space-between;align-items:center;}
        .am-foot-left{color:#6b7280;font-size:12px;}

        @media (max-width: 720px){
            .am-td-actions{justify-content:flex-start;}
            .am-th-actions{text-align:left;}
            .am-input{width:100%;}
        }
    </style>

    <script>
        function updateCount() {
            const table = document.getElementById('templatesTable');
            const label = document.getElementById('countLabel');
            if (!table || !label) return;

            const rows = Array.from(table.querySelectorAll('tbody tr'));
            const visible = rows.filter(r => r.style.display !== 'none').length;
            label.textContent = visible + ' template(s)';
        }

        function filterTable() {
            const input = document.getElementById('search');
            const filter = (input.value || '').toLowerCase();
            const table = document.getElementById('templatesTable');
            if (!table) return;

            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const title = row.getAttribute('data-title') || '';
                row.style.display = title.includes(filter) ? '' : 'none';
            });

            updateCount();
        }

        function sortTable(colIndex) {
            const table = document.getElementById('templatesTable');
            if (!table) return;

            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            const current = table.getAttribute('data-sort-dir') || 'asc';
            const nextDir = current === 'asc' ? 'desc' : 'asc';
            table.setAttribute('data-sort-dir', nextDir);

            rows.sort((a, b) => {
                const A = (a.children[colIndex]?.innerText || '').trim().toLowerCase();
                const B = (b.children[colIndex]?.innerText || '').trim().toLowerCase();
                if (A < B) return nextDir === 'asc' ? -1 : 1;
                if (A > B) return nextDir === 'asc' ? 1 : -1;
                return 0;
            });

            rows.forEach(r => tbody.appendChild(r));
            updateCount();
        }

        document.addEventListener('DOMContentLoaded', updateCount);
    </script>
</x-app-layout>
