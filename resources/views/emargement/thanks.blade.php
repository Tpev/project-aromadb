<x-guest-layout>
    <div class="max-w-xl mx-auto p-6 bg-white rounded-lg shadow">
        <h1 class="text-2xl font-bold mb-4">Merci !</h1>
        <p>Votre présence a été enregistrée.</p>
        @if($em->pdf_path)
            <p class="mt-3"><a class="text-blue-600 underline" href="{{ route('emargement.download', $em->id) }}">Télécharger le justificatif PDF</a></p>
        @endif
    </div>
</x-guest-layout>
