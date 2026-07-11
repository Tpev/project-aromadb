<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div><a href="{{ route('offer-journeys.index') }}" class="text-sm font-medium text-[#647a0b]">Parcours d’offre</a><h1 class="mt-1 text-2xl font-semibold text-gray-900">Campagnes programmées</h1><p class="mt-1 text-sm text-gray-500">Préparez un message commun à plusieurs parcours sans sursolliciter une même personne.</p></div>
        </div>
    </x-slot>

    <div class="py-6"><div class="mx-auto grid max-w-7xl gap-5 px-4 sm:px-6 lg:grid-cols-[minmax(0,1fr)_380px] lg:px-8">
        <div class="space-y-5">
            @if(session('success'))<div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-gray-900">Cette semaine</h2>
                <div class="mt-4 divide-y divide-gray-100">
                    @forelse($week as $item)<div class="flex items-start justify-between gap-4 py-3"><div><p class="text-sm font-medium text-gray-900">{{ $item->name }}</p><p class="mt-1 text-xs text-gray-500">{{ $item->scheduled_at->translatedFormat('l d F à H:i') }}</p></div><span class="rounded-full bg-[#f0f4df] px-2 py-0.5 text-xs font-medium text-[#526509]">{{ $item->status === 'scheduled' ? 'Programmée' : $item->status }}</span></div>@empty<p class="py-4 text-sm text-gray-500">Aucun message programmé cette semaine.</p>@endforelse
                </div>
            </section>

            <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-5 py-4"><h2 class="font-semibold text-gray-900">Historique</h2></div>
                <div class="divide-y divide-gray-100">@forelse($campaigns as $campaign)<article class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-start sm:justify-between"><div><p class="font-medium text-gray-900">{{ $campaign->name }}</p><p class="mt-1 text-sm text-gray-600">{{ $campaign->subject }}</p><p class="mt-1 text-xs text-gray-500">{{ $campaign->journeys->pluck('name')->join(', ') }} · {{ $campaign->scheduled_at?->format('d/m/Y H:i') }}</p>@if($campaign->status === 'sent')<p class="mt-1 text-xs text-gray-500">{{ $campaign->sent_count }} envoyé(s), {{ $campaign->skipped_count }} exclu(s)</p>@endif</div><div class="flex items-center gap-2"><span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">{{ ['scheduled'=>'Programmée','processing'=>'En cours','sent'=>'Envoyée','cancelled'=>'Annulée'][$campaign->status] ?? $campaign->status }}</span>@if($campaign->status === 'scheduled')<form method="POST" action="{{ route('offer-journeys.message-campaigns.cancel', $campaign) }}">@csrf<button class="text-xs font-semibold text-red-700">Annuler</button></form>@endif</div></article>@empty<div class="px-5 py-10 text-center text-sm text-gray-500">Aucune campagne.</div>@endforelse</div>
                <div class="px-5 py-4">{{ $campaigns->links() }}</div>
            </section>
        </div>

        <aside><section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm"><h2 class="font-semibold text-gray-900">Programmer une campagne</h2><p class="mt-1 text-xs text-gray-500">Le nombre exact de destinataires sera recalculé juste avant l’envoi.</p><form method="POST" action="{{ route('offer-journeys.message-campaigns.store') }}" class="mt-4 space-y-4">@csrf
            <div><label class="block text-sm font-medium text-gray-700" for="campaign-name">Nom interne</label><input id="campaign-name" name="name" value="{{ old('name') }}" required maxlength="120" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700" for="campaign-subject">Objet</label><input id="campaign-subject" name="subject" value="{{ old('subject') }}" required maxlength="180" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700" for="campaign-body">Message</label><textarea id="campaign-body" name="body" rows="7" required maxlength="6000" class="mt-1 block w-full rounded-md border-gray-300 text-sm">{{ old('body') }}</textarea><p class="mt-1 text-xs text-gray-500">Variables: @{{prenom}}, @{{offre}}, @{{nom_praticien}}, @{{lien_offre}}</p></div>
            <fieldset><legend class="text-sm font-medium text-gray-700">Parcours concernés</legend><div class="mt-2 max-h-44 space-y-2 overflow-y-auto rounded-md border border-gray-200 p-3">@forelse($journeys as $journey)<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="journey_ids[]" value="{{ $journey->id }}" @checked(in_array($journey->id, old('journey_ids', []))) class="rounded border-gray-300 text-[#647a0b]">{{ $journey->name }}</label>@empty<p class="text-sm text-gray-500">Publiez d’abord un parcours.</p>@endforelse</div></fieldset>
            <div><label class="block text-sm font-medium text-gray-700" for="campaign-date">Date et heure</label><input id="campaign-date" type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" required class="mt-1 block w-full rounded-md border-gray-300 text-sm"></div>
            <button class="w-full rounded-md bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white" @disabled($journeys->isEmpty())>Programmer</button>
        </form></section></aside>
    </div></div>
</x-app-layout>
