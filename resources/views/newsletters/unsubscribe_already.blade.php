<x-guest-layout>
    <div class="max-w-md mx-auto py-10 px-4 text-center">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">
            Vous êtes déjà désabonné(e)
        </h1>

        <p class="text-sm text-gray-600 mb-4">
            L’adresse <span class="font-mono">{{ $recipient->email }}</span> est déjà désabonnée
            des newsletters envoyées par
            <span class="font-semibold">{{ $therapist->name ?? $therapist->company_name }}</span>.
        </p>

        <p class="text-xs text-gray-500">
            Si vous pensez qu’il s’agit d’une erreur, vous pouvez contacter directement votre thérapeute.
        </p>

        <div class="mt-6">
            <a href="{{ config('app.url') }}"
               class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white shadow-sm"
               style="background-color:#647a0b;">
                Retour au site
            </a>
        </div>
    </div>
</x-guest-layout>
