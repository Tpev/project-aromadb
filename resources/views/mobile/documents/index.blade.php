@php
    $clientName = fn ($client) => trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? '')) ?: 'Client sans nom';
@endphp

<x-mobile-layout title="Documents clients">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-folder-open text-sm"></i>
                </div>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">Documents clients</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Fichiers partages, PDF a signer et suivi par dossier client.
                </p>
            </div>

            <a href="{{ route('mobile.menu') }}"
               class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-gray-500 shadow-sm"
               aria-label="Retour au menu">
                <i class="fas fa-bars text-xs"></i>
            </a>
        </div>

        <div class="mb-4 grid grid-cols-3 gap-2">
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Clients</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $clients->count() }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Fichiers</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $filesTotal }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">A signer</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $pendingTotal }}</div>
            </div>
        </div>

        <div class="mb-4">
            <label for="mobileDocumentSearch" class="sr-only">Rechercher un client</label>
            <div class="relative">
                <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                <input id="mobileDocumentSearch"
                       type="search"
                       class="h-11 w-full rounded-lg border border-[#e4e8d5] bg-white pl-9 pr-3 text-sm shadow-sm focus:border-[#647a0b] focus:ring-[#647a0b]"
                       placeholder="Rechercher un client">
            </div>
        </div>

        @if($clients->isEmpty())
            <div class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-5 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-user-friends text-sm"></i>
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Aucun client</h2>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Creez une fiche client pour ajouter des fichiers ou des documents a signer.
                </p>
                <a href="{{ route('mobile.clients.create') }}"
                   class="mt-4 inline-flex h-10 items-center justify-center rounded-lg bg-[#647a0b] px-4 text-sm font-semibold text-white shadow-sm">
                    Ajouter un client
                </a>
            </div>
        @else
            <div id="mobileDocumentClientList" class="space-y-2">
                @foreach($clients as $client)
                    @php
                        $name = $clientName($client);
                        $documentsCount = (int) ($documentCounts[$client->id] ?? 0);
                        $pendingCount = (int) ($pendingCounts[$client->id] ?? 0);
                    @endphp

                    <a href="{{ route('mobile.documents.client', $client) }}"
                       class="block rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm active:scale-[0.99]"
                       data-mobile-document-client="{{ strtolower($name . ' ' . ($client->email ?? '') . ' ' . ($client->phone ?? '')) }}">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#647a0b]/10 text-xs font-semibold text-[#647a0b]">
                                {{ strtoupper(mb_substr($client->first_name ?? 'C', 0, 1) . mb_substr($client->last_name ?? '', 0, 1)) ?: 'C' }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <h2 class="truncate text-sm font-semibold text-gray-900">{{ $name }}</h2>
                                        <p class="mt-1 truncate text-xs text-gray-500">{{ $client->email ?: 'Email non renseigne' }}</p>
                                    </div>
                                    @if($pendingCount > 0)
                                        <span class="shrink-0 rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-medium text-amber-700">
                                            {{ $pendingCount }} en cours
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                        {{ $client->client_files_count }} fichier{{ $client->client_files_count > 1 ? 's' : '' }}
                                    </span>
                                    <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                                        {{ $documentsCount }} document{{ $documentsCount > 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const input = document.getElementById('mobileDocumentSearch');
                const items = document.querySelectorAll('[data-mobile-document-client]');

                input?.addEventListener('input', () => {
                    const needle = input.value.trim().toLowerCase();
                    items.forEach((item) => {
                        item.classList.toggle('hidden', needle && !item.dataset.mobileDocumentClient.includes(needle));
                    });
                });
            });
        </script>
    @endpush
</x-mobile-layout>
