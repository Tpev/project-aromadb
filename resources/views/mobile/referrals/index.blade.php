@php
    $statusLabels = [
        'sent' => 'Envoyee',
        'opened' => 'Ouverte',
        'signed_up' => 'Inscrit',
        'paid' => 'Payant',
        'expired' => 'Expiree',
    ];

    $statusClasses = [
        'paid' => 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]',
        'signed_up' => 'border-blue-200 bg-blue-50 text-blue-700',
        'opened' => 'border-amber-200 bg-amber-50 text-amber-700',
        'expired' => 'border-red-200 bg-red-50 text-red-700',
        'sent' => 'border-gray-200 bg-gray-50 text-gray-600',
    ];
@endphp

<x-mobile-layout title="Parrainage">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-handshake text-sm"></i>
                </div>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">Parrainage</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Invitez des praticiens et suivez les inscriptions.
                </p>
            </div>

            <a href="{{ route('mobile.menu') }}"
               class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-gray-500 shadow-sm"
               aria-label="Retour au menu">
                <i class="fas fa-bars text-xs"></i>
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-[#d7dfaa] bg-[#647a0b]/10 p-3 text-sm font-medium text-[#4f6108]">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <div class="font-semibold">A corriger</div>
                <ul class="mt-1 list-disc pl-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-4 grid grid-cols-3 gap-2">
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Invites</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $invites->count() }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Inscrits</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $referredUsersCount }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Payants</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $referredUsersPaidCount }}</div>
            </div>
        </div>

        <section class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-link text-xs"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="text-sm font-semibold text-gray-900">Votre lien</h2>
                    <p class="mt-1 text-xs leading-snug text-gray-500">
                        Code {{ $code->code }}
                    </p>
                </div>
            </div>

            <label class="mt-3 block">
                <span class="sr-only">Lien de parrainage</span>
                <input id="mobileReferralLink"
                       type="text"
                       value="{{ $shareUrl }}"
                       readonly
                       class="h-11 w-full rounded-lg border-gray-300 bg-[#f7f8f1] text-sm text-gray-700 focus:border-[#647a0b] focus:ring-[#647a0b]">
            </label>

            <div class="mt-3 grid grid-cols-2 gap-2">
                <button type="button"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-xs font-semibold text-white"
                        onclick="copyMobileReferralLink(this)">
                    Copier
                </button>
                <button type="button"
                        class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-xs font-semibold text-gray-700"
                        onclick="shareMobileReferralLink()">
                    Partager
                </button>
            </div>
        </section>

        <section class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-900">Inviter un praticien</h2>
            <p class="mt-1 text-xs leading-snug text-gray-500">
                Une invitation personnalisee est souvent plus efficace qu un simple lien.
            </p>

            <form method="POST" action="{{ route('mobile.referrals.invite') }}" class="mt-3 space-y-3">
                @csrf

                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Email</span>
                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           inputmode="email"
                           placeholder="therapeute@example.com"
                           class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Message</span>
                    <textarea name="message"
                              rows="3"
                              placeholder="Je te recommande AromaMade PRO pour gerer tes RDV, dossiers clients et factures."
                              class="mt-1 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">{{ old('message') }}</textarea>
                </label>

                <button type="submit"
                        class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-[#647a0b] text-sm font-semibold text-white">
                    Envoyer l invitation
                </button>
            </form>
        </section>

        <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-sm font-semibold text-gray-900">Invitations</h2>
                <a href="{{ route('pro.referrals.index') }}"
                   class="text-xs font-semibold text-[#647a0b]">
                    Vue web
                </a>
            </div>

            @if($invites->isEmpty())
                <div class="mt-3 rounded-lg border border-dashed border-[#d7ddc6] bg-[#fbfcf7] p-4 text-center">
                    <h3 class="text-sm font-semibold text-gray-900">Aucune invitation</h3>
                    <p class="mt-1 text-sm leading-snug text-gray-600">
                        Envoyez une premiere invitation depuis votre mobile.
                    </p>
                </div>
            @else
                <div class="mt-3 space-y-2">
                    @foreach($invites as $invite)
                        @php
                            $isExpired = $invite->isExpired();
                            $status = $isExpired ? 'expired' : ($invite->status ?: 'sent');
                            $statusLabel = $statusLabels[$status] ?? $status;
                            $statusClass = $statusClasses[$status] ?? $statusClasses['sent'];
                        @endphp

                        <article class="rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-gray-900">{{ $invite->email }}</div>
                                    <div class="mt-1 text-xs text-gray-600">
                                        {{ $invite->created_at?->format('d/m/Y') ?: 'Date inconnue' }}
                                        @if($invite->expires_at)
                                            <span class="text-gray-300">/</span>
                                            expire le {{ $invite->expires_at->format('d/m/Y') }}
                                        @endif
                                    </div>
                                </div>
                                <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>

                            @if($invite->message)
                                <p class="mt-2 line-clamp-2 text-xs leading-snug text-gray-600">{{ $invite->message }}</p>
                            @endif

                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <div class="rounded-lg bg-white p-2">
                                    <div class="text-[11px] font-medium text-gray-500">Inscription</div>
                                    <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                                        {{ $invite->signed_up_at ? $invite->signed_up_at->format('d/m/Y') : '-' }}
                                    </div>
                                </div>
                                <div class="rounded-lg bg-white p-2">
                                    <div class="text-[11px] font-medium text-gray-500">Payant</div>
                                    <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                                        {{ $invite->paid_at ? $invite->paid_at->format('d/m/Y') : '-' }}
                                    </div>
                                </div>
                            </div>

                            @unless($isExpired)
                                <form method="POST"
                                      action="{{ route('mobile.referrals.resend', $invite) }}"
                                      class="mt-3">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex h-10 w-full items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                                        Renvoyer
                                    </button>
                                </form>
                            @endunless
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <script>
        function mobileReferralLinkValue() {
            const input = document.getElementById('mobileReferralLink');
            return input ? input.value : '';
        }

        async function copyMobileReferralLink(button) {
            const value = mobileReferralLinkValue();
            if (!value) return;

            try {
                await navigator.clipboard.writeText(value);
                const original = button.textContent;
                button.textContent = 'Copie';
                setTimeout(() => { button.textContent = original; }, 1200);
            } catch (error) {
                const input = document.getElementById('mobileReferralLink');
                if (input) {
                    input.focus();
                    input.select();
                }
            }
        }

        async function shareMobileReferralLink() {
            const value = mobileReferralLinkValue();
            if (!value) return;

            if (navigator.share) {
                await navigator.share({
                    title: 'AromaMade PRO',
                    text: 'Je te recommande AromaMade PRO pour ton activite.',
                    url: value,
                });
            } else {
                const input = document.getElementById('mobileReferralLink');
                if (input) {
                    input.focus();
                    input.select();
                }
            }
        }
    </script>
</x-mobile-layout>
