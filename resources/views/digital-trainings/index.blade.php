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

            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-semibold text-slate-800">
                    {{ __('Vos formations digitales') }}
                </h1>
                <a href="{{ route('digital-trainings.create') }}"
                   class="inline-flex items-center rounded-lg bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white hover:bg-[#506108]">
                    + {{ __('Créer une formation') }}
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
                            <th class="px-4 py-3">{{ __('Tarif') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($trainings as $training)
                            <tr class="border-b last:border-0">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($training->cover_image_path)
                                            <img src="{{ asset('storage/'.$training->cover_image_path) }}"
                                                 alt=""
                                                 class="h-10 w-10 rounded-md object-cover">
                                        @else
                                            <div class="h-10 w-10 rounded-md bg-slate-100 flex items-center justify-center text-xs text-slate-400">
                                                {{ __('IMG') }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-medium text-slate-800">{{ $training->title }}</div>
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
                                <td class="px-4 py-3">
                                    @if($training->access_type === 'public')
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
                                            {{ __('Public') }}
                                        </span>
                                    @elseif($training->access_type === 'private')
                                        <span class="rounded-full bg-slate-50 px-2.5 py-0.5 text-xs font-medium text-slate-700">
                                            {{ __('Privé') }}
                                        </span>
                                    @else
                                        <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">
                                            {{ __('Abonnement') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($training->status === 'draft')
                                        <span class="rounded-full bg-slate-50 px-2.5 py-0.5 text-xs font-medium text-slate-700">
                                            {{ __('Brouillon') }}
                                        </span>
                                    @elseif($training->status === 'published')
                                        <span class="rounded-full bg-[#647a0b]/10 px-2.5 py-0.5 text-xs font-medium text-[#647a0b]">
                                            {{ __('Publié') }}
                                        </span>
                                    @else
                                        <span class="rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-medium text-rose-700">
                                            {{ __('Archivé') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($training->is_free)
                                        <span class="text-xs text-emerald-700 font-semibold">{{ __('Gratuit') }}</span>
                                    @elseif($training->formatted_price)
                                        <span>{{ $training->formatted_price }}</span>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex gap-2">
                                        <a href="{{ route('digital-trainings.builder', $training) }}"
                                           class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                            {{ __('Éditer le contenu') }}
                                        </a>
                                        <a href="{{ route('digital-trainings.edit', $training) }}"
                                           class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                            {{ __('Paramètres') }}
                                        </a>
										<a href="{{ route('digital-trainings.preview', $training) }}"
   class="rounded-lg border border-sky-200 px-3 py-1.5 text-xs font-semibold text-sky-700 hover:bg-sky-50">
    {{ __('Prévisualiser') }}
</a>

                                        <form action="{{ route('digital-trainings.destroy', $training) }}"
                                              method="POST"
                                              onsubmit="return confirm('Supprimer cette formation ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50">
                                                {{ __('Supprimer') }}
                                            </button>
                                        </form>
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
</x-app-layout>
