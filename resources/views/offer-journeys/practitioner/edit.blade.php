<x-app-layout>
    <x-slot name="header">
        @include('offer-journeys.practitioner._workspace-header')
    </x-slot>
    <div class="py-6">
        <div class="mx-auto max-w-3xl space-y-5 px-4 sm:px-6 lg:px-8">
            <div><h2 class="text-xl font-semibold text-gray-900">Paramètres du parcours</h2><p class="mt-1 text-sm text-gray-500">Nom interne, adresse publique et visibilité sur votre profil.</p></div>
            @if(session('success'))<div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>@endif
            <form method="POST" action="{{ route('offer-journeys.update', $journey) }}" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div><label for="name" class="block text-sm font-medium text-gray-700">Nom interne</label><input id="name" name="name" value="{{ old('name', $journey->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]"></div>
                    <div><label for="slug" class="block text-sm font-medium text-gray-700">Adresse publique</label><div class="mt-1 flex rounded-md shadow-sm"><span class="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500">/offres/</span><input id="slug" name="slug" value="{{ old('slug', $journey->slug) }}" required class="min-w-0 flex-1 rounded-none rounded-r-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]"></div></div>
                    @if(in_array($journey->objective, ['appointment', 'event', 'training'], true))
                        @php
                            $sourceLabel = match($journey->objective) {
                                'appointment' => 'Prestation proposée après la page',
                                'event' => 'Événement proposé après la page',
                                default => 'Formation proposée après la page',
                            };
                            $sourceCreateUrl = match($journey->objective) {
                                'appointment' => route('products.create'),
                                'event' => route('events.create'),
                                default => route('digital-trainings.create'),
                            };
                            $sourceCreateLabel = match($journey->objective) {
                                'appointment' => 'Créer une prestation',
                                'event' => 'Créer un événement',
                                default => 'Créer une formation',
                            };
                        @endphp
                        <div class="border-t border-gray-100 pt-4">
                            <label for="source_ref" class="block text-sm font-medium text-gray-700">{{ $sourceLabel }}</label>
                            <select id="source_ref" name="source_ref" class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#647a0b] focus:ring-[#647a0b]">
                                <option value="">Je choisirai plus tard</option>
                                @foreach($sourceOptions as $option)
                                    <option value="{{ $option['value'] }}" @selected(old('source_ref', $currentSourceRef) === $option['value'])>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Cette association détermine où le visiteur sera dirigé. Elle est nécessaire avant la prochaine publication.</p>
                            @if($sourceOptions->isEmpty())
                                <p class="mt-2 text-sm text-amber-800">Aucun élément disponible. <a href="{{ $sourceCreateUrl }}" class="font-semibold underline">{{ $sourceCreateLabel }}</a>, puis revenez l’associer ici.</p>
                            @endif
                            @if($journey->published_version_id)
                                <p class="mt-2 border-l-2 border-[#647a0b] bg-[#f7f9ec] px-3 py-2 text-xs text-gray-700">La version actuellement en ligne reste inchangée tant que vous ne republiez pas ce parcours.</p>
                            @endif
                            @error('source_ref')<p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>@enderror
                        </div>
                    @elseif($journey->objective === 'gift_voucher')
                        <div class="border-l-2 border-[#647a0b] bg-[#f7f9ec] px-3 py-2 text-sm text-gray-700">Après la page, le visiteur sera dirigé vers votre espace de bons cadeaux Olithea.</div>
                    @endif
                    <label class="flex items-start gap-3"><input type="checkbox" name="show_on_profile" value="1" @checked(old('show_on_profile', $journey->show_on_profile)) class="mt-1 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"><span><span class="block text-sm font-medium text-gray-900">Afficher sur mon profil public</span><span class="block text-sm text-gray-500">L'affichage ne sera actif que lorsque le module public sera autorisé.</span></span></label>
                </div>
                <div class="mt-6 flex justify-end"><button class="rounded-md bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white hover:bg-[#526509]">Enregistrer</button></div>
            </form>
            <form method="POST" action="{{ route('offer-journeys.archive', $journey) }}" class="rounded-lg border border-red-200 bg-white p-5">@csrf<h2 class="font-semibold text-gray-900">Archiver le parcours</h2><p class="mt-1 text-sm text-gray-600">La ressource liée, les rendez-vous et les paiements ne seront pas supprimés.</p><button class="mt-4 rounded-md border border-red-300 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Archiver</button></form>
        </div>
    </div>
</x-app-layout>
