@php
    $reviewsCount = $googleTestimonials->count();
    $ratedReviews = $googleTestimonials->whereNotNull('rating');
    $averageRating = $ratedReviews->count() > 0 ? round((float) $ratedReviews->avg('rating'), 1) : null;
@endphp

<x-mobile-layout title="Avis Google">
    <div class="mx-auto w-full max-w-lg px-4 pb-24 pt-4">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#647a0b]/10 text-[#647a0b]">
                    <i class="fas fa-star text-sm"></i>
                </div>
                <h1 class="text-xl font-semibold leading-tight text-gray-900">Avis Google</h1>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Connexion Business Profile, synchronisation et avis importes.
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

        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-4 grid grid-cols-3 gap-2">
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Connexion</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $account ? 'Oui' : 'Non' }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Avis</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $reviewsCount }}</div>
            </div>
            <div class="min-h-[68px] rounded-lg border border-[#e4e8d5] bg-white p-2 shadow-sm">
                <div class="text-[11px] font-medium leading-tight text-gray-500">Moyenne</div>
                <div class="mt-1 truncate text-base font-semibold text-gray-900">{{ $averageRating ? number_format($averageRating, 1, ',', ' ') . '/5' : '-' }}</div>
            </div>
        </div>

        <div class="space-y-4">
            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-gray-900">Google Business</h2>
                    <span class="rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $account ? 'border-[#647a0b]/20 bg-[#647a0b]/10 text-[#647a0b]' : 'border-gray-200 bg-gray-50 text-gray-600' }}">
                        {{ $account ? 'Connecte' : 'A connecter' }}
                    </span>
                </div>

                @if(! $account)
                    <p class="mt-3 text-sm leading-snug text-gray-600">
                        Connectez votre fiche Google Business Profile pour importer vos avis et les afficher sur votre page publique.
                    </p>

                    <div class="mt-4 grid grid-cols-1 gap-2">
                        <a href="{{ route('mobile.google-reviews.connect') }}"
                           class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] px-3 text-sm font-semibold text-white shadow-sm active:scale-[0.99]">
                            <i class="fab fa-google mr-2 text-xs"></i>
                            Connecter Google
                        </a>
                        <a href="{{ route('pro.google-reviews.index') }}"
                           class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm active:scale-[0.99]">
                            Vue web
                        </a>
                    </div>
                @else
                    <div class="mt-3 space-y-2">
                        <div class="rounded-lg bg-[#f7f8f1] p-3">
                            <div class="text-[11px] font-medium text-gray-500">Compte</div>
                            <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                                {{ $account->account_display_name ?: 'Compte Google Business' }}
                            </div>
                        </div>
                        <div class="rounded-lg bg-[#f7f8f1] p-3">
                            <div class="text-[11px] font-medium text-gray-500">Etablissement</div>
                            <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                                {{ $account->location_title ?: 'Etablissement non renseigne' }}
                            </div>
                        </div>
                        <div class="rounded-lg bg-[#f7f8f1] p-3">
                            <div class="text-[11px] font-medium text-gray-500">Derniere synchronisation</div>
                            <div class="mt-0.5 truncate text-sm font-semibold text-gray-900">
                                {{ $account->last_synced_at ? $account->last_synced_at->format('d/m/Y H:i') : 'Jamais' }}
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('mobile.google-reviews.sync') }}" class="mt-4 space-y-3 rounded-lg bg-[#f7f8f1] p-3">
                        @csrf

                        @if(!empty($availableLocations) && count($availableLocations) > 1)
                            @php
                                $currentSelection = old('location_selection', ($account->account_id && $account->location_id)
                                    ? ($account->account_id . '|' . $account->location_id)
                                    : null);
                            @endphp

                            <label class="block">
                                <span class="text-sm font-medium text-gray-700">Etablissement</span>
                                <select name="location_selection"
                                        class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                                    @foreach($availableLocations as $location)
                                        <option value="{{ $location['selection_value'] }}" {{ (string) $currentSelection === (string) $location['selection_value'] ? 'selected' : '' }}>
                                            {{ $location['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                        @endif

                        <button type="submit"
                                class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-[#647a0b] text-xs font-semibold text-white">
                            <i class="fas fa-sync-alt mr-1.5 text-[11px]"></i>
                            Synchroniser les avis
                        </button>
                    </form>

                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <a href="{{ route('pro.google-reviews.index') }}"
                           class="inline-flex h-10 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-xs font-semibold text-gray-700">
                            <i class="fas fa-external-link-alt mr-1.5 text-[10px]"></i>
                            Vue web
                        </a>
                        <form method="POST"
                              action="{{ route('mobile.google-reviews.disconnect') }}"
                              onsubmit="return confirm('Supprimer la connexion Google ? Les avis importes resteront visibles.');">
                            @csrf
                            <button type="submit"
                                    class="inline-flex h-10 w-full items-center justify-center rounded-lg border border-red-100 bg-red-50 text-xs font-semibold text-red-600">
                                <i class="fas fa-unlink mr-1.5 text-[10px]"></i>
                                Deconnecter
                            </button>
                        </form>
                    </div>
                @endif
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-gray-900">Avis importes</h2>
                    <span class="rounded-full bg-[#f7f8f1] px-2 py-1 text-[11px] text-gray-600">
                        {{ $reviewsCount }} avis
                    </span>
                </div>

                @if($googleTestimonials->isEmpty())
                    <div class="mt-3 rounded-lg border border-dashed border-[#d7ddc6] bg-[#fbfcf7] p-4 text-center">
                        <h3 class="text-sm font-semibold text-gray-900">Aucun avis importe</h3>
                        <p class="mt-1 text-sm leading-snug text-gray-600">
                            Une fois Google connecte, lancez une synchronisation pour recuperer les derniers avis.
                        </p>
                    </div>
                @else
                    <div class="mt-3 space-y-2">
                        @foreach($googleTestimonials as $testimonial)
                            <article class="rounded-lg border border-[#f1f3e6] bg-[#fbfcf7] p-3">
                                <div class="flex items-start gap-3">
                                    @if($testimonial->reviewer_profile_photo_url)
                                        <img src="{{ $testimonial->reviewer_profile_photo_url }}"
                                             alt=""
                                             class="h-10 w-10 shrink-0 rounded-lg object-cover">
                                    @else
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#647a0b] text-sm font-semibold text-white">
                                            {{ strtoupper(mb_substr($testimonial->reviewer_name ?? 'G', 0, 1)) }}
                                        </div>
                                    @endif

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <div class="truncate text-sm font-semibold text-gray-900">
                                                    {{ $testimonial->reviewer_name ?: 'Client Google' }}
                                                </div>
                                                <div class="mt-0.5 text-[11px] text-gray-500">
                                                    {{ $testimonial->external_created_at?->format('d/m/Y') ?? $testimonial->created_at?->format('d/m/Y') }}
                                                </div>
                                            </div>

                                            <span class="shrink-0 rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-[#647a0b]">
                                                {{ $testimonial->rating ? $testimonial->rating . '/5' : '-' }}
                                            </span>
                                        </div>

                                        <div class="mt-2 flex gap-0.5 text-[#f6b400]">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="{{ $testimonial->rating && $i <= $testimonial->rating ? 'fas' : 'far' }} fa-star text-[11px] {{ ! $testimonial->rating || $i > $testimonial->rating ? 'text-gray-300' : '' }}"></i>
                                            @endfor
                                        </div>

                                        <p class="mt-2 whitespace-pre-line text-sm leading-snug text-gray-700">
                                            {{ $testimonial->testimonial }}
                                        </p>

                                        @if($testimonial->owner_reply)
                                            <div class="mt-3 rounded-lg bg-white p-3">
                                                <div class="text-[11px] font-semibold text-gray-500">Reponse</div>
                                                <p class="mt-1 whitespace-pre-line text-xs leading-snug text-gray-700">
                                                    {{ $testimonial->owner_reply }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-mobile-layout>
