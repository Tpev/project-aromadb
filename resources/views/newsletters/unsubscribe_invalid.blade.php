<x-guest-layout>
    <div class="max-w-md mx-auto py-10 px-4 text-center">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">
            Lien invalide
        </h1>

        <p class="text-sm text-gray-600 mb-4">
            Ce lien de désabonnement n’est plus valide ou a déjà été utilisé.
        </p>

        <p class="text-xs text-gray-500">
            Si vous ne souhaitez plus recevoir d’emails, vous pouvez répondre directement à l’expéditeur 
            ou contacter votre thérapeute pour demander le désabonnement.
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
