<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl" style="color:#647a0b;">
                    {{ __('Commentaires de la formation') }}
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    {{ $training->title }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('digital-trainings.builder', $training) }}"
                   class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    ✏️ {{ __('Retour au builder') }}
                </a>
                <a href="{{ route('digital-trainings.preview', $training) }}"
                   class="inline-flex items-center rounded-full bg-[#647a0b] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#506108]">
                    👀 {{ __('Aperçu') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="container mt-6">
        <div class="mx-auto max-w-5xl space-y-4">
            @if($comments->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center">
                    <p class="text-sm text-slate-600">{{ __('Aucun commentaire pour cette formation pour le moment.') }}</p>
                    <p class="mt-2 text-xs text-slate-500">{{ __('Activez les commentaires sur certains contenus dans le builder pour permettre à vos apprenants de vous poser leurs questions.') }}</p>
                </div>
            @else
                <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-4">
                    <div class="text-xs text-slate-500">
                        {{ trans_choice(':count commentaire|:count commentaires', $comments->total(), ['count' => $comments->total()]) }}
                    </div>
                </div>

                <div class="space-y-4">
                    @foreach($comments as $comment)
                        <article class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-2 text-[11px]">
                                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 font-semibold text-indigo-700 border border-indigo-100">
                                            💬 {{ __('Commentaire apprenant') }}
                                        </span>
                                        <span class="inline-flex items-center rounded-full bg-slate-50 px-2 py-0.5 text-slate-600 border border-slate-200">
                                            {{ $comment->module?->title ?: __('Module') }}
                                        </span>
                                        <span class="inline-flex items-center rounded-full bg-slate-50 px-2 py-0.5 text-slate-600 border border-slate-200">
                                            {{ $comment->block?->title ?: __('Contenu') }}
                                        </span>
                                    </div>

                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">
                                            {{ $comment->participant_name_snapshot ?: ($comment->participant_email_snapshot ?: __('Participant')) }}
                                        </div>
                                        @if($comment->participant_email_snapshot)
                                            <div class="text-xs text-slate-500">{{ $comment->participant_email_snapshot }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-xs text-slate-500">
                                    {{ $comment->created_at?->format('d/m/Y H:i') }}
                                </div>
                            </div>

                            <div class="mt-4 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm leading-relaxed text-slate-700 whitespace-pre-wrap">
                                {{ $comment->comment }}
                            </div>

                            @if($comment->replies->isNotEmpty())
                                <div class="mt-4 space-y-3">
                                    @foreach($comment->replies as $reply)
                                        <div class="ml-4 rounded-xl border border-[#d9f99d] bg-[#f7fee7] px-4 py-3">
                                            <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                                                <div>
                                                    <div class="inline-flex items-center rounded-full border border-[#d9f99d] bg-[#ecfccb] px-2 py-0.5 text-[11px] font-semibold text-[#4d5f11]">
                                                        {{ __('Réponse praticien') }}
                                                    </div>
                                                    <div class="mt-2 text-sm font-semibold text-slate-900">
                                                        {{ $reply->participant_name_snapshot ?: __('Votre thérapeute') }}
                                                    </div>
                                                </div>
                                                <div class="text-xs text-slate-500">
                                                    {{ $reply->created_at?->format('d/m/Y H:i') }}
                                                </div>
                                            </div>

                                            <div class="mt-3 text-sm leading-relaxed text-slate-700 whitespace-pre-wrap">
                                                {{ $reply->comment }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <form action="{{ route('digital-trainings.comments.reply.store', [$training, $comment]) }}" method="POST" class="mt-4 space-y-3 rounded-2xl border border-[#d9f99d] bg-[#fcfdf7] p-4">
                                @csrf
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">{{ __('Répondre à ce commentaire') }}</div>
                                    <div class="text-xs text-slate-500">{{ __('Votre réponse sera visible dans l’espace de formation sur cette section.') }}</div>
                                </div>

                                <textarea
                                    name="comment"
                                    rows="4"
                                    maxlength="2000"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-[#647a0b] focus:ring-[#647a0b]"
                                    placeholder="{{ __('Écrivez votre réponse ici...') }}"
                                >{{ old('comment') }}</textarea>

                                @error('comment')
                                    <div class="text-xs text-red-600">{{ $message }}</div>
                                @enderror

                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex items-center rounded-full bg-[#647a0b] px-4 py-2 text-xs font-semibold text-white hover:bg-[#506108]">
                                        {{ __('Envoyer la réponse') }}
                                    </button>
                                </div>
                            </form>
                        </article>
                    @endforeach
                </div>

                <div>
                    {{ $comments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
