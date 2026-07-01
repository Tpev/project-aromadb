@php
    $clientCount = $audience->clients_count ?? $audience->clients->count();
@endphp

<x-mobile-layout :title="$audience->name">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4">
            <a href="{{ route('mobile.audiences.index') }}" class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Audiences
            </a>

            <div class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                        <i class="fas fa-users text-sm"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h1 class="text-xl font-semibold leading-tight text-gray-900">
                            {{ $audience->name }}
                        </h1>
                        <p class="mt-1 line-clamp-2 text-sm leading-snug text-gray-600">
                            {{ $audience->description ?: 'Audience sans description' }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2">
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Contacts</div>
                        <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $clientCount }}</div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Statut</div>
                        <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                            {{ $clientCount > 0 ? 'Prete' : 'Vide' }}
                        </div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-2">
                        <div class="text-[11px] font-medium text-gray-500">Creee</div>
                        <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                            {{ $audience->created_at ? $audience->created_at->format('d/m') : '-' }}
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <a href="{{ route('mobile.audiences.edit', $audience) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        <i class="fas fa-edit mr-1.5 text-[11px]"></i>
                        Modifier
                    </a>
                    <a href="{{ route('audiences.edit', $audience) }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                        <i class="fas fa-external-link-alt mr-1.5 text-[10px]"></i>
                        Vue web
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-[#d7dfaa] bg-[#647a0b]/10 p-3 text-sm font-medium text-[#4f6108]">
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-4">
            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-gray-900">Contacts de l audience</h2>
                    <a href="{{ route('mobile.audiences.edit', $audience) }}"
                       class="text-xs font-semibold text-[#647a0b]">
                        Gerer
                    </a>
                </div>

                @if($audience->clients->isEmpty())
                    <div class="mt-3 rounded-lg border border-dashed border-[#d7ddc6] bg-[#fbfcf7] p-4 text-center">
                        <h3 class="text-sm font-semibold text-gray-900">Audience vide</h3>
                        <p class="mt-1 text-sm leading-snug text-gray-600">
                            Ajoutez des clients pour utiliser cette liste dans une newsletter.
                        </p>
                    </div>
                @else
                    <div class="mt-3 space-y-2">
                        @foreach($audience->clients as $client)
                            @php
                                $clientName = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''));
                            @endphp

                            <a href="{{ route('mobile.clients.show', $client) }}"
                               class="block rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3 active:scale-[0.99]">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-semibold text-gray-900">
                                            {{ $clientName ?: 'Client #' . $client->id }}
                                        </div>
                                        <div class="mt-1 truncate text-xs text-gray-600">
                                            {{ $client->email ?: ($client->phone ?: 'Coordonnees manquantes') }}
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right mt-1 text-[10px] text-gray-300"></i>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Utilisation</h2>
                <p class="mt-2 text-sm leading-snug text-gray-600">
                    Cette audience apparait dans le module newsletters pour choisir les destinataires.
                </p>
                <a href="{{ route('mobile.newsletters.index') }}"
                   class="mt-3 inline-flex h-10 w-full items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                    Ouvrir les newsletters
                </a>
            </section>

            <form method="POST"
                  action="{{ route('mobile.audiences.destroy', $audience) }}"
                  onsubmit="return confirm('Supprimer cette audience ? Les clients ne seront pas supprimes.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-red-100 bg-red-50 text-sm font-semibold text-red-600">
                    <i class="fas fa-trash-alt mr-1.5 text-[11px]"></i>
                    Supprimer l audience
                </button>
            </form>
        </div>
    </div>
</x-mobile-layout>
