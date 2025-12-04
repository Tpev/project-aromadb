{{-- resources/views/audiences/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#647a0b;">
            {{ __('Créer une liste / audience') }}
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto py-8 px-4 space-y-6">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Nouvelle liste</h1>
            <p class="text-sm text-gray-500">
                Donnez un nom à votre liste et choisissez les clients qui en font partie.
            </p>
        </div>

        <form action="{{ route('audiences.store') }}" method="POST">
            @csrf
            @include('audiences._form', ['audience' => new \App\Models\Audience()])
        </form>
    </div>
</x-app-layout>
