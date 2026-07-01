@php
    $isSent = $newsletter->status === 'sent';
    $statusClass = $isSent
        ? 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]'
        : 'border-gray-200 bg-gray-50 text-gray-600';
    $audienceName = $newsletter->audience?->name ?: 'Tous les clients';
    $previewText = function (array $block) {
        $html = $block['html'] ?? $block['text'] ?? '';
        $html = str_replace(['<br>', '<br/>', '<br />'], ' ', $html);

        return Str::limit(trim(html_entity_decode(strip_tags($html))), 150);
    };
@endphp

<x-mobile-layout :title="$newsletter->title">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4">
            <a href="{{ route('mobile.newsletters.index') }}" class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Newsletters
            </a>

            <div class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                        <i class="fas fa-envelope-open-text text-sm"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h1 class="text-xl font-semibold leading-tight text-gray-900">
                            {{ $newsletter->title }}
                        </h1>
                        <p class="mt-1 line-clamp-2 text-sm leading-snug text-gray-600">
                            {{ $newsletter->subject ?: 'Sujet non renseigne' }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2">
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Statut</div>
                        <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $isSent ? 'Envoyee' : 'Brouillon' }}</div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Cible</div>
                        <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $targetCount }}</div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Envoyes</div>
                        <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $newsletter->recipients_count }}</div>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-1.5">
                    <span class="rounded-full border px-2 py-1 text-[11px] font-medium {{ $statusClass }}">
                        {{ $isSent ? 'Envoyee' : 'Brouillon' }}
                    </span>
                    <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                        {{ $audienceName }}
                    </span>
                    @if($newsletter->sent_at)
                        <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                            {{ $newsletter->sent_at->format('d/m/Y H:i') }}
                        </span>
                    @endif
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <a href="{{ route('mobile.newsletters.edit', $newsletter) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        <i class="fas fa-edit mr-1.5 text-[11px]"></i>
                        Modifier
                    </a>
                    <a href="{{ route('newsletters.show', $newsletter) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        <i class="fas fa-external-link-alt mr-1.5 text-[10px]"></i>
                        Apercu web
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-[#d7dfaa] bg-[#647a0b]/10 p-3 text-sm font-medium text-[#4f6108]">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="space-y-4">
            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Apercu du contenu</h2>

                @if(empty($blocks))
                    <p class="mt-3 text-sm text-gray-500">Aucun bloc dans cette newsletter.</p>
                @else
                    <div class="mt-3 space-y-2">
                        @foreach($blocks as $block)
                            @php
                                $type = $block['type'] ?? 'text';
                            @endphp

                            <article class="rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-900">
                                            @if($type === 'heading_text')
                                                {{ $block['heading'] ?? 'Titre' }}
                                            @elseif($type === 'image')
                                                Image
                                            @elseif($type === 'button')
                                                Bouton
                                            @elseif($type === 'divider')
                                                Separateur
                                            @else
                                                Texte
                                            @endif
                                        </div>
                                        <p class="mt-1 line-clamp-3 text-xs leading-snug text-gray-600">
                                            @if($type === 'image')
                                                {{ $block['url'] ?? 'URL manquante' }}
                                            @elseif($type === 'button')
                                                {{ ($block['label'] ?? 'En savoir plus') . ' - ' . ($block['url'] ?? '') }}
                                            @elseif($type === 'divider')
                                                Ligne de separation.
                                            @else
                                                {{ $previewText($block) ?: 'Texte vide' }}
                                            @endif
                                        </p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-white px-2 py-0.5 text-[10px] font-medium text-gray-500">
                                        {{ $type }}
                                    </span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Envoi</h2>
                <p class="mt-1 text-xs leading-snug text-gray-500">
                    Cible actuelle: {{ $audienceName }} avec {{ $targetCount }} destinataire{{ $targetCount > 1 ? 's' : '' }} disponible{{ $targetCount > 1 ? 's' : '' }}.
                </p>

                <form method="POST" action="{{ route('mobile.newsletters.send-test', $newsletter) }}" class="mt-3 space-y-2">
                    @csrf
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Email de test</span>
                        <input type="email"
                               name="test_email"
                               value="{{ old('test_email', auth()->user()->email) }}"
                               required
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>
                    <button type="submit"
                            class="inline-flex h-10 w-full items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        Envoyer un test
                    </button>
                </form>

                @if($isSent)
                    <div class="mt-3 rounded-lg border border-[#d7dfaa] bg-[#647a0b]/10 p-3 text-sm text-[#4f6108]">
                        Cette newsletter est deja envoyee.
                    </div>
                @else
                    <form method="POST"
                          action="{{ route('mobile.newsletters.send-now', $newsletter) }}"
                          class="mt-3"
                          onsubmit="return confirm('Envoyer cette newsletter maintenant ?');">
                        @csrf
                        <button type="submit"
                                class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-[#854f38] text-sm font-semibold text-white">
                            Envoyer maintenant
                        </button>
                    </form>
                @endif
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Details</h2>
                <div class="mt-3 space-y-2 text-sm text-gray-700">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-user mt-1 w-4 text-[11px] text-gray-400"></i>
                        <span>{{ $newsletter->from_name }} - {{ $newsletter->from_email }}</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="fas fa-inbox mt-1 w-4 text-[11px] text-gray-400"></i>
                        <span>{{ $newsletter->preheader ?: 'Pre-header non renseigne' }}</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="fas fa-chart-line mt-1 w-4 text-[11px] text-gray-400"></i>
                        <span>Quota utilise: {{ number_format($quotaUsed) }} / {{ number_format($quotaLimit) }}</span>
                    </div>
                </div>
            </section>

            <form method="POST"
                  action="{{ route('mobile.newsletters.destroy', $newsletter) }}"
                  onsubmit="return confirm('Supprimer cette newsletter ?');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-red-100 bg-red-50 text-sm font-semibold text-red-600">
                    <i class="fas fa-trash-alt mr-1.5 text-[11px]"></i>
                    Supprimer la newsletter
                </button>
            </form>
        </div>
    </div>
</x-mobile-layout>
