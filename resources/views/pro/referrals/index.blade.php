<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl" style="color:#6B4A3A;">
                Parrainage & invitations
            </h2>
            <span class="text-xs text-gray-500">
                Grandir ensemble
            </span>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto px-4 py-8 space-y-6">

        @if(session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 shadow-sm">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                        <li class="leading-relaxed">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- TOP CARDS --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Share link --}}
            <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Votre lien de parrainage</h3>
                        <p class="text-gray-600 mt-1">
                            Partagez votre lien personnel avec des confrères ou consœurs thérapeutes.
                            Lorsqu’ils s’inscrivent via ce lien, le parrainage est automatiquement associé à votre compte.
                        </p>
                    </div>
                </div>

                <div class="mt-4 flex flex-col sm:flex-row gap-3">
                    <input
                        id="refLink"
                        type="text"
                        class="w-full rounded-xl border-gray-300 focus:border-gray-400 focus:ring-gray-200"
                        value="{{ $shareUrl }}"
                        readonly
                    />

                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-xl px-5 py-3 font-semibold text-white shadow-sm"
                        style="background:#6B4A3A;"
                        onclick="
                            navigator.clipboard.writeText(document.getElementById('refLink').value);
                            this.dataset.originalText = this.dataset.originalText || this.innerText;
                            this.innerText='Copié ✅';
                            setTimeout(()=>this.innerText=this.dataset.originalText, 1200);
                        "
                    >
                        Copier le lien
                    </button>
                </div>

                <div class="mt-4 text-sm text-gray-600">
                    Code : <span class="font-mono text-gray-900">{{ $code->code }}</span>
                </div>

                <div class="mt-5 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-emerald-900">
                    <p class="text-sm leading-relaxed">
                        💚 <span class="font-semibold">En partageant Olithea PRO</span>, vous gagnez un avantage,
                        mais vous aidez aussi directement à faire grandir la plateforme.
                        Chaque recommandation nous permet d’investir davantage dans le produit,
                        d’améliorer les outils, d’ajouter de nouvelles fonctionnalités et de renforcer l’écosystème
                        dédié aux thérapeutes.
                    </p>
                </div>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                        <div class="text-xs text-gray-500">Invitations envoyées</div>
                        <div class="text-lg font-semibold text-gray-900">{{ $invites->count() }}</div>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                        <div class="text-xs text-gray-500">Inscrits</div>
                        <div class="text-lg font-semibold text-gray-900">
                            {{ $referredUsersCount ?? 0 }}
                        </div>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                        <div class="text-xs text-gray-500">Payants</div>
                        <div class="text-lg font-semibold text-gray-900">
                            {{ $referredUsersPaidCount ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Advantage --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Avantage</h3>
                <p class="text-gray-700 mt-2 leading-relaxed">
                    Si un thérapeute invité souscrit à un forfait
                    <span class="font-semibold">équivalent ou supérieur</span> au vôtre,
                    vous bénéficiez d’<span class="font-semibold">un mois offert</span>
                    sur votre licence actuelle.
                </p>

                <div class="mt-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-emerald-900">
                    <div class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Exemple</div>
                    <div class="text-sm mt-1">
                        Vous êtes en <span class="font-semibold">Pro</span> → l’invité souscrit
                        <span class="font-semibold">Pro</span> ou <span class="font-semibold">Premium</span>
                        → <span class="font-semibold">1 mois offert</span>.
                    </div>
                </div>

                <div class="mt-4 text-sm text-gray-500">
                    Un parrainage gagnant-gagnant pour vous et pour la communauté.
                </div>
            </div>
        </div>

        {{-- Invite form --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Inviter un thérapeute</h3>
                <p class="text-gray-600 mt-1">
                    Une invitation personnalisée a souvent beaucoup plus d’impact qu’un simple lien partagé.
                </p>
            </div>

            <form method="POST" action="{{ route('pro.referrals.invite') }}" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf

                <div class="md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="w-full rounded-xl border-gray-300 focus:border-gray-400 focus:ring-gray-200"
                        placeholder="therapeute@email.com"
                        required
                    >
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Message (optionnel)</label>
                    <input
                        type="text"
                        name="message"
                        value="{{ old('message') }}"
                        class="w-full rounded-xl border-gray-300 focus:border-gray-400 focus:ring-gray-200"
                        placeholder="Ex : Je te recommande Olithea PRO pour gérer tes RDV, dossiers clients, factures..."
                    >
                </div>

                <div class="md:col-span-3 flex justify-end">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-xl px-6 py-3 font-semibold text-white shadow-sm"
                        style="background:#6B4A3A;"
                    >
                        Envoyer l’invitation
                    </button>
                </div>
            </form>
        </div>

        {{-- Invites table --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Mes invitations</h3>
                    <div class="text-sm text-gray-500">Suivi : Envoyée → Ouverte → Inscrit → Payant</div>
                </div>
            </div>

            @if($invites->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-center text-gray-600">
                    Aucune invitation pour le moment.
                </div>
            @else
                <div class="overflow-x-auto rounded-xl border border-gray-200">
                    <table class="min-w-full text-sm bg-white">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-gray-600 border-b">
                                <th class="py-3 px-4">Email</th>
                                <th class="py-3 px-4">Statut</th>
                                <th class="py-3 px-4">Créée</th>
                                <th class="py-3 px-4">Expiration</th>
                                <th class="py-3 px-4">Conversion</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($invites as $invite)
                                @php
                                    $statusLabel = [
                                        'sent' => 'Envoyée',
                                        'opened' => 'Ouverte',
                                        'signed_up' => 'Inscrit',
                                        'paid' => 'Payant',
                                        'expired' => 'Expirée',
                                    ][$invite->status] ?? $invite->status;

                                    $statusStyle = match($invite->status) {
                                        'paid' => 'background:#ecfdf5; color:#065f46; border-color:#a7f3d0;',
                                        'signed_up' => 'background:#eff6ff; color:#1e40af; border-color:#bfdbfe;',
                                        'opened' => 'background:#fefce8; color:#854d0e; border-color:#fde68a;',
                                        'expired' => 'background:#fef2f2; color:#991b1b; border-color:#fecaca;',
                                        default => 'background:#f3f4f6; color:#374151; border-color:#e5e7eb;',
                                    };
                                @endphp

                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4 text-gray-900">
                                        <div class="font-medium">{{ $invite->email }}</div>
                                        @if($invite->message)
                                            <div class="text-xs text-gray-500 mt-1 line-clamp-1">{{ $invite->message }}</div>
                                        @endif
                                    </td>

                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                                              style="{{ $statusStyle }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>

                                    <td class="py-3 px-4 text-gray-600">
                                        {{ $invite->created_at?->format('d/m/Y') ?? '—' }}
                                    </td>

                                    <td class="py-3 px-4 text-gray-600">
                                        {{ $invite->expires_at?->format('d/m/Y') ?? '—' }}
                                    </td>

                                    <td class="py-3 px-4 text-gray-600">
                                        {{ $invite->paid_at?->format('d/m/Y') ?? '—' }}
                                    </td>

                                    <td class="py-3 px-4 text-right">
                                        @if(!$invite->isExpired())
                                            <form method="POST" action="{{ route('pro.referrals.resend', $invite) }}" class="inline">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="rounded-xl px-3.5 py-2 border border-gray-200 text-gray-700 hover:bg-gray-100 font-semibold"
                                                >
                                                    Renvoyer
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 text-xs text-gray-500">
                    Astuce : un message personnalisé augmente fortement le taux d’inscription.
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
