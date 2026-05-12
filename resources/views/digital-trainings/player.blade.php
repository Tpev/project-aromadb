{{-- resources/views/digital-trainings/player.blade.php --}}
@php
    $author      = optional($training->user);
    $authorName  = $author->name ?? __('Votre thérapeute');
    $authorEmail = $author->email ?? null;

    // Replace use Str::... with full namespace
    $authorInit  = \Illuminate\Support\Str::upper(
                        \Illuminate\Support\Str::substr($authorName, 0, 1)
                   );

    $participantName  = $enrollment->participant_name ?: $enrollment->participant_email;
    $participantEmail = $enrollment->participant_email;

    $progress = (int) ($enrollment->progress_percent ?? 0);
    if ($enrollment->completed_at ?? false) { $progress = 100; }
    $progress = max(0, min(100, $progress));
    $viewedBlockIds = collect($enrollment->viewed_block_ids ?? [])->map(fn ($id) => (int) $id)->filter()->values();
    $completedBlockIds = collect($enrollment->completed_block_ids ?? [])->map(fn ($id) => (int) $id)->filter()->values();

    $commentsByBlock = $commentsByBlock ?? collect();

    $modulesPayload = $modules->map(function($m) use ($commentsByBlock) {
        return [
            'id'          => $m->id,
            'title'       => $m->title,
            'description' => $m->description,
            'blocks'      => ($m->sorted_blocks ?? $m->blocks ?? collect())
                                ->map(function($b) use ($commentsByBlock) {
                                    $comments = collect($commentsByBlock[$b->id] ?? [])
                                        ->map(function ($comment) {
                                            return [
                                                'id' => $comment->id,
                                                'comment' => $comment->comment,
                                                'participant_name' => $comment->participant_first_name ?: __('Participant'),
                                                'author_role' => $comment->created_by_role,
                                                'author_role_label' => $comment->author_role_label ?: ($comment->created_by_role === 'therapist' ? __('Thérapeute') : __('Participant')),
                                                'created_at_label' => optional($comment->created_at)->timezone(config('app.timezone'))->format('d/m/Y H:i'),
                                                'replies' => collect($comment->replies ?? [])->map(function ($reply) {
                                                    return [
                                                        'id' => $reply->id,
                                                        'comment' => $reply->comment,
                                                        'participant_name' => $reply->participant_first_name ?: __('Participant'),
                                                        'author_role' => $reply->created_by_role,
                                                        'author_role_label' => $reply->author_role_label ?: ($reply->created_by_role === 'therapist' ? __('Thérapeute') : __('Participant')),
                                                        'created_at_label' => optional($reply->created_at)->timezone(config('app.timezone'))->format('d/m/Y H:i'),
                                                    ];
                                                })->values()->all(),
                                            ];
                                        })
                                        ->values()
                                        ->all();

                                    return [
                                        'id'        => $b->id,
                                        'type'      => $b->type,
                                        'title'     => $b->title,
                                        'content'   => $b->content,
                                        'file_path' => $b->file_path,
                                        'comments_enabled' => $b->commentsEnabled(),
                                        'comments' => $comments,
                                    ];
                                })
                                ->values()
                                ->all(),
        ];
    })->values()->all();
@endphp

