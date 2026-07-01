@php
    $servicesText = old('services_text', implode("\n", $services));
    $checked = fn (string $field, bool $default = false) => (bool) old($field, $default);
@endphp

<x-mobile-layout title="Modifier le profil" :hide-nav="true">
    <form method="POST" action="{{ route('mobile.profile.update') }}" class="mx-auto w-full max-w-lg px-4 pb-28 pt-4">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <a href="{{ route('mobile.profile.index') }}"
               class="mb-2 inline-flex items-center text-xs font-semibold text-[#647a0b]">
                <i class="fas fa-arrow-left mr-1 text-[10px]"></i>
                Profil
            </a>
            <h1 class="text-xl font-semibold leading-tight text-gray-900">Modifier le profil</h1>
            <p class="mt-1 text-sm leading-snug text-gray-600">
                Les informations essentielles visibles par vos clients.
            </p>
        </div>

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

        <div class="space-y-4">
            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Compte</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Nom utilisateur</span>
                        <input type="text"
                               name="name"
                               value="{{ old('name', $user->name) }}"
                               required
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Email de connexion</span>
                        <input type="email"
                               name="email"
                               value="{{ old('email', $user->email) }}"
                               required
                               inputmode="email"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Entreprise</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Nom public</span>
                        <input type="text"
                               name="company_name"
                               value="{{ old('company_name', $user->company_name) }}"
                               placeholder="Cabinet, marque ou nom pro"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Adresse</span>
                        <textarea name="company_address"
                                  rows="3"
                                  class="mt-1 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">{{ old('company_address', $user->company_address) }}</textarea>
                    </label>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="block min-w-0">
                            <span class="text-sm font-medium text-gray-700">Email pro</span>
                            <input type="email"
                                   name="company_email"
                                   value="{{ old('company_email', $user->company_email) }}"
                                   inputmode="email"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>

                        <label class="block min-w-0">
                            <span class="text-sm font-medium text-gray-700">Telephone</span>
                            <input type="tel"
                                   name="company_phone"
                                   value="{{ old('company_phone', $user->company_phone) }}"
                                   inputmode="tel"
                                   class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                        </label>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Profil public</h2>

                <div class="mt-3 space-y-3">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Titre et specialites</span>
                        <textarea name="profile_description"
                                  rows="3"
                                  maxlength="1000"
                                  placeholder="Aromatherapeute, naturopathe, accompagnement..."
                                  class="mt-1 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">{{ old('profile_description', $user->profile_description) }}</textarea>
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">A propos</span>
                        <textarea name="about"
                                  rows="5"
                                  placeholder="Votre approche, parcours, certifications..."
                                  class="mt-1 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">{{ old('about', strip_tags((string) $user->about)) }}</textarea>
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Services</span>
                        <textarea name="services_text"
                                  rows="4"
                                  placeholder="Un service par ligne, ou separes par virgule"
                                  class="mt-1 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">{{ $servicesText }}</textarea>
                    </label>
                </div>

                <div class="mt-3 divide-y divide-gray-100">
                    @foreach([
                        'share_address_publicly' => ['label' => 'Afficher mon adresse', 'default' => (bool) $user->share_address_publicly],
                        'share_email_publicly' => ['label' => 'Afficher mon email pro', 'default' => (bool) $user->share_email_publicly],
                        'share_phone_publicly' => ['label' => 'Afficher mon telephone', 'default' => (bool) $user->share_phone_publicly],
                    ] as $field => $option)
                        <label class="flex items-center justify-between gap-4 py-3">
                            <span class="text-sm font-medium text-gray-700">{{ $option['label'] }}</span>
                            <input type="checkbox"
                                   name="{{ $field }}"
                                   value="1"
                                   class="h-5 w-5 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                                   {{ $checked($field, $option['default']) ? 'checked' : '' }}>
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Prise de RDV</h2>

                <label class="mt-3 flex items-center justify-between gap-4 rounded-lg bg-[#f7f8f1] px-3 py-3">
                    <span class="text-sm font-medium text-gray-700">Reservation en ligne</span>
                    <input type="checkbox"
                           name="accept_online_appointments"
                           value="1"
                           class="h-5 w-5 rounded border-gray-300 text-[#647a0b] focus:ring-[#647a0b]"
                           {{ $checked('accept_online_appointments', (bool) $user->accept_online_appointments) ? 'checked' : '' }}>
                </label>

                <div class="mt-3 grid grid-cols-2 gap-3">
                    <label class="block min-w-0">
                        <span class="text-sm font-medium text-gray-700">Delai mini h</span>
                        <input type="number"
                               name="minimum_notice_hours"
                               value="{{ old('minimum_notice_hours', $user->minimum_notice_hours) }}"
                               min="0"
                               inputmode="numeric"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block min-w-0">
                        <span class="text-sm font-medium text-gray-700">Pause min</span>
                        <input type="number"
                               name="buffer_time_between_appointments"
                               value="{{ old('buffer_time_between_appointments', $user->buffer_time_between_appointments) }}"
                               min="0"
                               inputmode="numeric"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block min-w-0">
                        <span class="text-sm font-medium text-gray-700">Max / jour</span>
                        <input type="number"
                               name="global_daily_booking_limit"
                               value="{{ old('global_daily_booking_limit', $user->global_daily_booking_limit) }}"
                               min="1"
                               max="500"
                               inputmode="numeric"
                               placeholder="Illimite"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>

                    <label class="block min-w-0">
                        <span class="text-sm font-medium text-gray-700">Annulation h</span>
                        <input type="number"
                               name="cancellation_notice_hours"
                               value="{{ old('cancellation_notice_hours', $user->cancellation_notice_hours ?? 0) }}"
                               min="0"
                               max="720"
                               inputmode="numeric"
                               class="mt-1 h-11 w-full rounded-lg border-gray-300 text-base focus:border-[#647a0b] focus:ring-[#647a0b]">
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-[#e4e8d5] bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Reglages avances</h2>
                <p class="mt-1 text-sm leading-snug text-gray-600">
                    Photos, logos, PDF, facturation electronique, couleurs de facture et connexions restent disponibles dans la vue web complete.
                </p>
                <a href="{{ route('profile.editCompanyInfo') }}"
                   class="mt-3 inline-flex h-10 w-full items-center justify-center rounded-lg border border-[#e4e8d5] bg-white px-3 text-xs font-semibold text-gray-700">
                    Ouvrir les reglages web
                </a>
            </section>
        </div>

        <div class="fixed bottom-0 left-0 z-50 w-full border-t border-[#e4e8d5] bg-white/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 shadow-lg backdrop-blur">
            <div class="mx-auto grid max-w-lg grid-cols-2 gap-2">
                <a href="{{ route('mobile.profile.index') }}"
                   class="inline-flex h-11 items-center justify-center rounded-lg border border-[#e4e8d5] bg-white text-sm font-semibold text-gray-700">
                    Annuler
                </a>
                <button type="submit"
                        class="inline-flex h-11 items-center justify-center rounded-lg bg-[#647a0b] text-sm font-semibold text-white">
                    Enregistrer
                </button>
            </div>
        </div>
    </form>
</x-mobile-layout>
