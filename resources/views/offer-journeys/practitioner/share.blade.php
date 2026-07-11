<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('offer-journeys.show', $journey) }}" class="text-sm font-medium text-[#647a0b] hover:text-[#854f38]">{{ $journey->name }}</a>
            <h1 class="mt-1 text-2xl font-semibold text-gray-900">Partager</h1>
            <p class="mt-1 text-sm text-gray-500">Utilisez un lien distinct pour comprendre d'où viennent les visites.</p>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ copied: '' }">
        <div class="mx-auto grid max-w-7xl gap-5 px-4 sm:px-6 lg:grid-cols-[minmax(0,1fr)_320px] lg:px-8">
            <div class="space-y-5">
                @if(session('success'))
                    <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('success') }}</div>
                @endif

                <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="font-semibold text-gray-900">Lien principal</h2>
                    <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                        <input value="{{ $canonicalUrl }}" readonly class="min-w-0 flex-1 rounded-md border-gray-300 bg-gray-50 text-sm text-gray-700">
                        <button type="button" @click="navigator.clipboard.writeText(@js($canonicalUrl)); copied = 'main'" class="rounded-md border border-[#647a0b] px-3 py-2 text-sm font-semibold text-[#647a0b] hover:bg-[#f7f9ec]" x-text="copied === 'main' ? 'Copié' : 'Copier le lien'">Copier le lien</button>
                    </div>
                </section>

                <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="font-semibold text-gray-900">Nouveau lien de campagne</h2>
                    <p class="mt-1 text-sm text-gray-500">Le suivi est ajouté automatiquement; aucun paramètre technique à saisir.</p>
                    <form method="POST" action="{{ route('offer-journeys.campaigns.store', $journey) }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                        @csrf
                        <div><label for="name" class="block text-sm font-medium text-gray-700">Nom du lien</label><input id="name" name="name" required maxlength="120" value="{{ old('name') }}" placeholder="Ex. Publication Instagram juillet" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]"></div>
                        <div><label for="channel" class="block text-sm font-medium text-gray-700">Canal</label><select id="channel" name="channel" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]">@foreach(['instagram'=>'Instagram','google_business'=>'Google Business','newsletter'=>'Newsletter','email'=>'Email individuel','facebook'=>'Facebook','qr'=>'Support imprimé','partner'=>'Partenaire','direct'=>'Autre'] as $key=>$label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></div>
                        <div class="sm:col-span-2"><label for="utm_content" class="block text-sm font-medium text-gray-700">Repère facultatif</label><input id="utm_content" name="utm_content" maxlength="120" value="{{ old('utm_content') }}" placeholder="Ex. carrousel-conseils" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]"></div>
                        <div class="sm:col-span-2"><button class="rounded-md bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white hover:bg-[#526509]">Créer le lien</button></div>
                    </form>
                </section>

                <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-5 py-4"><h2 class="font-semibold text-gray-900">Liens créés</h2></div>
                    @forelse($journey->campaignLinks as $campaign)
                        @php
                            $campaignUrl = $canonicalUrl.'?'.http_build_query(array_filter([
                                'oj_campaign' => $campaign->code,
                                'utm_source' => $campaign->utm_source,
                                'utm_medium' => $campaign->utm_medium,
                                'utm_campaign' => $campaign->utm_campaign,
                                'utm_content' => $campaign->utm_content,
                            ]));
                        @endphp
                        <div class="border-b border-gray-100 p-4 last:border-0 {{ $campaign->is_active ? '' : 'bg-gray-50 opacity-70' }}">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0"><p class="font-medium text-gray-900">{{ $campaign->name }}</p><p class="mt-1 text-sm text-gray-500">{{ ['instagram'=>'Instagram','google_business'=>'Google Business','newsletter'=>'Newsletter','email'=>'Email individuel','facebook'=>'Facebook','qr'=>'Support imprimé','partner'=>'Partenaire','direct'=>'Autre'][$campaign->channel] ?? $campaign->channel }} · créé le {{ $campaign->created_at->format('d/m/Y') }}</p></div>
                                <div class="flex shrink-0 gap-2">
                                    @if($campaign->is_active)<button type="button" @click="navigator.clipboard.writeText(@js($campaignUrl)); copied = @js($campaign->code)" class="rounded-md border border-[#647a0b] px-3 py-1.5 text-xs font-semibold text-[#647a0b]" x-text="copied === @js($campaign->code) ? 'Copié' : 'Copier'">Copier</button><form method="POST" action="{{ route('offer-journeys.campaigns.destroy', [$journey, $campaign]) }}">@csrf @method('DELETE')<button class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-600">Désactiver</button></form>@else<span class="text-xs font-medium text-gray-500">Désactivé</span>@endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-sm text-gray-500">Aucun lien de campagne pour le moment.</p>
                    @endforelse
                </section>
            </div>

            <aside>
                <section class="rounded-lg border border-gray-200 bg-white p-5 text-center shadow-sm">
                    <h2 class="text-left font-semibold text-gray-900">QR code</h2>
                    <div class="mx-auto mt-4 aspect-square w-full max-w-56 rounded-md border border-gray-200 bg-white p-3">
                        <img src="{{ route('offer-journeys.qr', $journey) }}" alt="QR code du parcours {{ $journey->name }}" class="h-full w-full object-contain">
                    </div>
                    <a href="{{ route('offer-journeys.qr', $journey) }}" target="_blank" rel="noopener" class="mt-4 inline-flex rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Ouvrir le QR code</a>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
