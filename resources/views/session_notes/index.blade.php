{{-- resources/views/session_notes/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#6B4A3A;">
            {{ __('Notes de séance') }}
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
                    <h1 class="am-title">{{ __('Notes de séance') }}</h1>
                    <p class="am-sub">
                        {{ $clientProfile->first_name }} {{ $clientProfile->last_name }}
                    </p>
                </div>

                <div class="am-actions">
                    <input
                        type="text"
                        id="search"
                        class="am-input"
                        placeholder="Rechercher (date, contenu...)"
                        onkeyup="filterTable()"
                    >

                    <a href="{{ route('session_notes.create', $clientProfile->id) }}" class="am-btn am-btn-primary">
                        + {{ __('Créer') }}
                    </a>
                </div>
            </div>

            @if(($sessionNotes ?? collect())->isEmpty())
                <div class="am-empty">
                    <div class="am-empty-title">{{ __('Aucune note pour le moment') }}</div>
                    <div class="am-empty-sub">{{ __('Créez votre première note de séance pour ce client.') }}</div>

                    <a href="{{ route('session_notes.create', $clientProfile->id) }}" class="am-btn am-btn-primary mt-3">
                        + {{ __('Créer une note') }}
                    </a>
                </div>
            @else
                <div class="am-tablewrap">
                    <table class="am-table" id="notesTable" data-sort-dir="asc">
                        <thead>
                            <tr>
                                <th class="am-sort" onclick="sortTable(0)">
                                    {{ __('Date') }} <span class="am-sort-icon">⇅</span>
                                </th>
                                <th class="am-sort" onclick="sortTable(1)">
                                    {{ __('Aperçu') }} <span class="am-sort-icon">⇅</span>
                                </th>
                                <th class="am-th-actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($sessionNotes as $note)
                                @php
                                    $date = optional($note->created_at)->format('d/m/Y');
                                    $plain = trim(strip_tags($note->note ?? ''));
                                    $preview = \Illuminate\Support\Str::limit($plain, 120);
                                    $tplTitle = $note->template?->title;
                                    $searchHay = strtolower($date.' '.$preview.' '.($tplTitle ?? ''));
                                @endphp

                                <tr class="am-row" data-search="{{ $searchHay }}">
                                    <td class="am-td-date">
                                        <div class="am-date">{{ $date }}</div>
                                        @if($tplTitle)
                                            <div class="am-chip">{{ $tplTitle }}</div>
                                        @else
                                            <div class="am-chip am-chip-muted">{{ __('Sans template') }}</div>
                                        @endif
                                    </td>

                                    <td class="am-td-preview">
                                        <a href="{{ route('session_notes.show', $note->id) }}" class="am-link">
                                            {{ $preview ?: __('(vide)') }}
                                        </a>
                                    </td>

                                    <td class="am-td-actions">
                                        <a href="{{ route('session_notes.show', $note->id) }}" class="am-btn am-btn-soft">
                                            {{ __('Voir') }}
                                        </a>
                                        <a href="{{ route('session_notes.edit', $note->id) }}" class="am-btn am-btn-soft">
                                            {{ __('Modifier') }}
                                        </a>
                                        <form action="{{ route('session_notes.destroy', $note->id) }}" method="POST" class="am-inline"
                                              onsubmit="return confirm('Supprimer cette note ?');">
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
                        <span id="countLabel"></span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <style>
        .am-card{background:#fff;border-radius:14px;box-shadow:0 6px 20px rgba(15,23,42,.08);border:1px solid rgba(167, 184, 138,.15);overflow:hidden;}
        .am-head{display:flex;gap:16px;align-items:flex-end;justify-content:space-between;padding:18px;border-bottom:1px solid rgba(15,23,42,.08);flex-wrap:wrap;}
        .am-title{font-size:22px;font-weight:900;color:#0f172a;margin:0;}
        .am-sub{margin:6px 0 0;color:#6b7280;font-size:13px;}
        .am-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
        .am-input{width:280px;max-width:70vw;padding:10px 12px;border-radius:10px;border:1px solid rgba(107, 74, 58,.35);outline:none;background:#fff;}
        .am-input:focus{border-color:#5F7048;box-shadow:0 0 0 3px rgba(107, 74, 58,.12);}

        .am-btn{display:inline-flex;align-items:center;justify-content:center;padding:9px 12px;border-radius:10px;text-decoration:none;border:1px solid transparent;font-weight:800;font-size:13px;cursor:pointer;white-space:nowrap;}
        .am-btn-primary{background:#6B4A3A;color:#fff;}
        .am-btn-primary:hover{background:#5F7048;}
        .am-btn-soft{background:#f8fafc;color:#0f172a;border-color:rgba(15,23,42,.12);}
        .am-btn-soft:hover{border-color:rgba(107, 74, 58,.55);background:#fff;}
        .am-btn-danger{background:#dc2626;color:#fff;}
        .am-btn-danger:hover{background:#b91c1c;}
        .am-inline{display:inline;}

        .am-tablewrap{padding:12px 12px 10px;}
        .am-table{width:100%;border-collapse:separate;border-spacing:0;}
        .am-table thead th{text-align:left;padding:12px;border-bottom:1px solid rgba(15,23,42,.10);font-size:12px;color:#64748b;}
        .am-sort{cursor:pointer;user-select:none;}
        .am-sort-icon{opacity:.6;margin-left:6px;}
        .am-row td{padding:14px 12px;border-bottom:1px solid rgba(15,23,42,.06);vertical-align:top;}
        .am-row:hover{background:rgba(167, 184, 138,.05);}

        .am-td-actions{display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap;}
        .am-th-actions{text-align:right;}
        .am-link{color:#0f172a;text-decoration:none;font-weight:900;}
        .am-link:hover{color:#5F7048;text-decoration:underline;}
        .am-date{font-weight:900;color:#0f172a;}

        .am-chip{display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;background:rgba(167, 184, 138,.12);color:#0f172a;font-weight:900;font-size:12px;margin-top:6px;}
        .am-chip-muted{background:rgba(15,23,42,.06);color:#64748b;}

        .am-empty{padding:28px;text-align:center;}
        .am-empty-title{font-weight:900;font-size:18px;color:#0f172a;}
        .am-empty-sub{margin-top:6px;color:#6b7280;}

        .am-foot{padding:10px 4px 6px;color:#6b7280;font-size:12px;}

        @media (max-width:720px){
            .am-td-actions{justify-content:flex-start;}
            .am-th-actions{text-align:left;}
            .am-input{width:100%;}
        }
    </style>

    <script>
        function updateCount() {
            const table = document.getElementById('notesTable');
            const label = document.getElementById('countLabel');
            if (!table || !label) return;

            const rows = Array.from(table.querySelectorAll('tbody tr'));
            const visible = rows.filter(r => r.style.display !== 'none').length;
            label.textContent = visible + ' note(s)';
        }

        function filterTable() {
            const input = document.getElementById('search');
            const filter = (input.value || '').toLowerCase();
            const table = document.getElementById('notesTable');
            if (!table) return;

            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const hay = row.getAttribute('data-search') || row.innerText.toLowerCase();
                row.style.display = hay.includes(filter) ? '' : 'none';
            });
            updateCount();
        }

        function sortTable(colIndex) {
            const table = document.getElementById('notesTable');
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
