<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            Avis Google & Témoignages
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Carte connexion Google --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-2xl font-semibold text-[#647a0b] mb-4 flex items-center gap-2">
                    <i class="fab fa-google text-[#854f38]"></i>
                    Connexion à Google Business Profile
                </h3>

                @if (! $account)
                    <p class="text-gray-700 mb-4">
                        Connectez votre compte Google Business Profile pour importer automatiquement
                        vos avis Google et les afficher sur votre page publique AromaMade.
                    </p>

                    <a href="{{ route('pro.google-reviews.connect') }}"
                       class="inline-flex items-center px-4 py-2 rounded-full bg-[#647a0b] text-white font-semibold hover:bg-[#8ea633] transition">
                        <i class="fab fa-google mr-2"></i>
                        Connecter mon compte Google
                    </a>
                @else
                    <div class="space-y-2 mb-4">
                        <p class="text-gray-700">
                            <span class="font-semibold">Compte :</span>
                            {{ $account->account_display_name ?? 'N/A' }}
                        </p>
                        <p class="text-gray-700">
                            <span class="font-semibold">Établissement :</span>
                            {{ $account->location_title ?? 'N/A' }}
                        </p>
                        <p class="text-gray-500 text-sm">
                            <span class="font-semibold">Dernière synchronisation :</span>
                            {{ $account->last_synced_at ? $account->last_synced_at->format('d/m/Y H:i') : 'Jamais' }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <form method="POST" action="{{ route('pro.google-reviews.sync') }}">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 rounded-full bg-[#647a0b] text-white font-semibold hover:bg-[#8ea633] transition">
                                <i class="fas fa-sync-alt mr-2"></i>
                                Synchroniser mes avis Google
                            </button>
                        </form>

                        <form method="POST" action="{{ route('pro.google-reviews.disconnect') }}"
                              onsubmit="return confirm('Supprimer la connexion Google ? Les avis déjà importés resteront en place.');">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 rounded-full bg-red-100 text-red-700 font-semibold hover:bg-red-200 transition">
                                <i class="fas fa-unlink mr-2"></i>
                                Supprimer la connexion
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            {{-- Liste des avis Google importés --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-2xl font-semibold text-[#647a0b] mb-4 flex items-center gap-2">
                    <i class="fas fa-comments text-[#854f38]"></i>
                    Avis Google importés
                </h3>

                @if ($googleTestimonials->count() === 0)
                    <p class="text-gray-600">
                        Aucun avis Google importé pour le moment.
                        Une fois la connexion établie, cliquez sur
                        <span class="font-semibold">“Synchroniser mes avis Google”</span>.
                    </p>
                @else
                    <div class="space-y-4">
                        @foreach ($googleTestimonials as $testimonial)
                            <div class="border-l-4 border-[#8ea633] bg-[#f9fafb] rounded-md p-4 flex gap-3">
                                @if ($testimonial->reviewer_profile_photo_url)
                                    <img src="{{ $testimonial->reviewer_profile_photo_url }}"
                                         alt="{{ $testimonial->reviewer_name }}"
                                         class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-[#8ea633] text-white flex items-center justify-center flex-shrink-0">
                                        <span class="text-sm font-bold">
                                            {{ strtoupper(substr($testimonial->reviewer_name ?? 'G', 0, 1)) }}
                                        </span>
                                    </div>
                                @endif

                                <div class="flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-gray-800">
                                                {{ $testimonial->reviewer_name ?? 'Client Google' }}
                                            </span>
                                            <span class="text-xs bg-[#e5f0c8] text-[#647a0b] px-2 py-0.5 rounded-full">
                                                Avis Google
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1 text-[#f6b400]">
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($testimonial->rating && $i <= $testimonial->rating)
                                                    <i class="fas fa-star text-xs"></i>
                                                @else
                                                    <i class="far fa-star text-xs text-gray-300"></i>
                                                @endif
                                            @endfor
                                        </div>
                                    </div>

                                    <p class="mt-2 text-gray-700 whitespace-pre-line">
                                        {{ $testimonial->testimonial }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $testimonial->external_created_at?->format('d/m/Y') ?? $testimonial->created_at->format('d/m/Y') }}
                                    </p>

                                    @if ($testimonial->owner_reply)
                                        <div class="mt-3 pl-3 border-l border-gray-300">
                                            <p class="text-xs uppercase text-gray-500 mb-1">
                                                Réponse AromaMade
                                            </p>
                                            <p class="text-sm text-gray-700 whitespace-pre-line">
                                                {{ $testimonial->owner_reply }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    @endpush
</x-app-layout>
