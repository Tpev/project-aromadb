<x-guest-layout>
    <div class="max-w-xl mx-auto p-6 bg-white rounded-lg shadow">
        <h1 class="text-2xl font-bold mb-4">Déjà signé</h1>
        <p>Cette feuille d’émargement a déjà été signée le {{ optional($em->signed_at)->format('d/m/Y H:i') }}.</p>
        @if($em->pdf_path)
            <a class="text-blue-600 underline" href="{{ route('emargement.download', $em->id) }}">Télécharger le justificatif PDF</a>
        @endif
    </div>
</x-guest-layout>
