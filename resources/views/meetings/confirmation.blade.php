<x-app-layout>
    <div class="max-w-md mx-auto mt-10 bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4">Merci d'avoir créé la réunion !</h2>

        <p>Voici le lien pour rejoindre la réunion :</p>
        <a href="{{ $link }}" class="text-blue-500 hover:underline">{{ $link }}</a>
    </div>
</x-app-layout>