<x-guest-layout>
    <style>
        :root { --brand: #6B4A3A; }
        * { box-sizing: border-box; }

        /* === Override Jetstream guest layout to be full-width on THIS page === */
        .min-h-screen {
            background: radial-gradient(circle at top left, #e4f0d4 0, #f3f4f6 45%, #e5e7eb 100%) !important;
        }
        .min-h-screen > div:first-child { display: none; }
        .min-h-screen > div:last-child {
            width: 100% !important;
            max-width: 100% !important;
            margin-top: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            overflow: visible !important;
        }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #0f172a;
        }

        .training-player-shell { min-height: 100vh; display: flex; flex-direction: column; }
        .training-player-header {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e5e7eb;
            padding: 14px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .training-header-left { display: flex; flex-direction: column; gap: 4px; }
        .training-title { font-size: 18px; font-weight: 650; color: var(--brand); }
        .training-subtitle { font-size: 11px; color: #6b7280; }
        .training-header-right { text-align: right; font-size: 11px; color: #4b5563; }
        .training-header-right span.email { color: #6b7280; }

        .progress-bar-outer {
            position: relative;
            width: 190px;
            height: 6px;
            border-radius: 999px;
            background: #e5e7eb;
            overflow: hidden;
        }
        .progress-bar-inner {
            position: absolute;
            inset: 0;
            border-radius: 999px;
            width: 0;
            background: linear-gradient(90deg, var(--brand), #a3e635);
            transition: width 0.35s ease;
        }

        .training-player-main {
            flex: 1;
            width: 100%;
            padding: 24px 24px 32px;
            display: flex;
            justify-content: center;
        }
        .training-inner-grid {
            width: 100%;
            max-width: 1280px;
            display: grid;
            grid-template-columns: minmax(260px, 340px) minmax(0, 1fr);
            gap: 24px;
            align-items: flex-start;
        }
        .card {
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
        }

        .sidebar {
            padding: 18px 14px 16px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            min-height: 0;
        }
        .content {
            padding: 18px 18px 16px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            min-height: calc(100vh - 150px);
            min-width: 0;
        }

        .badge-pill {
            border-radius: 999px;
            padding: 2px 10px;
            font-size: 10px;
            background: #eff6ff;
            color: #1d4ed8;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .sidebar-header { display: flex; gap: 10px; }
        .sidebar-cover {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            overflow: hidden;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: #9ca3af;
            flex-shrink: 0;
        }
        .sidebar-cover img { width: 100%; height: 100%; object-fit: cover; }

        .sidebar-header-main { flex: 1; display: flex; flex-direction: column; gap: 4px; }
        .sidebar-title { font-size: 14px; font-weight: 600; color: #111827; }
        .sidebar-meta { font-size: 11px; color: #6b7280; }

        .author-box {
            margin-top: 4px;
            padding: 8px 9px;
            border-radius: 12px;
            border: 1px dashed #e5e7eb;
            background: #f9fafb;
            display: flex;
            gap: 8px;
            align-items: flex-start;
        }
        .author-avatar {
            width: 26px;
            height: 26px;
            border-radius: 999px;
            background: #e4f0d4;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            color: var(--brand);
            flex-shrink: 0;
        }
        .author-text { font-size: 11px; color: #4b5563; }
        .author-name { font-weight: 600; font-size: 11px; color: #111827; }

        .tag-list { margin-top: 4px; display: flex; flex-wrap: wrap; gap: 4px; }
        .tag-chip { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 999px; background: #f3f4f6; font-size: 10px; color: #4b5563; }

        .sidebar-section-title { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #9ca3af; margin-top: 4px; }

        .module-list {
            margin-top: 6px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            max-height: calc(100vh - 270px);
            overflow: auto;
            padding-right: 4px;
        }
        .module-item {
            border-radius: 12px;
            padding: 8px 9px;
            cursor: pointer;
            border: 1px solid transparent;
            font-size: 13px;
            background: #ffffff;
        }
        .module-item:hover { background: #f9fafb; }
        .module-item.active {
            border-color: var(--brand);
            background: #f7fbe8;
            box-shadow: 0 0 0 1px rgba(167, 184, 138, 0.06);
        }
        .module-title-row { display: flex; justify-content: space-between; align-items: center; gap: 6px; }
        .module-title-text { font-size: 12px; font-weight: 600; color: #111827; }
        .module-index-pill { font-size: 10px; padding: 2px 6px; border-radius: 999px; background: #e5e7eb; color: #374151; white-space: nowrap; }
        .module-desc { margin-top: 2px; font-size: 11px; color: #6b7280; }
        .module-progress-text { margin-top: 4px; font-size: 10px; color: #94a3b8; }

        .block-list { margin-top: 5px; }
        .block-pill {
            font-size: 11px;
            padding: 5px 8px;
            border-radius: 999px;
            margin-right: 4px;
            margin-bottom: 4px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #f3f4f6;
            color: #4b5563;
            border: 1px solid #e5e7eb;
            cursor: pointer;
            transition: .2s ease;
        }
        .block-pill.active { background: var(--brand); color: #ffffff; border-color: var(--brand); }
        .block-pill.is-viewed:not(.active) { background: #f5f9e8; color: #4d5f11; border-color: #d9f99d; }
        .block-pill.is-completed:not(.active) { background: #ecfccb; color: #3f6212; border-color: #bef264; }
        .block-pill:hover { transform: translateY(-1px); }
        .block-status-icon {
            width: 16px;
            height: 16px;
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            line-height: 1;
            flex-shrink: 0;
            transition: .2s ease;
        }
        .block-pill.is-viewed .block-status-icon,
        .block-pill.is-completed .block-status-icon {
            color: #ffffff;
            border-color: #84cc16;
            background: linear-gradient(135deg, #84cc16, #6B4A3A);
        }
        .block-pill.active .block-status-icon {
            border-color: rgba(255,255,255,.35);
            background: rgba(255,255,255,.18);
            color: #ffffff;
        }
        .block-status-label {
            display: none;
            font-size: 10px;
            font-weight: 700;
            margin-left: 2px;
        }
        .block-pill.is-completed .block-status-label { display: inline; }

        .sidebar-footer { margin-top: auto; }

        .btn {
            border-radius: 999px;
            border: 1px solid #d1d5db;
            padding: 7px 14px;
            font-size: 13px;
            cursor: pointer;
            background: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .btn-primary { background: var(--brand); color: white; border-color: var(--brand); }
        .btn-primary:hover { background: #506108; }
        .btn:disabled { opacity: 0.5; cursor: default; }
        .btn-outline { background: transparent; }

        .content-meta-row { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
        .content-meta-left { display: flex; flex-direction: column; gap: 4px; }
        .content-meta-right { display: flex; flex-direction: column; gap: 8px; align-items: flex-end; }
        .content-path { font-size: 11px; color: #9ca3af; }
        .content-header-title { font-size: 18px; font-weight: 630; color: #111827; }
        .content-subtitle { font-size: 13px; color: #6b7280; }
        .content-state-badge {
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            color: #64748b;
        }
        .content-state-badge.is-viewed {
            border-color: #d9f99d;
            background: #f7fee7;
            color: #4d5f11;
        }
        .content-state-badge.is-completed {
            border-color: #bef264;
            background: #ecfccb;
            color: #365314;
        }

        .content-body {
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            padding: 16px;
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }
        #block-content { height: 100%; display: flex; flex-direction: column; }
        .content-body-inner { font-size: 14px; line-height: 1.6; color: #111827; flex: 1; overflow: auto; }
        .rich-text-content ul,
        .rich-text-content ol {
            margin: 0 0 1rem 1.5rem;
            padding-left: 1rem;
        }
        .rich-text-content ul { list-style: disc; }
        .rich-text-content ol { list-style: decimal; }
        .rich-text-content ol li[data-list="bullet"] { list-style-type: disc; }
        .rich-text-content ol li[data-list="ordered"] { list-style-type: decimal; }
        .rich-text-content ol li[data-list="bullet"]::marker { content: "\2022  "; }
        .rich-text-content li { margin: 0.25rem 0; }
        .rich-text-content p { margin: 0 0 1rem; }
        .rich-text-content h1,
        .rich-text-content h2,
        .rich-text-content h3 {
            margin: 0 0 0.75rem;
            font-weight: 700;
            color: #111827;
        }
        .rich-text-content h1 { font-size: 1.5rem; }
        .rich-text-content h2 { font-size: 1.25rem; }
        .rich-text-content h3 { font-size: 1.125rem; }
        .rich-text-content a { color: #6B4A3A; text-decoration: underline; }
        .rich-text-content blockquote {
            margin: 0 0 1rem;
            padding-left: 1rem;
            border-left: 3px solid #cbd5e1;
            color: #475569;
        }
        .comments-section { margin-top: 16px; border-top: 1px solid #e5e7eb; padding-top: 16px; display: flex; flex-direction: column; gap: 12px; }
        .comments-title { font-size: 13px; font-weight: 700; color: #111827; }
        .comments-help { font-size: 11px; color: #6b7280; }
        .comment-item { border: 1px solid #e5e7eb; background: #fff; border-radius: 12px; padding: 10px 12px; }
        .comment-item.is-therapist { border-color: #d9f99d; background: #f7fee7; }
        .comment-meta { font-size: 11px; color: #6b7280; margin-bottom: 4px; display: flex; justify-content: space-between; gap: 8px; }
        .comment-author { display: inline-flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .comment-role-badge { display: inline-flex; align-items: center; border-radius: 999px; padding: 2px 8px; font-size: 10px; font-weight: 700; border: 1px solid #d1d5db; background: #f8fafc; color: #475569; }
        .comment-role-badge.is-therapist { border-color: #d9f99d; background: #ecfccb; color: #4d5f11; }
        .comment-body { font-size: 13px; color: #1f2937; white-space: pre-wrap; }
        .comment-replies { margin-top: 10px; padding-top: 10px; border-top: 1px dashed #d1d5db; display: flex; flex-direction: column; gap: 10px; }
        .comment-reply { margin-left: 16px; padding-left: 12px; border-left: 3px solid #d1d5db; }
        .comment-empty { font-size: 12px; color: #6b7280; padding: 10px 12px; border: 1px dashed #d1d5db; border-radius: 12px; background: #fff; }
        .comment-form { display: flex; flex-direction: column; gap: 8px; }
        .comment-form textarea { width: 100%; min-height: 96px; border: 1px solid #d1d5db; border-radius: 12px; padding: 10px 12px; font-size: 13px; resize: vertical; }
        .comment-form-actions { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .comment-status { font-size: 11px; color: #6b7280; }
        .comment-error { font-size: 12px; color: #b91c1c; border: 1px solid #fecaca; background: #fef2f2; border-radius: 12px; padding: 8px 10px; }

        .nav-buttons { display: flex; justify-content: space-between; margin-top: 10px; gap: 10px; }
        .nav-side { font-size: 11px; color: #6b7280; }
        .completion-toolbar {
            border: 1px solid #e5e7eb;
            background: linear-gradient(180deg, #f9fafb 0%, #ffffff 100%);
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
        }
        .completion-copy { display: flex; flex-direction: column; gap: 4px; }
        .completion-title { font-size: 13px; font-weight: 700; color: #111827; }
        .completion-help { font-size: 12px; color: #6b7280; }
        .completion-toolbar.is-completed {
            border-color: #bef264;
            background: linear-gradient(180deg, #f7fee7 0%, #ffffff 100%);
        }

        /* ============= PDF SPECIFIC ============= */
        .pdf-block { display: flex; flex-direction: column; gap: 8px; height: 100%; }
        .pdf-main { flex: 1; min-height: 75vh; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; background: #ffffff; }
        .pdf-main embed { width: 100%; height: 100%; display: block; }

        .pdf-footer {
            font-size: 12px;
            color: #6b7280;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            justify-content: space-between;
        }
        .pdf-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .pdf-action-link { font-size: 12px; color: #6B4A3A; text-decoration: underline; cursor: pointer; border: none; padding: 0; background: none; }

        .pdf-fullscreen-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.9);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .pdf-fullscreen-overlay.active { display: flex; }

        .pdf-fullscreen-inner {
            position: relative;
            width: 100%;
            height: 100%;
            max-width: 1400px;
            max-height: 100%;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .pdf-fullscreen-bar { display: flex; justify-content: space-between; align-items: center; color: #e5e7eb; font-size: 13px; }
        .pdf-fullscreen-bar button {
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            padding: 4px 10px;
            font-size: 12px;
            cursor: pointer;
            background: transparent;
            color: #f9fafb;
        }
        .pdf-fullscreen-frame-wrapper { flex: 1; border-radius: 12px; overflow: hidden; background: #000; border: 1px solid #4b5563; }
        .pdf-fullscreen-frame-wrapper embed { width: 100%; height: 100%; display: block; }

        @media (max-width: 900px) {
            .training-player-header { flex-direction: column; align-items: flex-start; gap: 8px; }
            .training-player-main { padding: 16px 10px 24px; }
            .training-inner-grid { grid-template-columns: minmax(0, 1fr); }
            .sidebar { order: 2; }
            .content { order: 1; min-height: calc(100vh - 140px); }
            .module-list { max-height: none; }
            .content-body { min-height: 50vh; }
            .pdf-main { min-height: 60vh; }
            .progress-bar-outer { width: 150px; }
            .content-meta-row,
            .completion-toolbar { flex-direction: column; align-items: flex-start; }
            .content-meta-right { align-items: flex-start; }
        }
    </style>

    <div class="training-player-shell">
        <header class="training-player-header">
            <div class="training-header-left">
                <div class="training-title">{{ $training->title }}</div>
                <div class="training-subtitle">{{ __('Accès direct à votre formation – Olithea') }}</div>
            </div>

            <div class="training-header-right">
                <div style="margin-bottom:4px;">
                    {{ $participantName }}<br>
                    <span class="email">{{ $participantEmail }}</span>
                </div>
                <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px;">
                    <span style="font-size:11px;color:#6b7280;" id="globalProgressLabel">
                        {{ __('Progrès : :p%', ['p' => $progress]) }}
                    </span>
                    <div class="progress-bar-outer">
                        <div class="progress-bar-inner" id="globalProgressBar" style="width: {{ $progress }}%;"></div>
                    </div>
                </div>
            </div>
        </header>

        <main class="training-player-main">
            <div class="training-inner-grid">
                {{-- SIDEBAR --}}
                <aside class="card sidebar">
                    <div class="sidebar-header">
                        <div class="sidebar-cover">
                            @if($training->cover_image_path)
                                <img src="{{ asset('storage/'.$training->cover_image_path) }}" alt="">
                            @else
                                <span>IMG</span>
                            @endif
                        </div>
                        <div class="sidebar-header-main">
                            <div class="sidebar-title">{{ $training->title }}</div>
                            <div class="sidebar-meta">
                                @if($training->estimated_duration_minutes)
                                    {{ __('Durée estimée :') }} <strong>{{ $training->estimated_duration_minutes }} h</strong>
                                @endif
                            </div>

                            <div class="author-box">
                                <div class="author-avatar">{{ $authorInit }}</div>
                                <div class="author-text">
                                    <div class="author-name">{{ $authorName }}</div>
                                    <div>
                                        <span style="opacity:.9;">{{ __('Créateur de cette formation.') }}</span>
                                        @if($authorEmail)
                                            <br><span style="font-size:10px;color:#9ca3af;">{{ $authorEmail }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($training->tags)
                        <div class="tag-list">
                            @foreach($training->tags as $tag)
                                <span class="tag-chip">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div>
                        <div class="sidebar-section-title">{{ __('Contenu de la formation') }}</div>

                        <div class="module-list">
                            @foreach($modules as $index => $module)
                                <div class="module-item"
                                     data-module-index="{{ $index }}"
                                     onclick="selectModule({{ $index }})">
                                    <div class="module-title-row">
                                        <div class="module-title-text">
                                            {{ $module->title ?? __('Module :num', ['num' => $index + 1]) }}
                                        </div>
                                        <div class="module-index-pill">{{ 'M'.($index + 1) }}</div>
                                    </div>

                                    @if($module->description)
                                        <div class="module-desc">{{ $module->description }}</div>
                                    @endif

                                    @php $blocks = $module->sorted_blocks ?? $module->blocks ?? collect(); @endphp
                                    @php
                                        $moduleViewedCount = $blocks->filter(fn ($block) => $viewedBlockIds->contains((int) $block->id))->count();
                                        $moduleCompletedCount = $blocks->filter(fn ($block) => $completedBlockIds->contains((int) $block->id))->count();
                                    @endphp
                                    @if($blocks->count())
                                        <div class="module-progress-text" data-module-progress="{{ $index }}">
                                            {{ __(':completed/:total terminés · :viewed vus', [
                                                'completed' => $moduleCompletedCount,
                                                'total' => $blocks->count(),
                                                'viewed' => $moduleViewedCount,
                                            ]) }}
                                        </div>
                                    @endif
                                    @if($blocks->count())
                                        <div class="block-list">
                                            @foreach($blocks as $bIndex => $block)
                                                @php
                                                    $icon = '📝';
                                                    if ($block->type === 'pdf') $icon = '📄';
                                                    elseif ($block->type === 'video_url') $icon = '🎬';
                                                    elseif ($block->type === 'audio') $icon = '🎧';
                                                    $isViewed = $viewedBlockIds->contains((int) $block->id);
                                                    $isCompleted = $completedBlockIds->contains((int) $block->id);
                                                @endphp
                                                <span class="block-pill{{ $isViewed ? ' is-viewed' : '' }}{{ $isCompleted ? ' is-completed' : '' }}"
                                                      data-module-index="{{ $index }}"
                                                      data-block-index="{{ $bIndex }}"
                                                      data-block-id="{{ $block->id }}"
                                                      onclick="event.stopPropagation(); selectBlock({{ $index }}, {{ $bIndex }})">
                                                    <span class="block-status-icon" data-block-status-icon>✓</span>
                                                    <span>{{ $icon }}</span>
                                                    <span>{{ $block->title ?: __('Contenu :num', ['num' => $bIndex + 1]) }}</span>
                                                    <span class="block-status-label">Terminé</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="sidebar-footer">
                        <form action="{{ route('digital-trainings.access.complete', $enrollment->access_token) }}"
                              method="POST" style="margin-top:10px;">
                            @csrf
                            <button type="submit" class="btn btn-primary" style="width:100%;">
                                ✅ {{ __('Marquer la formation comme terminée') }}
                            </button>
                        </form>
                    </div>
                </aside>

                {{-- MAIN CONTENT --}}
                <section class="card content">
                    <div class="content-meta-row">
                        <div class="content-meta-left">
                            <div class="content-path" id="content-path"></div>
                            <div class="content-header-title" id="block-title"></div>
                            <div class="content-subtitle" id="block-subtitle"></div>
                        </div>
                        <div class="content-meta-right">
                            <span class="badge-pill">
                                <span>👣</span>
                                <span id="stepIndicator">0/0</span>
                            </span>
                            <span class="content-state-badge" id="currentBlockStateBadge">À découvrir</span>
                        </div>
                    </div>

                    <div class="content-body">
                        <div id="block-content">
                            <div class="content-body-inner"></div>
                        </div>
                    </div>

                    <div class="completion-toolbar" id="completionToolbar">
                        <div class="completion-copy">
                            <div class="completion-title" id="completionTitle">Continuez à votre rythme</div>
                            <div class="completion-help" id="completionHelp">Quand vous avez fini cette partie, marquez-la comme terminée pour garder un suivi visuel simple.</div>
                        </div>
                        <button class="btn btn-primary" type="button" id="btnMarkBlockCompleted" onclick="markCurrentBlockCompleted()">
                            ✓ Marquer cette partie comme terminée
                        </button>
                    </div>

                    <div class="nav-buttons">
                        <button class="btn btn-outline" id="btnPrev" onclick="goPrev()">← {{ __('Précédent') }}</button>
                        <button class="btn btn-outline" id="btnNext" onclick="goNext()" style="margin-left:auto;">{{ __('Suivant') }} →</button>
                    </div>
                </section>
            </div>
        </main>
    </div>

    {{-- Fullscreen PDF overlay (Option C) --}}
    <div id="pdfFullscreenOverlay" class="pdf-fullscreen-overlay" onclick="handlePdfOverlayClick(event)">
        <div class="pdf-fullscreen-inner">
            <div class="pdf-fullscreen-bar">
                <div>{{ __('Affichage du document en plein écran') }}</div>
                <button type="button" onclick="closePdfFullscreen()">✕ {{ __('Fermer') }}</button>
            </div>
            <div class="pdf-fullscreen-frame-wrapper">
                <embed id="pdfFullscreenFrame" src="about:blank" type="application/pdf">
            </div>
        </div>
    </div>

    <script>
        const modules = @json($modulesPayload);
        const commentStoreUrlTemplate = @json(route('digital-trainings.access.comments.store', ['token' => $enrollment->access_token, 'block' => '__BLOCK__']));
        const markViewedUrlTemplate = @json(route('digital-trainings.access.blocks.viewed', ['token' => $enrollment->access_token, 'block' => '__BLOCK__']));
        const markCompletedUrlTemplate = @json(route('digital-trainings.access.blocks.complete', ['token' => $enrollment->access_token, 'block' => '__BLOCK__']));
        const downloadBlockUrlTemplate = @json(route('digital-trainings.access.blocks.download', ['token' => $enrollment->access_token, 'block' => '__BLOCK__']));
        const csrfToken = @json(csrf_token());
        const selectedBlockId = @json($selectedBlockId ?? 0);
        const initialViewedBlockIds = @json($viewedBlockIds->values()->all());
        const initialCompletedBlockIds = @json($completedBlockIds->values()->all());
        const viewedBlocks = new Set(initialViewedBlockIds.map(String));
        const completedBlocks = new Set(initialCompletedBlockIds.map(String));

        let currentModuleIndex = 0;
        let currentBlockIndex  = 0;

        function escapeHtml(str) {
            return (str || '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function getVideoEmbedUrl(url) {
            if (!url) return '';

            // YouTube watch?v= / youtu.be
            const ytWatch = url.match(/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/);
            const ytShort = url.match(/youtu\.be\/([a-zA-Z0-9_-]+)/);
            if (ytWatch && ytWatch[1]) return `https://www.youtube.com/embed/${ytWatch[1]}`;
            if (ytShort && ytShort[1]) return `https://www.youtube.com/embed/${ytShort[1]}`;

            // Vimeo
            const vimeo = url.match(/vimeo\.com\/(\d+)/);
            if (vimeo && vimeo[1]) return `https://player.vimeo.com/video/${vimeo[1]}`;

            // Fallback
            return url;
        }

        function getCommentStoreUrl(blockId) {
            return commentStoreUrlTemplate.replace('__BLOCK__', String(blockId));
        }

        function getMarkViewedUrl(blockId) {
            return markViewedUrlTemplate.replace('__BLOCK__', String(blockId));
        }

        function getMarkCompletedUrl(blockId) {
            return markCompletedUrlTemplate.replace('__BLOCK__', String(blockId));
        }

        function getDownloadBlockUrl(blockId) {
            return downloadBlockUrlTemplate.replace('__BLOCK__', String(blockId));
        }

        function getBlockState(blockId) {
            const key = String(blockId);
            if (completedBlocks.has(key)) return 'completed';
            if (viewedBlocks.has(key)) return 'viewed';
            return 'unseen';
        }

        function applyProgressPayload(payload) {
            const progress = Number(payload.progress_percent || 0);
            const bar = document.getElementById('globalProgressBar');
            const label = document.getElementById('globalProgressLabel');

            if (Array.isArray(payload.viewed_block_ids)) {
                viewedBlocks.clear();
                payload.viewed_block_ids.forEach((id) => viewedBlocks.add(String(id)));
            }

            if (Array.isArray(payload.completed_block_ids)) {
                completedBlocks.clear();
                payload.completed_block_ids.forEach((id) => completedBlocks.add(String(id)));
            }

            if (bar) {
                bar.style.width = `${progress}%`;
            }

            if (label) {
                label.textContent = `Progrès : ${progress}%`;
            }

            updateActiveUI();
            updateCurrentBlockCompletionUI();
        }

        async function markBlockViewed(block) {
            if (!block || !block.id) return;

            const blockKey = String(block.id);
            if (viewedBlocks.has(blockKey)) {
                return;
            }

            viewedBlocks.add(blockKey);

            try {
                const response = await fetch(getMarkViewedUrl(block.id), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({}),
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(payload.message || 'Impossible de mettre à jour la progression.');
                }
                applyProgressPayload(payload);
            } catch (error) {
                viewedBlocks.delete(blockKey);
            }
        }

        async function markCurrentBlockCompleted() {
            const module = modules[currentModuleIndex];
            const block = module?.blocks?.[currentBlockIndex];

            if (!block || !block.id) {
                return;
            }

            const button = document.getElementById('btnMarkBlockCompleted');
            if (button) {
                button.disabled = true;
            }

            try {
                const response = await fetch(getMarkCompletedUrl(block.id), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({}),
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(payload.message || 'Impossible de marquer cette partie comme terminée.');
                }

                applyProgressPayload(payload);
            } finally {
                if (button && getBlockState(block.id) !== 'completed') {
                    button.disabled = false;
                }
            }
        }

        function renderCommentsSection(block) {
            if (!block.comments_enabled) {
                return '';
            }

            const comments = Array.isArray(block.comments) ? block.comments : [];
            const renderComment = (comment, isReply = false) => {
                const replies = Array.isArray(comment.replies) ? comment.replies : [];
                const roleLabel = comment.author_role_label || (comment.author_role === 'therapist' ? 'Thérapeute' : 'Participant');
                const wrapperClass = isReply ? 'comment-reply' : `comment-item${comment.author_role === 'therapist' ? ' is-therapist' : ''}`;
                const repliesHtml = replies.length
                    ? `<div class="comment-replies">${replies.map((reply) => renderComment(reply, true)).join('')}</div>`
                    : '';

                return `
                    <div class="${wrapperClass}">
                        <div class="comment-meta">
                            <span class="comment-author">
                                <span>${escapeHtml(comment.participant_name || 'Participant')}</span>
                                <span class="comment-role-badge${comment.author_role === 'therapist' ? ' is-therapist' : ''}">${escapeHtml(roleLabel)}</span>
                            </span>
                            <span>${escapeHtml(comment.created_at_label || '')}</span>
                        </div>
                        <div class="comment-body">${escapeHtml(comment.comment || '')}</div>
                        ${repliesHtml}
                    </div>
                `;
            };

            const commentsHtml = comments.length
                ? comments.map((comment) => renderComment(comment)).join('')
                : `<div class="comment-empty">Aucun commentaire pour le moment sur cette section.</div>`;

            return `
                <div class="comments-section">
                    <div>
                        <div class="comments-title">Questions et commentaires sur cette section</div>
                        <div class="comments-help">Votre thérapeute sera notifié dans l’application lorsque vous laissez un commentaire.</div>
                    </div>
                    <div>${commentsHtml}</div>
                    <form class="comment-form" data-training-comment-form data-block-id="${block.id}">
                        <textarea name="comment" maxlength="2000" placeholder="Écrivez votre commentaire, question ou retour sur ce contenu..."></textarea>
                        <div class="comment-error" data-comment-error hidden></div>
                        <div class="comment-form-actions">
                            <span class="comment-status" data-comment-status></span>
                            <button type="submit" class="btn btn-primary" data-comment-submit>Envoyer mon commentaire</button>
                        </div>
                    </form>
                </div>
            `;
        }

        function bindCommentForm(block) {
            const form = document.querySelector('[data-training-comment-form]');
            if (!form) return;

            const textarea = form.querySelector('textarea[name="comment"]');
            const errorBox = form.querySelector('[data-comment-error]');
            const statusEl = form.querySelector('[data-comment-status]');
            const submitBtn = form.querySelector('[data-comment-submit]');

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const comment = (textarea?.value || '').trim();
                if (!comment) {
                    if (errorBox) {
                        errorBox.hidden = false;
                        errorBox.textContent = 'Merci de saisir un commentaire avant l’envoi.';
                    }
                    return;
                }

                if (errorBox) {
                    errorBox.hidden = true;
                    errorBox.textContent = '';
                }
                if (statusEl) statusEl.textContent = 'Envoi en cours...';
                if (submitBtn) submitBtn.disabled = true;

                try {
                    const response = await fetch(getCommentStoreUrl(block.id), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ comment }),
                    });

                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        throw new Error(payload.message || 'Impossible d’envoyer le commentaire.');
                    }

                    block.comments = Array.isArray(block.comments) ? block.comments : [];
                    block.comments.push({ ...payload.comment, replies: [] });
                    if (textarea) textarea.value = '';
                    if (statusEl) statusEl.textContent = 'Commentaire envoyé.';
                    renderCurrentBlock();
                } catch (error) {
                    if (errorBox) {
                        errorBox.hidden = false;
                        errorBox.textContent = error.message || 'Une erreur est survenue pendant l’envoi.';
                    }
                    if (statusEl) statusEl.textContent = '';
                } finally {
                    if (submitBtn) submitBtn.disabled = false;
                }
            });
        }

        function selectModule(mIndex) {
            currentModuleIndex = mIndex;
            currentBlockIndex  = 0;
            updateActiveUI();
            renderCurrentBlock();
        }

        function selectBlock(mIndex, bIndex) {
            currentModuleIndex = mIndex;
            currentBlockIndex  = bIndex;
            updateActiveUI();
            renderCurrentBlock();
        }

        function flattenBlocks() {
            const flat = [];
            modules.forEach((m, mi) => {
                (m.blocks || []).forEach((b, bi) => {
                    flat.push({ moduleIndex: mi, blockIndex: bi });
                });
            });
            return flat;
        }

        function findCurrentFlatIndex(flat) {
            for (let i = 0; i < flat.length; i++) {
                if (flat[i].moduleIndex === currentModuleIndex && flat[i].blockIndex === currentBlockIndex) return i;
            }
            return null;
        }

        function goPrev() {
            const flat = flattenBlocks();
            const idx = findCurrentFlatIndex(flat);
            if (idx === null || idx <= 0) return;
            const prev = flat[idx - 1];
            currentModuleIndex = prev.moduleIndex;
            currentBlockIndex  = prev.blockIndex;
            updateActiveUI();
            renderCurrentBlock();
        }

        function goNext() {
            const flat = flattenBlocks();
            const idx = findCurrentFlatIndex(flat);
            if (idx === null || idx >= flat.length - 1) return;
            const next = flat[idx + 1];
            currentModuleIndex = next.moduleIndex;
            currentBlockIndex  = next.blockIndex;
            updateActiveUI();
            renderCurrentBlock();
        }

        function renderCurrentBlock() {
            const metaTitle   = document.getElementById('block-title');
            const metaSub     = document.getElementById('block-subtitle');
            const metaPath    = document.getElementById('content-path');
            const contentWrap = document.querySelector('#block-content .content-body-inner');
            const stepInd     = document.getElementById('stepIndicator');

            const module = modules[currentModuleIndex];
            const flat   = flattenBlocks();

            if (!module || !module.blocks || !module.blocks.length) {
                metaTitle.textContent = 'Aucun contenu';
                metaSub.textContent   = '';
                metaPath.textContent  = '';
                stepInd.textContent   = '0/0';
                contentWrap.innerHTML = '<p style="font-size:13px;color:#6b7280;">Ce module ne contient aucun contenu pour le moment.</p>';
                updateNavButtons();
                return;
            }

            const block = module.blocks[currentBlockIndex];

            const moduleLabel = module.title ? module.title : ('Module ' + (currentModuleIndex + 1));
            const blockLabel  = block.title ? block.title : ('Contenu ' + (currentBlockIndex + 1));

            metaTitle.textContent = blockLabel;
            metaSub.textContent   = moduleLabel;

            const currentFlatIndex = findCurrentFlatIndex(flat);
            const totalSteps       = flat.length || 0;
            const currentStep      = currentFlatIndex !== null ? currentFlatIndex + 1 : 1;

            metaPath.textContent = 'Module ' + (currentModuleIndex + 1) + ' · Leçon ' + (currentBlockIndex + 1);
            stepInd.textContent  = currentStep + '/' + totalSteps;

            let html = '';

            if (block.type === 'text') {
                html = `<div class="rich-text-content">${block.content || ''}</div>`;
            } else if (block.type === 'video_url') {
                // Uploaded file has priority
                if (block.file_path) {
                    const src = `{{ rtrim(config('app.url'), '/') }}/storage/` + block.file_path;
                    const downloadUrl = getDownloadBlockUrl(block.id);
                    const url = block.content || '';

                    html = `
                        <div style="display:flex;flex-direction:column;gap:10px;">
                            <div style="position:relative;width:100%;border-radius:12px;overflow:hidden;background:#0f172a;border:1px solid #e5e7eb;">
                                <video controls preload="metadata" style="width:100%;height:auto;display:block;">
                                    <source src="${src}">
                                    {{ __('Votre navigateur ne supporte pas la lecture vidéo.') }}
                                </video>
                            </div>

                            <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
                                <a href="${downloadUrl}" download style="display:inline-flex;align-items:center;justify-content:center;border-radius:999px;border:1px solid #6B4A3A;background:#6B4A3A;color:#ffffff;padding:8px 14px;font-size:12px;font-weight:600;text-decoration:none;">
                                    {{ __('Télécharger la vidéo') }}
                                </a>
                                <a href="${src}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;justify-content:center;border-radius:999px;border:1px solid #d1d5db;background:#ffffff;color:#374151;padding:8px 14px;font-size:12px;font-weight:600;text-decoration:none;">
                                    {{ __('Ouvrir dans un nouvel onglet') }}
                                </a>
                            </div>

                            <div style="font-size:12px;color:#6b7280;">
                                {{ __('Si le bouton de téléchargement du lecteur n’apparaît pas sur votre appareil, utilisez le bouton Télécharger la vidéo ci-dessus.') }}
                            </div>

                            ${url ? `
                                <div style="border:1px solid #e5e7eb;background:#ffffff;border-radius:12px;padding:10px;">
                                    <div style="font-size:11px;font-weight:600;color:#374151;margin-bottom:4px;">
                                        {{ __('URL (optionnelle) :') }}
                                    </div>
                                    <div style="font-size:11px;color:#6b7280;word-break:break-all;">
                                        ${escapeHtml(url)}
                                    </div>
                                </div>
                            ` : ``}
                        </div>
                    `;
                } else {
                    // Fallback to URL embed
                    const raw = block.content || '';
                    const embed = getVideoEmbedUrl(raw);

                    if (!raw) {
                        html = `<p style="font-size:13px;color:#6b7280;">Aucune vidéo n’est renseignée pour ce contenu.</p>`;
                    } else {
                        html = `
                            <div style="display:flex;flex-direction:column;gap:8px;">
                                <div style="position:relative;width:100%;padding-bottom:56.25%;border-radius:12px;overflow:hidden;background:#0f172a;">
                                    <iframe src="${embed}" frameborder="0" allowfullscreen
                                            style="position:absolute;top:0;left:0;width:100%;height:100%;"></iframe>
                                </div>
                                <div style="font-size:12px;color:#6b7280;">
                                    Si la vidéo ne s’affiche pas correctement, vous pouvez
                                    <a href="${raw}" target="_blank" style="color:#6B4A3A;text-decoration:underline;">ouvrir la vidéo dans un nouvel onglet</a>.
                                </div>
                            </div>`;
                    }
                }
            } else if (block.type === 'audio') {
                if (block.file_path) {
                    const src = `{{ rtrim(config('app.url'), '/') }}/storage/` + block.file_path;
                    const url = block.content || '';

                    html = `
                        <div style="display:flex;flex-direction:column;gap:10px;">
                            <div style="border:1px solid #e5e7eb;background:#ffffff;border-radius:12px;padding:16px;">
                                <audio controls preload="metadata" style="width:100%;display:block;">
                                    <source src="${src}">
                                    {{ __('Votre navigateur ne supporte pas la lecture audio.') }}
                                </audio>
                            </div>

                            <div style="font-size:12px;color:#6b7280;">
                                <a href="${src}" target="_blank" style="color:#6B4A3A;text-decoration:underline;">
                                    {{ __('Ouvrir l’audio dans un nouvel onglet') }}
                                </a>
                            </div>

                            ${url ? `
                                <div style="border:1px solid #e5e7eb;background:#ffffff;border-radius:12px;padding:10px;">
                                    <div style="font-size:11px;font-weight:600;color:#374151;margin-bottom:4px;">
                                        {{ __('URL (optionnelle) :') }}
                                    </div>
                                    <div style="font-size:11px;color:#6b7280;word-break:break-all;">
                                        ${escapeHtml(url)}
                                    </div>
                                </div>
                            ` : ``}
                        </div>
                    `;
                } else {
                    const raw = block.content || '';

                    if (!raw) {
                        html = `<p style="font-size:13px;color:#6b7280;">Aucun audio n’est renseigné pour ce contenu.</p>`;
                    } else {
                        html = `
                            <div style="display:flex;flex-direction:column;gap:10px;">
                                <div style="border:1px solid #e5e7eb;background:#ffffff;border-radius:12px;padding:16px;">
                                    <audio controls preload="metadata" src="${escapeHtml(raw)}" style="width:100%;display:block;">
                                        {{ __('Votre navigateur ne supporte pas la lecture audio.') }}
                                    </audio>
                                </div>
                                <div style="font-size:12px;color:#6b7280;">
                                    Si l’audio ne se lance pas correctement, vous pouvez
                                    <a href="${raw}" target="_blank" style="color:#6B4A3A;text-decoration:underline;">ouvrir le fichier dans un nouvel onglet</a>.
                                </div>
                            </div>`;
                    }
                }
            } else if (block.type === 'pdf') {
                const filePath = block.file_path ? `{{ rtrim(config('app.url'), '/') }}/storage/` + block.file_path : null;
                if (filePath) {
                    html = `
                        <div class="pdf-block">
                            <div class="pdf-main">
                                <embed src="${filePath}#toolbar=1&navpanes=0" type="application/pdf">
                            </div>
                            <div class="pdf-footer">
                                <div>
                                    Si le document ne s’affiche pas correctement, vous pouvez
                                    <a href="${filePath}" target="_blank" style="color:#6B4A3A;text-decoration:underline;">télécharger le PDF</a>.
                                </div>
                                <div class="pdf-actions">
                                    <button type="button" class="pdf-action-link" onclick="openPdfFullscreen('${filePath}')">
                                        🗖 Afficher en plein écran
                                    </button>
                                </div>
                            </div>
                        </div>`;
                } else {
                    html = `<p style="font-size:13px;color:#6b7280;">Ce document PDF n’est pas disponible.</p>`;
                }
            } else {
                html = `<p style="font-size:13px;color:#6b7280;">Type de contenu non reconnu.</p>`;
            }

            html += renderCommentsSection(block);
            contentWrap.innerHTML = html;
            bindCommentForm(block);
            markBlockViewed(block);
            updateCurrentBlockCompletionUI();
            updateNavButtons();
        }

        function updateActiveUI() {
            document.querySelectorAll('.module-item').forEach((el, idx) => {
                el.classList.toggle('active', idx === currentModuleIndex);
            });
            document.querySelectorAll('.block-pill').forEach((el) => {
                const blockId = el.getAttribute('data-block-id');
                const mi = parseInt(el.getAttribute('data-module-index'), 10);
                const bi = parseInt(el.getAttribute('data-block-index'), 10);
                const state = getBlockState(blockId);

                el.classList.toggle('is-viewed', state === 'viewed' || state === 'completed');
                el.classList.toggle('is-completed', state === 'completed');
                el.classList.toggle('active', (mi === currentModuleIndex && bi === currentBlockIndex));
            });
            updateModuleProgressUI();
        }

        function updateModuleProgressUI() {
            document.querySelectorAll('[data-module-progress]').forEach((el) => {
                const moduleIndex = Number(el.getAttribute('data-module-progress'));
                const blocks = modules[moduleIndex]?.blocks || [];
                const total = blocks.length;
                const viewed = blocks.filter((block) => getBlockState(block.id) !== 'unseen').length;
                const completed = blocks.filter((block) => getBlockState(block.id) === 'completed').length;

                el.textContent = `${completed}/${total} terminés · ${viewed} vus`;
            });
        }

        function updateCurrentBlockCompletionUI() {
            const module = modules[currentModuleIndex];
            const block = module?.blocks?.[currentBlockIndex];
            const stateBadge = document.getElementById('currentBlockStateBadge');
            const toolbar = document.getElementById('completionToolbar');
            const title = document.getElementById('completionTitle');
            const help = document.getElementById('completionHelp');
            const button = document.getElementById('btnMarkBlockCompleted');

            if (!block || !stateBadge || !toolbar || !title || !help || !button) {
                return;
            }

            const state = getBlockState(block.id);

            stateBadge.classList.remove('is-viewed', 'is-completed');
            toolbar.classList.toggle('is-completed', state === 'completed');

            if (state === 'completed') {
                stateBadge.textContent = '✓ Terminé';
                stateBadge.classList.add('is-completed');
                title.textContent = 'Cette partie est terminée';
                help.textContent = 'Votre progression visuelle est à jour. Vous pouvez revenir dessus quand vous le souhaitez.';
                button.textContent = '✓ Partie terminée';
                button.disabled = true;
            } else if (state === 'viewed') {
                stateBadge.textContent = '✓ Vu';
                stateBadge.classList.add('is-viewed');
                title.textContent = 'Vous avez déjà ouvert cette partie';
                help.textContent = 'Quand vous l’avez vraiment terminée, cliquez ci-dessous pour garder un suivi clair.';
                button.textContent = '✓ Marquer cette partie comme terminée';
                button.disabled = false;
            } else {
                stateBadge.textContent = 'À découvrir';
                title.textContent = 'Continuez à votre rythme';
                help.textContent = 'Quand vous avez fini cette partie, marquez-la comme terminée pour garder un suivi visuel simple.';
                button.textContent = '✓ Marquer cette partie comme terminée';
                button.disabled = false;
            }
        }

        function updateNavButtons() {
            const flat    = flattenBlocks();
            const prevBtn = document.getElementById('btnPrev');
            const nextBtn = document.getElementById('btnNext');

            const idx = findCurrentFlatIndex(flat);
            if (idx === null || !flat.length) {
                prevBtn.disabled = true;
                nextBtn.disabled = true;
                return;
            }

            prevBtn.disabled = (idx === 0);
            nextBtn.disabled = (idx === flat.length - 1);
        }

        // ==== PDF FULLSCREEN (Option C) ====
        function openPdfFullscreen(path) {
            const overlay = document.getElementById('pdfFullscreenOverlay');
            const frame   = document.getElementById('pdfFullscreenFrame');

            frame.setAttribute('src', path + '#toolbar=1&navpanes=0');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closePdfFullscreen() {
            const overlay = document.getElementById('pdfFullscreenOverlay');
            const frame   = document.getElementById('pdfFullscreenFrame');

            overlay.classList.remove('active');
            frame.setAttribute('src', 'about:blank');
            document.body.style.overflow = '';
        }

        function handlePdfOverlayClick(e) {
            if (e.target.id === 'pdfFullscreenOverlay') closePdfFullscreen();
        }

        (function init() {
            let found = false;
            modules.forEach((m, mi) => {
                if (!found && m.blocks && m.blocks.length) {
                    currentModuleIndex = mi;
                    currentBlockIndex  = 0;
                    found = true;
                }
            });
            if (!found) { currentModuleIndex = 0; currentBlockIndex = 0; }

            if (selectedBlockId) {
                modules.forEach((module, moduleIndex) => {
                    (module.blocks || []).forEach((block, blockIndex) => {
                        if (Number(block.id) === Number(selectedBlockId)) {
                            currentModuleIndex = moduleIndex;
                            currentBlockIndex = blockIndex;
                        }
                    });
                });
            }
            updateActiveUI();
            renderCurrentBlock();
        })();
    </script>
</x-guest-layout>
