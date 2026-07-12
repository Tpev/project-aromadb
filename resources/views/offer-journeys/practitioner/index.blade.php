<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-[#647a0b]">Pages et campagnes</h1>
                <p class="mt-1 text-sm text-gray-600">Guidez chaque personne, de la découverte de votre offre jusqu’à la prise de rendez-vous ou à l’achat.</p>
            </div>
            @if($canPublish)
                <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('offer-journeys.contacts.index') }}" class="text-sm font-semibold text-[#647a0b] hover:text-[#854f38]">Personnes intéressées</a>
                @if(config('offer_journeys.campaigns_enabled'))<a href="{{ route('offer-journeys.message-campaigns.index') }}" class="text-sm font-semibold text-[#647a0b] hover:text-[#854f38]">Campagnes</a>@endif
                <a href="{{ route('offer-journeys.usage') }}" class="text-sm font-semibold text-[#647a0b] hover:text-[#854f38]">Utilisation</a>
                <a href="{{ route('offer-journeys.create') }}"
                   class="inline-flex items-center justify-center rounded-md bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white hover:bg-[#526509] focus:outline-none focus:ring-2 focus:ring-[#647a0b] focus:ring-offset-2">
                    Créer une page
                </a>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
                    {{ session('success') }}
                </div>
            @endif

            @unless($canPublish)
                <section class="rounded-lg border border-[#dfe6c7] bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">Une fonctionnalité Premium</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600">
                        Présentez clairement vos offres, reliez-les à votre agenda, vos ateliers ou vos formations, puis suivez les demandes reçues.
                    </p>
                    <a href="{{ route('profile.license') }}" class="mt-4 inline-flex text-sm font-semibold text-[#647a0b] hover:text-[#854f38]">
                        Découvrir l'offre Premium
                    </a>
                </section>
            @else
                <section class="rounded-lg border border-[#dfe6c7] bg-[#f7f9ec] p-5" aria-labelledby="next-action-title">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase text-[#526509]">Prochaine action recommandée</p>
                            <h2 id="next-action-title" class="mt-1 text-lg font-semibold text-gray-900">{{ $activation['next']['title'] }}</h2>
                            <p class="mt-1 max-w-2xl text-sm text-gray-600">{{ $activation['next']['body'] }}</p>
                        </div>
                        <a href="{{ $activation['next']['url'] }}" class="inline-flex shrink-0 justify-center rounded-md bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white hover:bg-[#526509]">{{ $activation['next']['label'] }}</a>
                    </div>
                    <div class="mt-5 grid gap-2 border-t border-[#dfe6c7] pt-4 sm:grid-cols-5" aria-label="Première mise en ligne">
                        @foreach($activation['checks'] as $check)
                            <div class="flex items-center gap-2 text-xs {{ $check['done'] ? 'font-medium text-[#526509]' : 'text-gray-500' }}">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full {{ $check['done'] ? 'bg-[#647a0b] text-white' : 'border border-gray-300 bg-white' }}">{{ $check['done'] ? '✓' : $loop->iteration }}</span>
                                <span>{{ $check['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
                @unless($journeys->isEmpty())
                <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5" aria-label="Résumé des 30 derniers jours">
                    @foreach([['Parcours en ligne',$summary['published']],['Nouveaux contacts',$summary['contacts']],['Actions réalisées',$summary['conversions']],['Revenu attribué',number_format($summary['revenue_cents']/100,2,',',' ').' €'],['Sans échange depuis 30 j',$summary['inactive']]] as [$label,$value])
                        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm"><p class="text-xs font-medium text-gray-500">{{ $label }}</p><p class="mt-1 text-xl font-semibold text-gray-900">{{ $value }}</p></div>
                    @endforeach
                </section>
                @if($summary['inactive'] > 0)<div class="flex justify-end"><a href="{{ route('offer-journeys.contacts.index', ['inactive_days'=>30]) }}" class="text-sm font-semibold text-[#647a0b] hover:text-[#854f38]">Voir les personnes à recontacter</a></div>@endif
                @endunless
                @if($journeys->isEmpty())
                    <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <h2 class="font-semibold text-gray-900">Votre première page en trois temps</h2>
                        <div class="mt-4 grid gap-4 sm:grid-cols-3">@foreach([['1','Choisissez un résultat','Rendez-vous, atelier, ressource, formation, bon cadeau ou demande de contact.'],['2','Relisez le brouillon','Olithea prépare les étapes sans rien publier automatiquement.'],['3','Testez puis partagez','Vérifiez exactement ce que verra la personne avant la mise en ligne.']] as [$number,$title,$body])<div><span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#f0f4df] text-xs font-semibold text-[#526509]">{{ $number }}</span><p class="mt-2 text-sm font-semibold text-gray-900">{{ $title }}</p><p class="mt-1 text-sm text-gray-500">{{ $body }}</p></div>@endforeach</div>
                    </section>
                @else
                    <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-200 px-4 py-4 sm:px-5">
                            <h2 class="font-semibold text-gray-900">Vos pages et parcours</h2>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @foreach($journeys as $journey)
                                @php
                                    $status = [
                                        'draft' => ['Brouillon', 'bg-gray-100 text-gray-700'],
                                        'published' => ['Publié', 'bg-green-100 text-green-800'],
                                        'paused' => ['En pause', 'bg-amber-100 text-amber-800'],
                                        'archived' => ['Archivé', 'bg-gray-100 text-gray-500'],
                                    ][$journey->status] ?? [$journey->status, 'bg-gray-100 text-gray-700'];
                                @endphp
                                <article class="p-4 sm:p-5">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h3 class="truncate font-semibold text-gray-900">{{ $journey->name }}</h3>
                                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $status[1] }}">{{ $status[0] }}</span>
                                            </div>
                                            <p class="mt-1 text-sm text-gray-500">{{ config('offer_journeys.objective_labels.'.$journey->objective, $journey->objective) }} · modifié {{ $journey->updated_at->diffForHumans() }}</p>
                                            <dl class="mt-3 grid grid-cols-3 gap-4 text-sm">
                                                <div><dt class="text-xs text-gray-500">Vues</dt><dd class="font-semibold text-gray-900">{{ $journey->views_count }}</dd></div>
                                                <div><dt class="text-xs text-gray-500">Contacts</dt><dd class="font-semibold text-gray-900">{{ $journey->leads_count }}</dd></div>
                                                <div><dt class="text-xs text-gray-500">Conversions</dt><dd class="font-semibold text-gray-900">{{ $journey->conversions_count }}</dd></div>
                                            </dl>
                                        </div>
                                        <a href="{{ route('offer-journeys.show', $journey) }}" class="inline-flex shrink-0 items-center justify-center rounded-md border border-[#647a0b] px-3 py-2 text-sm font-semibold text-[#647a0b] hover:bg-[#f7f9ec]">
                                            Ouvrir
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                    {{ $journeys->links() }}
                @endif
            @endunless
        </div>
    </div>
</x-app-layout>
