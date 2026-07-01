@php
    $publicUrl = $user->slug ? route('therapist.show', $user->slug) : null;
    $aboutText = trim(strip_tags((string) ($user->about ?? '')));
    $completionItems = [
        filled($user->company_name),
        filled($user->company_address),
        filled($user->company_email),
        filled($user->company_phone),
        filled($user->profile_description),
        filled($aboutText),
        count($services) > 0,
        filled($user->profile_picture),
    ];
    $completion = (int) round((collect($completionItems)->filter()->count() / count($completionItems)) * 100);
@endphp

<x-mobile-layout title="Profil">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-user-cog text-sm"></i>
                </div>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">Profil</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Identite publique, coordonnees et prise de RDV.
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

        <section class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-[#647a0b]/10 text-[#647a0b]">
                    @if($user->profile_picture)
                        <img src="{{ asset('storage/' . $user->profile_picture) }}"
                             alt="Photo de profil"
                             class="h-full w-full object-cover">
                    @else
                        <i class="fas fa-user text-lg"></i>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="truncate text-base font-semibold text-gray-900">
                        {{ $user->company_name ?: $user->name }}
                    </h2>
                    <p class="mt-1 truncate text-sm text-gray-600">{{ $user->email }}</p>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <span class="rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $user->visible_annuarire_admin_set ? 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]' : 'border-gray-200 bg-gray-50 text-gray-600' }}">
                            {{ $user->visible_annuarire_admin_set ? 'Visible' : 'Prive' }}
                        </span>
                        <span class="rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $user->accept_online_appointments ? 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
                            RDV {{ $user->accept_online_appointments ? 'actifs' : 'inactifs' }}
                        </span>
                    </div>
                </div>
            </div>

            @if($user->profile_description)
                <p class="mt-3 text-sm leading-snug text-gray-700">
                    {{ $user->profile_description }}
                </p>
            @endif

            <div class="mt-4 grid grid-cols-2 gap-2">
                <a href="{{ route('mobile.profile.edit') }}"
                   class="inline-flex h-10 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-xs font-semibold text-white">
                    Modifier
                </a>
                @if($publicUrl)
                    <a href="{{ $publicUrl }}"
                       class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-xs font-semibold text-gray-700">
                        Voir public
                    </a>
                @else
                    <span class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs font-semibold text-gray-400">
                        Voir public
                    </span>
                @endif
            </div>
        </section>

        <div class="mb-4 grid grid-cols-3 gap-2">
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Completion</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $completion }}%</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Vues</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $user->view_count ?? 0 }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Services</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ count($services) }}</div>
            </div>
        </div>

        @if($publicUrl)
            <section class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Lien public</h2>
                <label class="mt-3 block">
                    <span class="sr-only">Lien public</span>
                    <input id="mobileProfilePublicUrl"
                           type="text"
                           value="{{ $publicUrl }}"
                           readonly
                           class="h-11 w-full rounded-lg border-gray-300 bg-[#f7f8f1] text-sm text-gray-700 focus:border-[#647a0b] focus:ring-[#647a0b]">
                </label>
                <button type="button"
                        class="mt-3 inline-flex h-10 w-full items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-xs font-semibold text-gray-700"
                        onclick="copyMobileProfileUrl(this)">
                    Copier le lien
                </button>
            </section>
        @endif

        <section class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-900">Coordonnees</h2>
            <div class="mt-3 space-y-2">
                <div class="rounded-lg bg-[#f7f8f1] p-3">
                    <div class="text-[11px] font-medium text-gray-500">Adresse</div>
                    <div class="mt-0.5 text-sm font-semibold text-gray-900">{{ $user->company_address ?: 'Non renseignee' }}</div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="rounded-lg bg-[#f7f8f1] p-3">
                        <div class="text-[11px] font-medium text-gray-500">Email pro</div>
                        <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $user->company_email ?: 'Non renseigne' }}</div>
                    </div>
                    <div class="rounded-lg bg-[#f7f8f1] p-3">
                        <div class="text-[11px] font-medium text-gray-500">Telephone</div>
                        <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $user->company_phone ?: 'Non renseigne' }}</div>
                    </div>
                </div>
            </div>

            <div class="mt-3 flex flex-wrap gap-1.5">
                <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                    Adresse {{ $user->share_address_publicly ? 'publique' : 'privee' }}
                </span>
                <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                    Email {{ $user->share_email_publicly ? 'public' : 'prive' }}
                </span>
                <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">
                    Tel {{ $user->share_phone_publicly ? 'public' : 'prive' }}
                </span>
            </div>
        </section>

        <section class="mb-4 rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-900">Prise de RDV</h2>
            <div class="mt-3 grid grid-cols-2 gap-2">
                <div class="rounded-lg bg-[#f7f8f1] p-3">
                    <div class="text-[11px] font-medium text-gray-500">Delai mini</div>
                    <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ (int) ($user->minimum_notice_hours ?? 0) }} h</div>
                </div>
                <div class="rounded-lg bg-[#f7f8f1] p-3">
                    <div class="text-[11px] font-medium text-gray-500">Pause RDV</div>
                    <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ (int) ($user->buffer_time_between_appointments ?? 0) }} min</div>
                </div>
                <div class="rounded-lg bg-[#f7f8f1] p-3">
                    <div class="text-[11px] font-medium text-gray-500">Max / jour</div>
                    <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ $user->global_daily_booking_limit ?: 'Illimite' }}</div>
                </div>
                <div class="rounded-lg bg-[#f7f8f1] p-3">
                    <div class="text-[11px] font-medium text-gray-500">Annulation</div>
                    <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">{{ (int) ($user->cancellation_notice_hours ?? 0) }} h</div>
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-900">Services</h2>
            @if(empty($services))
                <p class="mt-2 text-sm leading-snug text-gray-600">Aucun service affiche sur votre profil public.</p>
            @else
                <div class="mt-3 flex flex-wrap gap-1.5">
                    @foreach($services as $service)
                        <span class="rounded-full bg-[#f5f7eb] px-2 py-1 text-[11px] text-gray-600">{{ $service }}</span>
                    @endforeach
                </div>
            @endif

            <div class="mt-4 grid grid-cols-2 gap-2">
                <a href="{{ route('profile.editCompanyInfo') }}"
                   class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-xs font-semibold text-gray-700">
                    Reglages web
                </a>
                <a href="{{ route('mobile.subscription.index') }}"
                   class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-xs font-semibold text-gray-700">
                    Abonnement
                </a>
            </div>
        </section>
    </div>

    <script>
        function copyMobileProfileUrl(button) {
            const input = document.getElementById('mobileProfilePublicUrl');
            if (!input) return;

            navigator.clipboard?.writeText(input.value);
            const original = button.textContent;
            button.textContent = 'Lien copie';
            window.setTimeout(() => {
                button.textContent = original;
            }, 1600);
        }
    </script>
</x-mobile-layout>
