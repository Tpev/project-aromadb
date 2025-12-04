{{-- resources/views/newsletters/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color:#647a0b;">
            {{ __('Aperçu de la newsletter') }} – {{ $newsletter->title }}
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto py-8 px-4">
        <div class="mb-4 text-sm text-gray-500">
            Ceci est un aperçu web. Le rendu email sera très proche.
        </div>

        <div class="bg-gray-100 py-6 flex justify-center">
            <div class="w-full max-w-[600px] bg-white border border-gray-200 rounded-lg overflow-hidden">
                @include('emails.newsletter', [
                    'newsletter' => $newsletter,
                    'client' => $client,
                    // pas besoin d’URL de désabonnement ici
                    'unsubscribeUrl' => null,
                    'isWebPreview' => true,
                ])
            </div>
        </div>
    </div>
</x-app-layout>
