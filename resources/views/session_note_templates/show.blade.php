{{-- resources/views/session_note_templates/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#647a0b;">
            {{ __('Template de note de séance') }}
        </h2>
    </x-slot>

    {{-- Quill CSS to render Quill HTML properly (lists/indent/align/etc.) --}}
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
                    <h1 class="am-title">{{ $template->title }}</h1>
                    <p class="am-sub">
                        {{ __('Créé le') }} {{ optional($template->created_at)->format('d/m/Y') }}
                        @if($template->updated_at && $template->updated_at->ne($template->created_at))
                            · {{ __('Mis à jour le') }} {{ optional($template->updated_at)->format('d/m/Y') }}
                        @endif
                    </p>
                </div>

                <div class="am-actions">
                    <a href="{{ route('session-note-templates.index') }}" class="am-btn am-btn-soft">
                        {{ __('Retour') }}
                    </a>

                    <a href="{{ route('session-note-templates.edit', $template->id) }}" class="am-btn am-btn-primary">
                        {{ __('Modifier') }}
                    </a>

                    <form action="{{ route('session-note-templates.destroy', $template->id) }}" method="POST" class="am-inline"
                          onsubmit="return confirm('Supprimer ce template ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="am-btn am-btn-danger">
                            {{ __('Supprimer') }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="am-body">
                @if(empty(trim(strip_tags($template->content ?? ''))))
                    <div class="am-empty">
                        <div class="am-empty-title">{{ __('Ce template est vide') }}</div>
                        <div class="am-empty-sub">{{ __('Cliquez sur “Modifier” pour ajouter du contenu.') }}</div>
                    </div>
                @else
                    {{-- IMPORTANT: Wrap Quill HTML in Quill classes so formatting is rendered correctly --}}
                    <div class="ql-snow am-quill-view">
                        <div class="ql-editor">
                            {!! $template->content !!}
                        </div>
                    </div>
                @endif
            </div>
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
        .am-btn{display:inline-flex;align-items:center;justify-content:center;padding:9px 12px;border-radius:10px;text-decoration:none;border:1px solid transparent;font-weight:800;font-size:13px;cursor:pointer;white-space:nowrap;}
        .am-btn-primary{background:#647a0b;color:#fff;}
        .am-btn-primary:hover{background:#854f38;}
        .am-btn-soft{background:#f8fafc;color:#0f172a;border-color:rgba(15,23,42,.12);}
        .am-btn-soft:hover{border-color:rgba(133,79,56,.55); background:#fff;}
        .am-btn-danger{background:#dc2626;color:#fff;}
        .am-btn-danger:hover{background:#b91c1c;}
        .am-inline{display:inline;}

        .am-body{padding:18px;}

        /* Quill viewer container look */
        .am-quill-view{
            border:1px solid rgba(15,23,42,.10);
            border-radius:14px;
            overflow:hidden;
            background:#fff;
        }

        /* Make Quill output feel like your app */
        .am-quill-view .ql-editor{
            font-size:16px;
            line-height:1.85;
            color:#0f172a;
            padding:16px;
            min-height:120px;
        }

        /* Better media */
        .am-quill-view .ql-editor img{max-width:100%;height:auto;border-radius:12px;}

        .am-empty{padding:22px;text-align:center;}
        .am-empty-title{font-weight:900;font-size:18px;color:#0f172a;}
        .am-empty-sub{margin-top:6px;color:#6b7280;}
    </style>
</x-app-layout>
