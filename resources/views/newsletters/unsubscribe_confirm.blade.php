<x-guest-layout>
    <div class="max-w-md mx-auto py-10 px-4">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">
            Se désabonner des emails
        </h1>

        <p class="text-sm text-gray-600 mb-4">
            Vous êtes sur le point de vous désabonner des emails envoyés par
            <span class="font-semibold">{{ $therapist->name ?? $therapist->company_name }}</span>.
        </p>

        <p class="text-sm text-gray-600 mb-4">
            Vous ne recevrez plus de newsletters ni d’informations marketing envoyées via AromaMade par ce thérapeute.
            Vous continuerez toutefois à recevoir les emails strictement nécessaires à la gestion de vos rendez-vous (confirmations, rappels, etc.), le cas échéant.
        </p>

        <form method="POST" action="{{ route('unsubscribe.newsletter.confirm', $recipient->unsubscribe_token) }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Facultatif : une raison de votre désabonnement ?
                </label>
                <input type="text"
                       name="reason"
                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#647a0b] focus:border-[#647a0b]"
                       placeholder="Trop d’emails, sujet non pertinent...">
            </div>

            <div class="flex items-center justify-between mt-6">
                <a href="{{ config('app.url') }}"
                   class="text-xs text-gray-500 hover:text-gray-700 underline">
                    Retour au site
                </a>

                <button type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white shadow-sm"
                        style="background-color:#854f38;">
                    Confirmer le désabonnement
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>
