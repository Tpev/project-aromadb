{{-- resources/views/newsletters/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#647a0b;">
            {{ __('Nouvelle newsletter') }}
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto py-8 px-4">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-1">
                Créer une newsletter
            </h1>
            <p class="text-sm text-gray-500">
                Rédigez votre email, prévisualisez-le et envoyez-le à vos clients.
            </p>
        </div>

        @include('newsletters._form', [
            'route'          => route('newsletters.store'),
            'method'         => 'POST',
            'initialBlocks'  => $initialBlocks ?? [],
        ])
    </div>
</x-app-layout>
