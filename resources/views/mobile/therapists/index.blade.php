{{-- resources/views/mobile/therapists/index.blade.php --}}
<x-mobile-layout title="Rechercher un praticien">
    {{-- SEO --}}
    @section('title', 'Trouver un praticien | AromaMade')
    @section('meta_description')
Trouvez rapidement un praticien en médecines douces depuis l’app AromaMade : recherchez par nom, spécialité ou lieu et prenez rendez-vous en quelques taps.
    @endsection

    <div
        class="min-h-screen flex flex-col items-center justify-center px-6 py-10"
        style="background: radial-gradient(circle at top, #fffaf3 0, #f7f4ec 40%, #eee7dc 100%);"
    >
        <div class="w-full max-w-md space-y-8">

            {{-- Intro / hero --}}
            <div class="text-center space-y-3 px-2">
                <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-medium bg-primary-50 text-primary-700"
                >
                    <i class="fas fa-search mr-1.5 text-[10px]"></i>
                    {{ __('Recherche de praticien') }}
                </span>

                <h1 class="text-[22px] font-extrabold text-gray-900 tracking-tight">
                    {{ __('Trouvez votre praticien en quelques secondes') }}
                </h1>

                <p class="text-[13px] text-gray-600 leading-relaxed">
                    {{ __('Cherchez par nom, spécialité ou lieu pour trouver un praticien en cabinet, à domicile ou en visio.') }}
                </p>
            </div>

            {{-- Search card --}}
            <x-ts-card class="rounded-3xl shadow-lg border border-primary-50 px-4 py-5">
                <form
                    method="POST"
                    action="{{ route('mobile.therapists.search') }}"
                    class="space-y-4"
                >
                    @csrf

                    {{-- Nom / cabinet --}}
                    <x-ts-input
                        name="name"
                        label="Nom du praticien ou du cabinet"
                        placeholder="Ex : Marie Dupont, Cabinet Harmonie"
                        icon="user"
                        :value="old('name', request('name'))"
                        class="text-[13px]"
                    />

                    {{-- Spécialité --}}
                    <x-ts-input
                        name="specialty"
                        label="Spécialité"
                        placeholder="Ex : Naturopathie, Sophrologie"
                        icon="sparkles"
                        :value="old('specialty', request('specialty'))"
                        class="text-[13px]"
                    />

                    {{-- Lieu --}}
                    <x-ts-input
                        name="location"
                        label="Ville ou région"
                        placeholder="Ex : Strasbourg, Alsace"
                        icon="map-pin"
                        :value="old('location', request('location'))"
                        class="text-[13px]"
                    />

                    {{-- CTA --}}
                    <div class="pt-2">
                        <x-ts-button
                            type="submit"
                            color="primary"
                            class="w-full !text-[13px] !py-2.5"
                            icon="magnifying-glass"
                            position="left"
                        >
                            {{ __('Rechercher des praticiens') }}
                        </x-ts-button>
                    </div>

                    {{-- Helper line --}}
                    <div class="flex items-center gap-1.5 mt-1 text-[11px] text-gray-500">
                        <i class="fas fa-lightbulb text-[10px] text-secondary-500"></i>
                        <span>
                            {{ __('Astuce : combinez spécialité + ville pour affiner les résultats.') }}
                        </span>
                    </div>
                </form>
            </x-ts-card>

            {{-- Small reassurance block --}}
            <x-ts-card class="rounded-2xl border-0 bg-white/80 shadow-md px-4 py-3 space-y-2">
                <div class="flex items-center gap-2">
                    <div class="inline-flex items-center justify-center w-7 h-7 rounded-2xl bg-primary-50">
                        <i class="fas fa-shield-heart text-[11px] text-primary-700"></i>
                    </div>
                    <p class="text-[12px] font-semibold text-gray-800">
                        {{ __('Avec AromaMade, vous restez entre de bonnes mains 🤍') }}
                    </p>
                </div>

                <ul class="pl-9 space-y-0.5 text-[11px] text-gray-600">
                    <li>• {{ __('Praticiens vérifiés par notre équipe') }}</li>
                    <li>• {{ __('Rendez-vous en cabinet, visio ou à domicile') }}</li>
                    <li>• {{ __('Aucune commission sur vos consultations') }}</li>
                </ul>
            </x-ts-card>

            {{-- Tiny footer info --}}
            <p class="text-[11px] text-gray-500 text-center leading-relaxed px-4">
                {{ __('Vous pourrez revenir à cet écran à tout moment depuis le menu de l’application.') }}
            </p>
        </div>
    </div>
</x-mobile-layout>
