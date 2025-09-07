<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight">
            {{ __('Cabinets & Lieux') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="rounded bg-green-100 text-green-800 px-4 py-2">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="rounded bg-red-100 text-red-800 px-4 py-2">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600">
                    {{ __('Gérez vos adresses de cabinet (utilisées par les disponibilités et rendez-vous).') }}
                </p>
                <a href="{{ route('practice-locations.create') }}"
                   class="inline-flex items-center px-4 py-2 rounded-lg bg-[#647a0b] text-white hover:bg-[#8ea633] transition">
                    + {{ __('Nouveau cabinet') }}
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($locations as $loc)
                    <div class="bg-white shadow rounded-lg p-5 hover:shadow-xl transition">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-lg font-semibold text-gray-800">{{ $loc->label }}</h3>
                                    @if($loc->is_primary)
                                        <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-800">
                                            {{ __('Principal') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-600 mt-1">
                                    {{ $loc->full_address }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('practice-locations.edit', $loc) }}"
                                   class="px-3 py-1.5 rounded border hover:bg-gray-50">
                                    {{ __('Modifier') }}
                                </a>
                                <form action="{{ route('practice-locations.destroy', $loc) }}" method="POST"
                                      onsubmit="return confirm('Supprimer ce cabinet ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="px-3 py-1.5 rounded border border-red-300 text-red-700 hover:bg-red-50">
                                        {{ __('Supprimer') }}
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="mt-4 text-xs text-gray-500">
                            {{-- Astuce UX : rappeler les liens depuis d’autres modules si besoin --}}
                            {{ __('Astuce : Associez ce lieu dans vos disponibilités pour ouvrir la prise de rendez-vous au cabinet.') }}
                        </div>
                    </div>
                @empty
                    <div class="bg-white shadow rounded-lg p-6 text-gray-600">
                        {{ __('Aucun cabinet pour l’instant. Créez-en un pour commencer.') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <style>
        .hover\:shadow-xl:hover { box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1); }
    </style>
</x-app-layout>
