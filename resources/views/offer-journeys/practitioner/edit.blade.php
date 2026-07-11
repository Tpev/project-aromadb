<x-app-layout>
    <x-slot name="header">
        <div><a href="{{ route('offer-journeys.show', $journey) }}" class="text-sm font-medium text-[#647a0b] hover:text-[#854f38]">{{ $journey->name }}</a><h1 class="mt-1 text-2xl font-semibold text-gray-900">Paramètres</h1></div>
    </x-slot>
    <div class="py-6">
        <div class="mx-auto max-w-3xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if(session('success'))<div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>@endif
            <form method="POST" action="{{ route('offer-journeys.update', $journey) }}" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div><label for="name" class="block text-sm font-medium text-gray-700">Nom interne</label><input id="name" name="name" value="{{ old('name', $journey->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]"></div>
                    <div><label for="slug" class="block text-sm font-medium text-gray-700">Adresse publique</label><div class="mt-1 flex rounded-md shadow-sm"><span class="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500">/offres/</span><input id="slug" name="slug" value="{{ old('slug', $journey->slug) }}" required class="min-w-0 flex-1 rounded-none rounded-r-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]"></div></div>
                    <label class="flex items-start gap-3"><input type="checkbox" name="show_on_profile" value="1" @checked(old('show_on_profile', $journey->show_on_profile)) class="mt-1 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"><span><span class="block text-sm font-medium text-gray-900">Afficher sur mon profil public</span><span class="block text-sm text-gray-500">L'affichage ne sera actif que lorsque le module public sera autorisé.</span></span></label>
                </div>
                <div class="mt-6 flex justify-end"><button class="rounded-md bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white hover:bg-[#526509]">Enregistrer</button></div>
            </form>
            <form method="POST" action="{{ route('offer-journeys.archive', $journey) }}" class="rounded-lg border border-red-200 bg-white p-5">@csrf<h2 class="font-semibold text-gray-900">Archiver le parcours</h2><p class="mt-1 text-sm text-gray-600">La ressource liée, les rendez-vous et les paiements ne seront pas supprimés.</p><button class="mt-4 rounded-md border border-red-300 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Archiver</button></form>
        </div>
    </div>
</x-app-layout>
