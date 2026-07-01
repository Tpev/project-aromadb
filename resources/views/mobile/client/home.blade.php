<x-mobile-client-layout title="Accueil client">
    <div class="mx-auto max-w-lg space-y-5 px-4 py-5">
        <section class="space-y-3">
            <span class="inline-flex items-center rounded-full bg-[#f7faef] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#647a0b]">
                Espace prive
            </span>
            <div class="flex items-end justify-between gap-4">
                <div class="min-w-0">
                    <h1 class="truncate text-2xl font-bold tracking-tight text-gray-900">
                        Bonjour {{ $clientProfile->first_name ?: 'client' }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">Vos prochains echanges et documents au meme endroit.</p>
                </div>
            </div>
        </section>

        @if(session('success'))
            <div class="rounded-xl border border-[#dfe8c8] bg-[#f7faef] px-4 py-3 text-sm font-semibold text-[#4f6508]">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <section class="grid grid-cols-3 gap-3">
            <a href="{{ route('mobile.client.messages.index') }}" class="rounded-2xl border border-[#e4e8d5] bg-white p-3 shadow-sm">
                <p class="text-xl font-bold text-gray-900">{{ $messages->count() }}</p>
                <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-gray-500">Messages</p>
            </a>
            <a href="{{ route('mobile.client.communities.index') }}" class="rounded-2xl border border-[#e4e8d5] bg-white p-3 shadow-sm">
                <p class="text-xl font-bold text-gray-900">{{ $activeCommunitiesCount }}</p>
                <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-gray-500">Groupes</p>
            </a>
            <a href="{{ route('mobile.client.communities.index') }}" class="rounded-2xl border border-[#e4e8d5] bg-white p-3 shadow-sm">
                <p class="text-xl font-bold text-gray-900">{{ $pendingInvitesCount }}</p>
                <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-gray-500">Invites</p>
            </a>
        </section>

        <section class="space-y-3 rounded-2xl border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-gray-900">Messages recents</h2>
                    <p class="text-xs text-gray-500">Conversation avec votre praticien.</p>
                </div>
                <a href="{{ route('mobile.client.messages.index') }}" class="rounded-lg bg-[#f7faef] px-3 py-2 text-xs font-semibold text-[#647a0b]">
                    Ouvrir
                </a>
            </div>

            <div class="space-y-2">
                @forelse($messages as $message)
                    <div class="rounded-xl {{ $message->sender_type === 'client' ? 'bg-[#f7faef]' : 'bg-gray-50' }} px-3 py-2">
                        <div class="flex items-center justify-between gap-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-gray-500">
                            <span>{{ $message->sender_type === 'client' ? 'Vous' : 'Praticien' }}</span>
                            <span>{{ $message->created_at?->format('d/m H:i') }}</span>
                        </div>
                        <p class="mt-1 text-sm leading-6 text-gray-800">{{ \Illuminate\Support\Str::limit($message->content, 120) }}</p>
                    </div>
                @empty
                    <p class="rounded-xl bg-gray-50 px-3 py-4 text-sm text-gray-500">Aucun message pour le moment.</p>
                @endforelse
            </div>
        </section>

        <section id="documents" class="space-y-4 rounded-2xl border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <div>
                <h2 class="text-base font-bold text-gray-900">Documents</h2>
                <p class="text-xs text-gray-500">Envoyez un fichier ou retrouvez vos documents partages.</p>
            </div>

            <form method="POST" action="{{ route('mobile.client.files.store') }}" enctype="multipart/form-data" class="space-y-3 rounded-xl bg-[#fafbf7] p-3 ring-1 ring-[#eef1e5]">
                @csrf
                <label for="document" class="text-sm font-semibold text-gray-800">Ajouter un document</label>
                <input id="document" name="document" type="file" required class="block w-full rounded-xl border border-gray-300 bg-white text-sm file:mr-3 file:border-0 file:bg-[#647a0b] file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
                <x-input-error :messages="$errors->get('document')" class="mt-2" />
                <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center rounded-xl bg-[#647a0b] px-4 py-2 text-sm font-semibold text-white">
                    Envoyer le document
                </button>
            </form>

            <div class="space-y-2">
                @forelse($clientFiles as $file)
                    <a href="{{ route('mobile.client.files.download', $file) }}" class="flex items-center justify-between gap-3 rounded-xl bg-gray-50 px-3 py-3 text-sm">
                        <span class="min-w-0 truncate font-semibold text-gray-800">{{ $file->original_name }}</span>
                        <span class="shrink-0 text-xs font-semibold text-[#647a0b]">Telecharger</span>
                    </a>
                @empty
                    <p class="rounded-xl bg-gray-50 px-3 py-4 text-sm text-gray-500">Aucun document disponible.</p>
                @endforelse
            </div>
        </section>

        <section class="space-y-3 rounded-2xl border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <h2 class="text-base font-bold text-gray-900">Rendez-vous a venir</h2>
            <div class="space-y-2">
                @forelse($appointments as $appointment)
                    <div class="rounded-xl bg-gray-50 px-3 py-3">
                        <p class="text-sm font-semibold text-gray-900">{{ $appointment->appointment_date?->format('d/m/Y H:i') }}</p>
                        <p class="mt-1 text-xs text-gray-500">Avec {{ $appointment->user->name ?? 'votre praticien' }}</p>
                    </div>
                @empty
                    <p class="rounded-xl bg-gray-50 px-3 py-4 text-sm text-gray-500">Aucun rendez-vous prevu.</p>
                @endforelse
            </div>
        </section>

        <section class="space-y-3 rounded-2xl border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <h2 class="text-base font-bold text-gray-900">Factures</h2>
            <div class="space-y-2">
                @forelse($invoices as $invoice)
                    <a href="{{ route('mobile.client.invoices.pdf', $invoice) }}" class="flex items-center justify-between gap-3 rounded-xl bg-gray-50 px-3 py-3 text-sm">
                        <span class="min-w-0">
                            <span class="block truncate font-semibold text-gray-800">Facture {{ $invoice->invoice_number ?? '#' . $invoice->id }}</span>
                            <span class="block text-xs text-gray-500">{{ $invoice->created_at?->format('d/m/Y') }}</span>
                        </span>
                        <span class="shrink-0 text-xs font-semibold text-[#647a0b]">PDF</span>
                    </a>
                @empty
                    <p class="rounded-xl bg-gray-50 px-3 py-4 text-sm text-gray-500">Aucune facture disponible.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-mobile-client-layout>
