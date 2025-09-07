<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight">
            {{ __('Modifier le cabinet') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
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

            <div class="bg-white shadow rounded-lg p-6">
                <form method="POST" action="{{ route('practice-locations.update', $location) }}">
                    @csrf
                    @method('PUT')
                    @include('practice_locations._form', ['location' => $location])
                </form>

                <hr class="my-6">

                <form method="POST" action="{{ route('practice-locations.destroy', $location) }}"
                      onsubmit="return confirm('Supprimer ce cabinet ?');">
                    @csrf
                    @method('DELETE')
                    <button class="px-4 py-2 rounded-lg border border-red-300 text-red-700 hover:bg-red-50">
                        {{ __('Supprimer ce cabinet') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
