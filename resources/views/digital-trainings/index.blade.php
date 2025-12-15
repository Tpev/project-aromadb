<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Formations & Contenu digital') }}
        </h2>
    </x-slot>

    <div class="container mt-6">
        <div class="mx-auto max-w-6xl bg-white shadow-sm rounded-xl p-6">
            @if(session('success'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-800">
                        {{ __('Vos formations digitales') }}
                    </h1>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ __('Créez, gérez et partagez vos formations en ligne avec vos clients.') }}
                    </p>
                </div>
                <a href="{{ route('digital-trainings.create') }}"
                   class="inline-flex items-center justify-center rounded-full bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#506108]">
                    <span class="mr-1 text-base">+</span>
                    <span>{{ __('Créer une formation') }}</span>
                </a>
            </div>

            @if($trainings->isEmpty())
                <p class="text-sm text-slate-500">
                    {{ __('Vous n’avez pas encore créé de formation digitale.') }}
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left text-slate-700">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3">{{ __('Titre') }}</th>
                            <th class="px-4 py-3">{{ __('Accès') }}</th>
                            <th class="px-4 py-3">{{ __('Statut') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        @foreach($trainings as $training)
                            <tr class="hover:bg-slate-50/60">
                                {{-- Titre + tags --}}
                                <td class="px-4 py-3 align-top">
                                    <div class="flex items-center gap-3">
                                        @if($training->cover_image_path)
                                            <img src="{{ asset('storage/'.$training->cover_image_path) }}"
                                                 alt=""
                                                 class="h-10 w-10 rounded-md object-cover flex-shrink-0">
                                        @else
                                            <div class="h-10 w-10 rounded-md bg-slate-100 flex items-center justify-center text-[10px] text-slate-400 flex-shrink-0">
                                                {{ __('IMG') }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="font-medium text-slate-800 truncate">
                                                {{ $training->title }}
                                            </div>
                                            @if($training->tags)
                                                <div class="mt-1 flex flex-wrap gap-1">
                                                    @foreach($training->tags as $tag)
                                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600">
                                                            {{ $tag }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Accès --}}
                                <td class="px-4 py-3 align-top">
                                    @if($training->access_type === 'public')
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
                                            {{ __('Public') }}
                                        </span>
                                    @elseif($training->access_type === 'private')
                                        <span class="inline-flex items-center rounded-full bg-slate-50 px-2.5 py-0.5 text-xs font-medium text-slate-700">
                                            {{ __('Privé') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">
                                            {{ __('Abonnement') }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Statut --}}
                                <td class="px-4 py-3 align-top">
                                    @if($training->status === 'draft')
                                        <span class="inline-flex items-center rounded-full bg-slate-50 px-2.5 py-0.5 text-xs font-medium text-slate-700">
                                            {{ __('Brouillon') }}
                                        </span>
                                    @elseif($training->status === 'published')
                                        <span class="inline-flex items-center rounded-full bg-[#647a0b]/10 px-2.5 py-0.5 text-xs font-medium text-[#647a0b]">
                                            {{ __('Publié') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-medium text-rose-700">
                                            {{ __('Archivé') }}
                                        </span>
                                    @endif
                                </td>



                                {{-- Actions --}}
                                <td class="px-4 py-3 align-top text-right">
                                    <div class="inline-flex flex-col items-end gap-1">

                                        {{-- Ligne 1 : actions principales --}}
                                        <div class="flex flex-wrap justify-end gap-2">
                                            {{-- Builder / contenu --}}
                                            <a href="{{ route('digital-trainings.builder', $training) }}"
                                               class="inline-flex items-center gap-1 rounded-full bg-[#647a0b]/10 px-3 py-1.5 text-xs font-semibold text-[#647a0b] hover:bg-[#647a0b]/20">
                                                🧱 <span class="hidden sm:inline">{{ __('Contenu') }}</span>
                                                <span class="sm:hidden">{{ __('Éditer') }}</span>
                                            </a>

                                            {{-- Inviter / participants --}}
                                            <a href="{{ route('digital-trainings.enrollments.index', $training) }}"
                                               class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                                👥 <span class="hidden sm:inline">{{ __('Participants') }}</span>
                                            </a>

                                            {{-- Preview --}}
                                            <a href="{{ route('digital-trainings.preview', $training) }}"
                                               target="_blank"
                                               class="inline-flex items-center gap-1 rounded-full border border-sky-200 px-3 py-1.5 text-xs font-semibold text-sky-700 hover:bg-sky-50">
                                                👁 <span class="hidden sm:inline">{{ __('Prévisualiser') }}</span>
                                            </a>
                                        </div>

                                        {{-- Ligne 2 : actions secondaires --}}
                                        <div class="flex flex-wrap justify-end gap-2">
                                            {{-- Lien public (seulement si publié) --}}
                                            @if($training->status === 'published')
                                                <button type="button"
                                                        onclick="copyTrainingPublicLink('{{ route('digital-trainings.public.show', $training) }}', '{{ addslashes($training->title) }}')"
                                                        class="inline-flex items-center gap-1 rounded-full border border-emerald-200 px-2.5 py-1 text-[11px] font-medium text-emerald-700 hover:bg-emerald-50">
                                                    🔗 <span class="hidden sm:inline">{{ __('Lien public') }}</span>
                                                </button>
                                            @endif

                                            {{-- Paramètres --}}
                                            <a href="{{ route('digital-trainings.edit', $training) }}"
                                               class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2.5 py-1 text-[11px] font-medium text-slate-600 hover:bg-slate-50">
                                                ⚙️ <span class="hidden sm:inline">{{ __('Paramètres') }}</span>
                                            </a>

                                            {{-- Supprimer --}}
                                            <form action="{{ route('digital-trainings.destroy', $training) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Supprimer cette formation ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 rounded-full border border-rose-200 px-2.5 py-1 text-[11px] font-medium text-rose-700 hover:bg-rose-50">
                                                    🗑 <span class="hidden sm:inline">{{ __('Supprimer') }}</span>
                                                </button>
                                            </form>
                                        </div>

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Toast for "link copied" --}}
    <div id="trainingPublicLinkToast"
         class="fixed inset-x-0 bottom-4 z-40 flex justify-center pointer-events-none hidden">
        <div class="pointer-events-auto rounded-full bg-slate-900/90 px-4 py-2 text-xs text-slate-50 shadow-lg flex items-center gap-2">
            <span>🔗</span>
            <span id="trainingPublicLinkToastText">
                {{ __('Lien copié') }}
            </span>
        </div>
    </div>

    <script>
        function copyTrainingPublicLink(url, title) {
            const toast      = document.getElementById('trainingPublicLinkToast');
            const toastText  = document.getElementById('trainingPublicLinkToastText');
            const message    = "{{ __('Lien public copié pour') }} " + '"' + title + '"';

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url)
                    .then(function () {
                        if (toast && toastText) {
                            toastText.textContent = message;
                            toast.classList.remove('hidden');
                            setTimeout(function () {
                                toast.classList.add('hidden');
                            }, 2500);
                        }
                    })
                    .catch(function () {
                        window.prompt("{{ __('Copiez ce lien :') }}", url);
                    });
            } else {
                // Fallback anciens navigateurs
                window.prompt("{{ __('Copiez ce lien :') }}", url);
            }
        }
    </script>
</x-app-layout>
