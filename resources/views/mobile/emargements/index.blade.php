@php
    $statusLabel = fn (?string $status) => match ($status) {
        'pending' => 'En attente',
        'signed' => 'Signe',
        'expired' => 'Expire',
        default => 'A envoyer',
    };
    $statusClass = fn (?string $status) => match ($status) {
        'signed' => 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]',
        'pending' => 'border-amber-200 bg-amber-50 text-amber-700',
        'expired' => 'border-red-200 bg-red-50 text-red-700',
        default => 'border-gray-200 bg-gray-50 text-gray-600',
    };
@endphp

<x-mobile-layout title="Emargements">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-signature text-sm"></i>
                </div>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">Emargements</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Feuilles de presence liees aux rendez-vous et preuves signees.
                </p>
            </div>

            <a href="{{ route('mobile.menu') }}"
               class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-gray-500 shadow-sm"
               aria-label="Retour au menu">
                <i class="fas fa-bars text-xs"></i>
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-[#647a0b]/20 bg-[#647a0b]/10 px-3 py-2 text-sm font-semibold text-[#647a0b]">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-4 grid grid-cols-3 gap-2">
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Requis</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $totalRequired }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">A suivre</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $pendingTotal }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Signes</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $signedTotal }}</div>
            </div>
        </div>

        <div class="mb-4">
            <label for="mobileEmargementSearch" class="sr-only">Rechercher</label>
            <div class="relative">
                <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                <input id="mobileEmargementSearch"
                       type="search"
                       class="h-11 w-full rounded-lg border border-[#e4e8d5] bg-white pl-9 pr-3 text-sm shadow-sm focus:border-[#647a0b] focus:ring-[#647a0b]"
                       placeholder="Client, prestation, email">
            </div>
        </div>

        @if($rows->isEmpty())
            <div class="rounded-lg border border-dashed border-[#d7ddc6] bg-white p-5 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-calendar-check text-sm"></i>
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Aucun emargement a suivre</h2>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Les rendez-vous avec une prestation qui demande un emargement apparaitront ici.
                </p>
                <a href="{{ route('mobile.appointments.index') }}"
                   class="mt-4 inline-flex h-10 items-center justify-center rounded-lg bg-[#647a0b] px-4 text-sm font-semibold text-white shadow-sm">
                    Voir les rendez-vous
                </a>
            </div>
        @else
            <div id="mobileEmargementList" class="space-y-2">
                @foreach($rows as $row)
                    @php
                        $appointment = $row['appointment'];
                        $emargement = $row['emargement'];
                        $status = $emargement?->status;
                        $appointmentDate = $appointment->appointment_date;
                        $search = strtolower(implode(' ', [
                            $row['clientName'],
                            $row['clientEmail'],
                            $appointment->product?->name,
                            optional($appointmentDate)->format('d/m/Y H:i'),
                        ]));
                    @endphp

                    <article class="rounded-lg border border-[#e4e8d5] bg-white p-3 shadow-sm"
                             data-mobile-emargement-card="{{ $search }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $statusClass($status) }}">
                                        {{ $statusLabel($status) }}
                                    </span>
                                    @if($emargement?->isExpired())
                                        <span class="rounded-full border border-red-200 bg-red-50 px-2 py-0.5 text-[10px] font-medium text-red-700">
                                            Lien expire
                                        </span>
                                    @endif
                                </div>

                                <h2 class="mt-2 break-words text-sm font-semibold text-gray-900">
                                    {{ $row['clientName'] }}
                                </h2>
                                <p class="mt-1 line-clamp-2 text-xs leading-snug text-gray-600">
                                    {{ $appointment->product?->name ?: 'Prestation non renseignee' }}
                                </p>
                            </div>

                            <div class="shrink-0 text-right">
                                <div class="text-[11px] font-semibold text-gray-900">
                                    {{ optional($appointmentDate)->format('d/m') ?: '--' }}
                                </div>
                                <div class="text-[10px] text-gray-500">
                                    {{ optional($appointmentDate)->format('H:i') ?: '' }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Email</div>
                                <div class="mt-0.5 truncate text-xs font-semibold text-gray-900">
                                    {{ $row['clientEmail'] ?: 'Manquant' }}
                                </div>
                            </div>
                            <div class="rounded-lg bg-[#f7f8f1] p-2">
                                <div class="text-[11px] font-medium text-gray-500">Expiration</div>
                                <div class="mt-0.5 truncate text-xs font-semibold text-gray-900">
                                    {{ $emargement?->expires_at?->format('d/m/Y') ?: '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <a href="{{ route('mobile.appointments.show', $appointment) }}"
                               class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-xs font-semibold text-gray-700 shadow-sm">
                                Rendez-vous
                            </a>

                            @if($row['canDownload'])
                                <a href="{{ route('mobile.emargements.download', $emargement) }}"
                                   class="inline-flex h-10 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-xs font-semibold text-white shadow-sm">
                                    Preuve PDF
                                </a>
                            @elseif($row['canSend'])
                                <form action="{{ route('mobile.emargements.send', $appointment) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-[#647a0b] px-3 text-xs font-semibold text-white shadow-sm">
                                        Envoyer
                                    </button>
                                </form>
                            @elseif($row['canResend'])
                                <form action="{{ route('mobile.emargements.resend', $emargement) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-[#647a0b] px-3 text-xs font-semibold text-white shadow-sm">
                                        Renvoyer
                                    </button>
                                </form>
                            @else
                                <span class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-xs font-semibold text-gray-400">
                                    Aucune action
                                </span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const input = document.getElementById('mobileEmargementSearch');
                const items = document.querySelectorAll('[data-mobile-emargement-card]');

                input?.addEventListener('input', () => {
                    const needle = input.value.trim().toLowerCase();
                    items.forEach((item) => {
                        item.classList.toggle('hidden', needle && !item.dataset.mobileEmargementCard.includes(needle));
                    });
                });
            });
        </script>
    @endpush
</x-mobile-layout>
