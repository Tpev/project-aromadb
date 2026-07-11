<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase text-[#854f38]">Administration Olithea</p>
            <h1 class="mt-1 text-2xl font-semibold text-gray-900">Support des parcours d'offre</h1>
            <p class="mt-1 text-sm text-gray-600">Diagnostic des envois, executions et parcours sans acces direct a la base.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <div>
            <p class="text-xs font-semibold uppercase text-[#854f38]">Administration Olithea</p>
            <h1 class="mt-1 text-2xl font-semibold text-gray-900">Support des parcours d'offre</h1>
            <p class="mt-1 text-sm text-gray-600">Diagnostic des envois, executions et parcours sans acces direct a la base.</p>
        </div>
        @if (session('success'))
            <div class="border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
        @endif

        <section aria-labelledby="deliverability-title">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="deliverability-title" class="text-lg font-semibold text-gray-900">Delivrabilite sur 30 jours</h2>
                    <p class="text-sm text-gray-600">Les taux utilisent les messages envoyes connus par Olithea.</p>
                </div>
                <a href="{{ route('admin.offer-journeys.support.index', ['refresh_dns' => 1, 'q' => $query]) }}" class="text-sm font-semibold text-[#647a0b] hover:underline">Actualiser le diagnostic DNS</a>
            </div>
            <dl class="mt-3 grid grid-cols-2 gap-px overflow-hidden border border-gray-200 bg-gray-200 sm:grid-cols-4 lg:grid-cols-7">
                @foreach ([
                    'Envoyes' => $metrics['sent'],
                    'Distribues' => $metrics['delivered'],
                    'Rejets' => $metrics['bounced'],
                    'Plaintes' => $metrics['complaints'],
                    'Refuses' => $metrics['rejected'],
                    'Taux de rejet' => $metrics['bounce_rate'].' %',
                    'Taux de plainte' => $metrics['complaint_rate'].' %',
                ] as $label => $value)
                    <div class="bg-white px-4 py-3">
                        <dt class="text-xs text-gray-500">{{ $label }}</dt>
                        <dd class="mt-1 text-xl font-semibold text-gray-900">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        <section class="border-y border-gray-200 bg-white py-5" aria-labelledby="dns-title">
            <div class="px-4 sm:px-5">
                <h2 id="dns-title" class="text-lg font-semibold text-gray-900">Authentification de {{ $dns['domain'] }}</h2>
                <div class="mt-4 grid gap-5 md:grid-cols-3">
                    @foreach (['spf' => 'SPF', 'dkim' => 'DKIM', 'dmarc' => 'DMARC'] as $key => $label)
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full {{ $dns[$key]['valid'] ? 'bg-green-600' : 'bg-red-600' }}"></span>
                                <h3 class="font-semibold text-gray-900">{{ $label }}</h3>
                            </div>
                            <p class="mt-2 text-sm text-gray-600">{{ $dns[$key]['recommendation'] }}</p>
                            @if (isset($dns[$key]['value']) && $dns[$key]['value'])
                                <p class="mt-2 break-all font-mono text-xs text-gray-500">{{ $dns[$key]['value'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section aria-labelledby="search-title">
            <h2 id="search-title" class="text-lg font-semibold text-gray-900">Recherche support</h2>
            <form method="GET" action="{{ route('admin.offer-journeys.support.index') }}" class="mt-3 flex gap-2">
                <label for="support-query" class="sr-only">Parcours, contact, execution, message ou praticien</label>
                <input id="support-query" name="q" value="{{ $query }}" class="min-w-0 flex-1 border-gray-300 text-sm focus:border-[#647a0b] focus:ring-[#647a0b]" placeholder="Email, nom, identifiant, slug ou erreur">
                <button class="bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white hover:bg-[#536608]">Rechercher</button>
            </form>
        </section>

        @if ($query !== '')
            <section class="space-y-6" aria-label="Resultats de recherche">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Praticiens</h2>
                    <div class="mt-2 overflow-x-auto border border-gray-200 bg-white">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-3 py-2">Compte</th><th class="px-3 py-2">Controle des envois</th></tr></thead>
                            <tbody class="divide-y divide-gray-100">
                            @forelse ($results['practitioners'] as $practitioner)
                                <tr>
                                    <td class="px-3 py-3"><strong>{{ $practitioner->company_name ?: $practitioner->name }}</strong><br><span class="text-gray-500">#{{ $practitioner->id }} · {{ $practitioner->email }}</span></td>
                                    <td class="px-3 py-3">
                                        <form method="POST" action="{{ route('admin.offer-journeys.support.sender-control', $practitioner) }}" class="flex min-w-[540px] gap-2">
                                            @csrf
                                            <select name="mode" class="border-gray-300 text-xs"><option value="marketing">Pause marketing</option><option value="all">Pause tous les emails</option><option value="resume">Retablir</option></select>
                                            <input name="reason" required minlength="5" class="min-w-0 flex-1 border-gray-300 text-xs" placeholder="Motif obligatoire">
                                            <button class="border border-gray-300 px-3 py-2 text-xs font-semibold hover:bg-gray-50">Appliquer</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="px-3 py-5 text-center text-gray-500">Aucun praticien trouve.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <h2 class="text-base font-semibold text-gray-900">Parcours</h2>
                    <div class="mt-2 overflow-x-auto border border-gray-200 bg-white">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-3 py-2">Parcours</th><th class="px-3 py-2">Etat</th><th class="px-3 py-2">Action</th></tr></thead>
                            <tbody class="divide-y divide-gray-100">
                            @forelse ($results['journeys'] as $journey)
                                <tr>
                                    <td class="px-3 py-3"><strong>{{ $journey->name }}</strong><br><span class="text-gray-500">#{{ $journey->id }} · {{ $journey->user->company_name ?: $journey->user->name }}</span></td>
                                    <td class="px-3 py-3">{{ config('offer_journeys.status_labels.'.$journey->status, $journey->status) }}</td>
                                    <td class="px-3 py-3">
                                        @if ($journey->status !== 'paused')
                                            <form method="POST" action="{{ route('admin.offer-journeys.support.journeys.pause', $journey) }}" class="flex min-w-[360px] gap-2">
                                                @csrf
                                                <input name="reason" required minlength="5" class="min-w-0 flex-1 border-gray-300 text-xs" placeholder="Motif de la pause">
                                                <button class="border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-900">Mettre en pause</button>
                                            </form>
                                        @else
                                            <span class="text-gray-500">Deja en pause</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-3 py-5 text-center text-gray-500">Aucun parcours trouve.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Contacts</h2>
                        <div class="mt-2 divide-y divide-gray-100 border border-gray-200 bg-white">
                            @forelse ($results['contacts'] as $contact)
                                <article class="p-3 text-sm"><div class="flex justify-between gap-3"><strong>{{ $contact->display_name }}</strong><span>#{{ $contact->id }}</span></div><p class="mt-1 text-gray-600">{{ $contact->email }}</p><p class="text-xs text-gray-500">{{ $contact->user->company_name ?: $contact->user->name }} · {{ $contact->status }}</p></article>
                            @empty
                                <p class="p-4 text-sm text-gray-500">Aucun contact trouvé.</p>
                            @endforelse
                        </div>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Campagnes programmées</h2>
                        <div class="mt-2 divide-y divide-gray-100 border border-gray-200 bg-white">
                            @forelse ($results['campaigns'] as $campaign)
                                <article class="p-3 text-sm"><div class="flex justify-between gap-3"><strong>{{ $campaign->name }}</strong><span>{{ $campaign->status }}</span></div><p class="mt-1 text-gray-600">{{ $campaign->subject }}</p><p class="text-xs text-gray-500">#{{ $campaign->id }} · {{ $campaign->user->company_name ?: $campaign->user->name }} · {{ $campaign->scheduled_at?->format('d/m/Y H:i') ?: 'Sans date' }}</p></article>
                            @empty
                                <p class="p-4 text-sm text-gray-500">Aucune campagne trouvée.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Executions</h2>
                        <div class="mt-2 divide-y divide-gray-100 border border-gray-200 bg-white">
                            @forelse ($results['runs'] as $run)
                                <article class="p-3 text-sm">
                                    <div class="flex justify-between gap-3"><strong>Execution #{{ $run->id }}</strong><span>{{ $run->status }}</span></div>
                                    <p class="mt-1 text-gray-600">{{ $labels->reason($run->exit_reason ?: ($run->last_error ? 'failed' : null)) }}</p>
                                    <p class="text-xs text-gray-500">{{ $labels->recommendation($run->exit_reason) }}</p>
                                    @if ($run->status === 'failed')
                                        <form method="POST" action="{{ route('admin.offer-journeys.support.runs.retry', $run) }}" class="mt-3 flex gap-2">
                                            @csrf
                                            <input name="reason" required minlength="5" class="min-w-0 flex-1 border-gray-300 text-xs" placeholder="Motif de la relance">
                                            <button class="border border-gray-300 px-3 py-2 text-xs font-semibold">Relancer sans doublon</button>
                                        </form>
                                    @endif
                                </article>
                            @empty
                                <p class="p-4 text-sm text-gray-500">Aucune execution trouvee.</p>
                            @endforelse
                        </div>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Messages</h2>
                        <div class="mt-2 divide-y divide-gray-100 border border-gray-200 bg-white">
                            @forelse ($results['deliveries'] as $delivery)
                                <article class="p-3 text-sm">
                                    <div class="flex justify-between gap-3"><strong>Message #{{ $delivery->id }}</strong><span>{{ $delivery->status }}</span></div>
                                    <p class="mt-1 text-gray-600">{{ $delivery->recipient_email }}</p>
                                    @if ($delivery->failure_reason)<p class="mt-1 text-xs text-red-700">{{ $labels->reason($delivery->failure_reason) }}</p>@endif
                                </article>
                            @empty
                                <p class="p-4 text-sm text-gray-500">Aucun message trouve.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <section class="grid gap-6 lg:grid-cols-[1fr_2fr]" aria-labelledby="operations-title">
            <div>
                <h2 id="operations-title" class="text-lg font-semibold text-gray-900">Reconciliation</h2>
                <form method="POST" action="{{ route('admin.offer-journeys.support.reconcile') }}" class="mt-3 space-y-3 border border-gray-200 bg-white p-4">
                    @csrf
                    <label class="block text-sm font-medium text-gray-700">Fenetre en jours<input type="number" name="days" value="35" min="1" max="365" class="mt-1 w-full border-gray-300"></label>
                    <label class="flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="dry_run" value="1" checked class="border-gray-300 text-[#647a0b] focus:ring-[#647a0b]">Simulation sans modification</label>
                    <label class="block text-sm font-medium text-gray-700">Motif<input name="reason" required minlength="5" class="mt-1 w-full border-gray-300" placeholder="Controle apres incident"></label>
                    <button class="w-full bg-[#28331f] px-4 py-2 text-sm font-semibold text-white">Placer dans la file</button>
                </form>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Derniers signaux SES</h2>
                <div class="mt-3 overflow-x-auto border border-gray-200 bg-white">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-3 py-2">Date</th><th class="px-3 py-2">Signal</th><th class="px-3 py-2">Destinataire</th><th class="px-3 py-2">Diagnostic</th></tr></thead>
                        <tbody class="divide-y divide-gray-100">
                        @forelse ($recentEvents as $event)
                            <tr><td class="whitespace-nowrap px-3 py-2">{{ $event->occurred_at?->format('d/m/Y H:i') }}</td><td class="px-3 py-2">{{ $event->event_type }}{{ $event->event_subtype ? ' · '.$event->event_subtype : '' }}</td><td class="px-3 py-2">{{ $event->recipient_email ?: 'Non rattache' }}</td><td class="max-w-sm truncate px-3 py-2" title="{{ $event->diagnostic }}">{{ $event->diagnostic }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-5 text-center text-gray-500">Aucun signal recu.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section aria-labelledby="audit-title">
            <h2 id="audit-title" class="text-lg font-semibold text-gray-900">Journal des actions support</h2>
            <div class="mt-3 divide-y divide-gray-100 border border-gray-200 bg-white">
                @forelse ($recentAudits as $audit)
                    <div class="grid gap-1 px-4 py-3 text-sm sm:grid-cols-[170px_180px_1fr]">
                        <span class="text-gray-500">{{ $audit->occurred_at?->format('d/m/Y H:i') }}</span>
                        <strong>{{ $audit->actor?->name ?: 'Systeme' }}</strong>
                        <span>{{ $audit->action }} · {{ $audit->reason }}</span>
                    </div>
                @empty
                    <p class="p-4 text-sm text-gray-500">Aucune action support journalisee.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
