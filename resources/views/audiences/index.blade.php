<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#647a0b;">
            {{ __('Listes / Audiences email') }}
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto py-8 px-4 space-y-6">
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Vos listes de contacts</h1>
                <p class="text-sm text-gray-500">
                    Créez des audiences (segments) pour envoyer vos newsletters à des groupes spécifiques.
                </p>
            </div>
            <a href="{{ route('audiences.create') }}"
               class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white shadow-sm"
               style="background-color:#647a0b;">
                + Nouvelle liste
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            @if ($audiences->isEmpty())
                <div class="p-6 text-sm text-gray-500 text-center">
                    Vous n’avez pas encore créé de liste.
                    <a href="{{ route('audiences.create') }}" class="text-[#647a0b] font-semibold">
                        Créer une première liste
                    </a>.
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach ($audiences as $audience)
                        <div class="px-4 py-4 flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800">
                                    {{ $audience->name }}
                                </h3>
                                @if ($audience->description)
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $audience->description }}
                                    </p>
                                @endif
                                <p class="mt-2 text-xs text-gray-400">
                                    {{ $audience->clients_count }} contact(s).
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('audiences.edit', $audience) }}"
                                   class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200">
                                    Modifier
                                </a>
                                <form action="{{ route('audiences.destroy', $audience) }}"
                                      method="POST"
                                      onsubmit="return confirm('Supprimer cette liste ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100">
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
