{{-- resources/views/session_notes/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#647a0b;">
            {{ __('Note de séance') }}
        </h2>
    </x-slot>

    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">

    <div class="max-w-5xl mx-auto py-8 px-4">
        @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-100 border border-green-200 text-green-800 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="am-card">
            <div class="am-head">
                <div>
                    <h1 class="am-title">
                        {{ __('Note du') }} {{ optional($sessionNote->created_at)->format('d/m/Y') }}
                    </h1>
                    <p class="am-sub">
                        @if($sessionNote->template?->title)
                            {{ __('Template :') }} <span class="am-pill">{{ $sessionNote->template->title }}</span>
                        @else
                            <span class="am-pill am-pill-muted">{{ __('Sans template') }}</span>
                        @endif
                    </p>
                </div>

                <div class="am-actions">
                    <a href="{{ route('session_notes.index', $sessionNote->client_profile_id) }}" class="am-btn am-btn-soft">
                        {{ __('Retour') }}
                    </a>

                    <a href="{{ route('session_notes.edit', $sessionNote->id) }}" class="am-btn am-btn-primary">
                        {{ __('Modifier') }}
                    </a>

                    <form action="{{ route('session_notes.destroy', $sessionNote->id) }}" method="POST" class="am-inline"
                          onsubmit="return confirm('Supprimer cette note ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="am-btn am-btn-danger">
                            {{ __('Supprimer') }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="am-body">
                @php
                    $raw = $sessionNote->note ?? '';
                    $hasContent = !empty(trim(strip_tags($raw)));
                    // if stripping tags doesn't change it, it's plain text
                    $textOnly = trim(strip_tags($raw)) === trim($raw);
                @endphp

                @if(!$hasContent)
                    <div class="am-empty">
                        <div class="am-empty-title">{{ __('Note vide') }}</div>
                        <div class="am-empty-sub">{{ __('Cette note ne contient pas de contenu.') }}</div>
                    </div>
                @else
                    <div class="ql-snow am-quill-view">
                        <div class="ql-editor">
                            @if($textOnly)
                                {!! nl2br(e($raw)) !!}
                            @else
                                {!! $raw !!}
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .am-card{background:#fff;border-radius:14px;box-shadow:0 6px 20px rgba(15,23,42,.08);border:1px solid rgba(100,122,11,.15);overflow:hidden;}
        .am-head{display:flex;gap:16px;align-items:flex-end;justify-content:space-between;padding:18px;border-bottom:1px solid rgba(15,23,42,.08);flex-wrap:wrap;}
        .am-title{font-size:22px;font-weight:900;color:#0f172a;margin:0;}
        .am-sub{margin:6px 0 0;color:#6b7280;font-size:13px;}
        .am-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
        .am-body{padding:18px;}

        .am-pill{display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;background:rgba(100,122,11,.12);color:#0f172a;font-weight:900;}
        .am-pill-muted{background:rgba(15,23,42,.06);color:#64748b;}

        .am-btn{display:inline-flex;align-items:center;justify-content:center;padding:9px 12px;border-radius:10px;text-decoration:none;border:1px solid transparent;font-weight:800;font-size:13px;cursor:pointer;white-space:nowrap;}
        .am-btn-primary{background:#647a0b;color:#fff;}
        .am-btn-primary:hover{background:#854f38;}
        .am-btn-soft{background:#fff;color:#0f172a;border-color:rgba(15,23,42,.12);border-width:1px;border-style:solid;}
        .am-btn-soft:hover{border-color:rgba(133,79,56,.55);}
        .am-btn-danger{background:#dc2626;color:#fff;}
        .am-btn-danger:hover{background:#b91c1c;}
        .am-inline{display:inline;}

        .am-quill-view{border:1px solid rgba(15,23,42,.10);border-radius:14px;overflow:hidden;background:#fff;}
        .am-quill-view .ql-editor{font-size:16px;line-height:1.85;color:#0f172a;padding:16px;min-height:120px;}
        .am-quill-view .ql-editor img{max-width:100%;height:auto;border-radius:12px;}

        .am-empty{padding:22px;text-align:center;}
        .am-empty-title{font-weight:900;font-size:18px;color:#0f172a;}
        .am-empty-sub{margin-top:6px;color:#6b7280;}
    </style>
</x-app-layout>
