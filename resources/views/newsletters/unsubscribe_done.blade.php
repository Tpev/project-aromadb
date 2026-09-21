<x-guest-layout>
    <div class="mx-auto max-w-xl px-4 py-12">
        <h1 class="text-2xl font-semibold">Votre désabonnement est confirmé</h1>
        <p class="mt-4">Vous ne recevrez plus les newsletters de {{ $therapist->name }} à l’adresse {{ $recipient->email }}.</p>
    </div>
</x-guest-layout>
