{{-- resources/views/events/show.blade.php --}}
@php
    use App\Models\ClientProfile;

    // Précharger tous les emails clients du thérapeute pour éviter les requêtes dans la boucle
    $clientEmailsMap = ClientProfile::where('user_id', $event->user_id)
        ->whereNotNull('email')
        ->get()
        ->reduce(function ($carry, $client) {
            $carry[strtolower($client->email)] = $client->id;
            return $carry;
        }, []);

    $totalReservations = $event->reservations->count();
    $availableSpots    = $event->limited_spot ? $event->number_of_spot : '∞';

    // URL publique de réservation (celle qui a les bons tags OG)
    $eventPublicUrl = url("/events/{$event->id}/reserve");

    // Texte de base pour le post Facebook
    $shareText = "Je participe à : {$event->name}";

    // URL du partage Facebook (click-to-share)
    $facebookShareUrl = 'https://www.facebook.com/sharer/sharer.php'
                      . '?u=' . urlencode($eventPublicUrl)
                      . '&quote=' . urlencode($shareText);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-[#647a0b]">
                    {{ __('Détails de l\'Événement') }}
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    {{ __('Gérez cet atelier / événement directement depuis AromaMade PRO.') }}
                </p>
            </div>

            {{-- Actions header (desktop) --}}
            <div class="hidden md:flex items-center gap-2">
                <a href="{{ route('events.edit', $event->id) }}"
                   class="inline-flex items-center gap-2 rounded-full bg-[#647a0b] px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-[#4f6409] transition">
                    <i class="fas fa-edit text-[11px]"></i>
                    <span>{{ __('Modifier') }}</span>
                </a>

                {{-- Partager sur Facebook (header desktop) --}}
                <a href="{{ $facebookShareUrl }}"
                   target="_blank"
                   rel="noopener"
                   class="inline-flex items-center gap-2 rounded-full bg-[#1877F2] px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-[#145DBF] transition">
                    <i class="fab fa-facebook-f text-[11px]"></i>
                    <span>{{ __('Partager sur Facebook') }}</span>
                </a>

                <form action="{{ route('events.destroy', $event->id) }}" method="POST"
                      onsubmit="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer cet événement ?') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-full bg-red-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-red-700 transition">
                        <i class="fas fa-trash-alt text-[11px]"></i>
                        <span>{{ __('Supprimer') }}</span>
                    </button>
                </form>

                <a href="{{ route('events.index') }}"
                   class="inline-flex items-center gap-2 rounded-full border border-[#854f38]/40 bg-white px-4 py-2 text-xs font-semibold text-[#854f38] hover:bg-[#854f38] hover:text-white transition">
                    <i class="fas fa-arrow-left text-[11px]"></i>
                    <span>{{ __('Retour') }}</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6 bg-[#f7fbe8]">

        {{-- Alerts --}}
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50/90 px-4 py-3 text-sm text-emerald-800 shadow-sm">
                <div class="flex">
                    <span class="mr-2 mt-[2px] text-emerald-500">
                        <i class="fas fa-check-circle"></i>
                    </span>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50/90 px-4 py-3 text-sm text-red-800 shadow-sm">
                <div class="flex">
                    <span class="mr-2 mt-[2px] text-red-500">
                        <i class="fas fa-exclamation-triangle"></i>
                    </span>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        {{-- Main card --}}
        <div class="rounded-2xl border border-[#dbe3b8] bg-white shadow-md overflow-hidden">
            {{-- Solid green header band --}}
            <div class="px-6 py-4 sm:px-8 sm:py-5" style="background-color: #647a0b;">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between text-white">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold">
                            {{ $event->name }}
                        </h1>
                        <p class="mt-1 text-xs text-white/80">
                            {{ \Carbon\Carbon::parse($event->start_date_time)->format('d/m/Y à H:i') }}
                            · {{ $event->location }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2 justify-start md:justify-end">
                        <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-[11px] font-semibold">
                            <i class="fas fa-users mr-1 text-amber-300"></i>
                            {{ __('Réservations :') }} {{ $totalReservations }} / {{ $availableSpots }}
                        </span>

                        <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-[11px] font-semibold">
                            <i class="fas fa-ticket-alt mr-1 text-lime-200"></i>
                            {{ $event->booking_required ? __('Réservation requise') : __('Sans réservation obligatoire') }}
                        </span>

                        <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-[11px] font-semibold">
                            <i class="fas fa-globe-europe mr-1 text-sky-200"></i>
                            @if($event->showOnPortail)
                                {{ __('Affiché sur le portail') }}
                            @else
                                {{ __('Non affiché sur le portail') }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            {{-- Content --}}
            <div class="px-4 py-5 sm:px-8 sm:py-6">
                <div class="grid gap-6 lg:grid-cols-3 lg:items-start">
                    {{-- Left: info --}}
                    <div class="lg:col-span-2 space-y-5">

                        {{-- Description --}}
                        @if($event->description)
                            <div class="rounded-xl border border-[#e2ecc3] bg-[#fbfff6] px-4 py-3 sm:px-5 sm:py-4">
                                <h3 class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-[#647a0b]">
                                    <i class="fas fa-info-circle text-[#647a0b]"></i>
                                    {{ __('Description') }}
                                </h3>
                                <p class="mt-2 text-sm leading-relaxed text-slate-800">
                                    {{ $event->description }}
                                </p>
                            </div>
                        @endif

                        {{-- Meta grid --}}
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="rounded-xl border border-[#e2ecc3] bg-[#fdfaf3] px-4 py-3">
                                <h3 class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-[#854f38]">
                                    <i class="fas fa-calendar-alt text-[#854f38]"></i>
                                    {{ __('Date & heure') }}
                                </h3>
                                <p class="mt-2 text-sm font-medium text-slate-900">
                                    {{ \Carbon\Carbon::parse($event->start_date_time)->format('d/m/Y à H:i') }}
                                </p>
                            </div>

                            <div class="rounded-xl border border-[#e2ecc3] bg-[#fdfaf3] px-4 py-3">
                                <h3 class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-[#854f38]">
                                    <i class="fas fa-hourglass-half text-[#854f38]"></i>
                                    {{ __('Durée') }}
                                </h3>
                                <p class="mt-2 text-sm font-medium text-slate-900">
                                    {{ $event->duration }} {{ __('minutes') }}
                                </p>
                            </div>

                            <div class="rounded-xl border border-[#e2ecc3] bg-[#fbfff6] px-4 py-3">
                                <h3 class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-[#647a0b]">
                                    <i class="fas fa-map-marker-alt text-[#647a0b]"></i>
                                    {{ __('Lieu') }}
                                </h3>
                                <p class="mt-2 text-sm font-medium text-slate-900">
                                    {{ $event->location }}
                                </p>
                            </div>

                            <div class="rounded-xl border border-[#e2ecc3] bg-[#fbfff6] px-4 py-3">
                                <h3 class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-[#647a0b]">
                                    <i class="fas fa-users text-[#647a0b]"></i>
                                    {{ __('Places limitées') }}
                                </h3>
                                <p class="mt-2 text-sm font-medium text-slate-900">
                                    {{ $event->limited_spot ? __('Oui') : __('Non') }}
                                    @if($event->limited_spot)
                                        · {{ __('Nombre de places :') }} {{ $event->number_of_spot }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Associated product --}}
                        @if($event->associatedProduct)
                            <div class="rounded-xl border border-[#e2ecc3] bg-[#fffaf7] px-4 py-4 sm:px-5 sm:py-5">
                                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                    <div>
                                        <h3 class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-[#854f38]">
                                            <i class="fas fa-box-open text-[#854f38]"></i>
                                            {{ __('Produit associé') }}
                                        </h3>
                                        <p class="mt-2 text-sm font-semibold text-slate-900">
                                            {{ $event->associatedProduct->name }}
                                        </p>
                                        @if($event->associatedProduct->description)
                                            <p class="mt-1 text-xs text-slate-700 leading-relaxed">
                                                {{ $event->associatedProduct->description }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="md:text-right">
                                        <p class="text-[11px] font-semibold uppercase tracking-wide text-[#854f38]/80">
                                            {{ __('Prix TTC') }}
                                        </p>
                                        <p class="mt-1 text-2xl font-extrabold text-[#854f38]">
                                            {{ number_format($event->associatedProduct->price_incl_tax, 2, ',', ' ') }} €
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Right: image / placeholder --}}
                    <div class="space-y-4">
                        @if($event->image)
                            <div class="rounded-2xl border border-[#dbe3b8] bg-[#fbfff6] p-2 shadow-sm">
                                <img src="{{ asset('storage/' . $event->image) }}"
                                     alt="{{ $event->name }}"
                                     class="w-full max-h-72 object-cover rounded-xl">
                            </div>
                        @else
                            <div class="rounded-2xl border border-dashed border-[#d5dfac] bg-[#fbfff6] p-5 text-sm text-slate-600 flex flex-col justify-center h-full">
                                <p class="font-semibold text-[#647a0b]">
                                    {{ __('Aucune image définie pour cet événement') }}
                                </p>
                                <p class="mt-1 text-xs">
                                    {{ __('Ajoutez une image depuis la page d’édition pour rendre le visuel plus attractif sur votre portail.') }}
                                </p>
                                <a href="{{ route('events.edit', $event->id) }}"
                                   class="mt-3 inline-flex items-center gap-2 rounded-full bg-[#647a0b] px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-[#4f6409] transition">
                                    <i class="fas fa-image text-[11px]"></i>
                                    <span>{{ __('Ajouter une image') }}</span>
                                </a>
                            </div>
                        @endif

                        {{-- Small info chip --}}
                        <div class="rounded-xl border border-[#e2ecc3] bg-[#fefaf1] px-4 py-3 text-xs text-slate-700">
                            <p>
                                <i class="fas fa-lightbulb text-amber-400 mr-2"></i>
                                {{ __('Pensez à partager le lien de cet événement à vos clients pour remplir les places disponibles plus rapidement.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Reservations --}}
        @if(Auth::id() === $event->user_id)
            <div class="rounded-2xl border border-[#dbe3b8] bg-white shadow-md">
                <div class="flex flex-col gap-2 border-b border-[#e2ecc3] px-4 py-3 sm:px-6 sm:py-4 md:flex-row md:items-center md:justify-between bg-[#fbfff6]">
                    <div>
                        <h2 class="text-base font-semibold text-[#647a0b]">
                            {{ __('Liste des réservations') }}
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ __('Visualisez les personnes inscrites, créez rapidement des dossiers clients et gérez les réservations.') }}
                        </p>
                    </div>

                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-700 border border-emerald-100">
                        <i class="fas fa-users mr-1"></i>
                        {{ __('Total :') }} {{ $totalReservations }} / {{ $availableSpots }}
                    </span>
                </div>

                @if($event->reservations->count() > 0)
                    <div class="w-full overflow-x-auto">
                        <table class="min-w-[900px] text-sm text-left text-slate-700">
                            <thead>
                                <tr class="bg-[#647a0b] text-white">
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide">{{ __('N°') }}</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide">{{ __('Nom complet') }}</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide">{{ __('Email') }}</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide">{{ __('Téléphone') }}</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide whitespace-nowrap">{{ __('Date de réservation') }}</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide whitespace-nowrap">{{ __('Dossier client') }}</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($event->reservations as $index => $reservation)
                                    @php
                                        $normalizedEmail = $reservation->email ? strtolower($reservation->email) : null;
                                        $existingClientId = $normalizedEmail && isset($clientEmailsMap[$normalizedEmail])
                                            ? $clientEmailsMap[$normalizedEmail]
                                            : null;
                                    @endphp
                                    <tr class="border-b border-[#eef3d4] odd:bg-white even:bg-[#fbfff6] hover:bg-lime-50/60">
                                        <td class="px-4 py-3 align-top text-xs text-slate-500">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-4 py-3 align-top text-sm font-medium text-slate-900">
                                            {{ $reservation->full_name }}
                                        </td>
                                        <td class="px-4 py-3 align-top text-sm">
                                            {{ $reservation->email }}
                                        </td>
                                        <td class="px-4 py-3 align-top text-sm">
                                            {{ $reservation->phone ?? __('N/A') }}
                                        </td>
                                        <td class="px-4 py-3 align-top whitespace-nowrap text-sm">
                                            {{ $reservation->created_at->format('d/m/Y H:i') }}
                                        </td>

                                        {{-- Dossier client --}}
                                        <td class="px-4 py-3 align-top client-cell">
                                            @if($existingClientId)
                                                <div class="flex flex-col gap-1">
                                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 border border-emerald-100">
                                                        {{ __('Client existant') }}
                                                    </span>
                                                    <a href="{{ route('client_profiles.show', $existingClientId) }}"
                                                       class="text-xs font-medium text-[#647a0b] hover:underline"
                                                       title="{{ __('Ouvrir le dossier client') }}">
                                                        {{ __('Voir le dossier') }}
                                                    </a>
                                                </div>
                                            @elseif($reservation->email)
                                                <button type="button"
                                                        class="js-create-client-from-reservation inline-flex items-center gap-1 rounded-full bg-[#647a0b] px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-[#4f6409] transition"
                                                        data-route="{{ route('reservations.createClient', ['event' => $event->id, 'reservation' => $reservation->id]) }}">
                                                    <i class="fas fa-user-plus text-[11px]"></i>
                                                    <span>{{ __('Créer un profil') }}</span>
                                                </button>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600 border border-slate-200">
                                                    {{ __('Email manquant') }}
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Actions --}}
                                        <td class="px-4 py-3 align-top text-right">
                                            <form action="{{ route('reservations.destroy', $reservation->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer cette réservation ?') }}');"
                                                  class="inline-flex">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 rounded-full bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-700 transition">
                                                    <i class="fas fa-trash-alt text-[11px]"></i>
                                                    <span>{{ __('Supprimer') }}</span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-8 text-center text-sm text-slate-500">
                        {{ __('Aucune réservation pour le moment.') }}
                    </div>
                @endif
            </div>
        @endif

        {{-- Global actions bar (desktop) --}}
        <div class="mt-6 hidden md:flex justify-center gap-3">
            <a href="{{ route('events.edit', $event->id) }}"
               class="inline-flex items-center gap-2 rounded-full bg-[#647a0b] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#4f6409] transition">
                <i class="fas fa-edit text-xs"></i>
                <span>{{ __('Modifier l\'événement') }}</span>
            </a>

            {{-- Partager sur Facebook (global actions desktop) --}}
            <a href="{{ $facebookShareUrl }}"
               target="_blank"
               rel="noopener"
               class="inline-flex items-center gap-2 rounded-full bg-[#1877F2] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#145DBF] transition">
                <i class="fab fa-facebook-f text-xs"></i>
                <span>{{ __('Partager sur Facebook') }}</span>
            </a>

            <form action="{{ route('events.destroy', $event->id) }}" method="POST"
                  onsubmit="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer cet événement ?') }}');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-full bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700 transition">
                    <i class="fas fa-trash-alt text-xs"></i>
                    <span>{{ __('Supprimer l\'événement') }}</span>
                </button>
            </form>

            <a href="{{ route('events.index') }}"
               class="inline-flex items-center gap-2 rounded-full border border-[#854f38]/40 bg-white px-5 py-2.5 text-sm font-semibold text-[#854f38] hover:bg-[#854f38] hover:text-white transition">
                <i class="fas fa-arrow-left text-xs"></i>
                <span>{{ __('Retour à la liste des événements') }}</span>
            </a>
        </div>

        {{-- Mobile footer actions --}}
        <div class="mt-4 flex md:hidden flex-col gap-2">
            <a href="{{ route('events.edit', $event->id) }}"
               class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#647a0b] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#4f6409] transition">
                <i class="fas fa-edit text-xs"></i>
                <span>{{ __('Modifier cet événement') }}</span>
            </a>

            {{-- Partager sur Facebook (mobile) --}}
            <a href="{{ $facebookShareUrl }}"
               target="_blank"
               rel="noopener"
               class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#1877F2] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#145DBF] transition">
                <i class="fab fa-facebook-f text-xs"></i>
                <span>{{ __('Partager sur Facebook') }}</span>
            </a>

            <form action="{{ route('events.destroy', $event->id) }}" method="POST"
                  onsubmit="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer cet événement ?') }}');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700 transition">
                    <i class="fas fa-trash-alt text-xs"></i>
                    <span>{{ __('Supprimer l\'événement') }}</span>
                </button>
            </form>

            <a href="{{ route('events.index') }}"
               class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-[#854f38]/40 bg-white px-4 py-2.5 text-sm font-semibold text-[#854f38] hover:bg-[#854f38] hover:text-white transition">
                <i class="fas fa-arrow-left text-xs"></i>
                <span>{{ __('Retour à la liste des événements') }}</span>
            </a>
        </div>
    </div>

    {{-- JS: création du client en background (sans changer de page) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const buttons = document.querySelectorAll('.js-create-client-from-reservation');

            buttons.forEach((btn) => {
                btn.addEventListener('click', async function () {
                    const url = this.dataset.route;
                    if (!url) return;

                    if (!confirm('{{ __("Créer un profil client à partir de cette réservation ?") }}')) {
                        return;
                    }

                    this.disabled = true;
                    this.classList.add('opacity-60', 'pointer-events-none');
                    this.textContent = '{{ __("Création...") }}';

                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({}),
                        });

                        const data = await response.json();

                        if (response.ok && (data.status === 'created' || data.status === 'exists')) {
                            const cell = this.closest('.client-cell');
                            if (cell) {
                                cell.innerHTML = `
                                    <div class="flex flex-col gap-1">
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 border border-emerald-100">
                                            {{ __('Client créé') }}
                                        </span>
                                        ${data.client_profile_url
                                            ? `<a href="${data.client_profile_url}"
                                                   class="text-xs font-medium text-[#647a0b] hover:underline">
                                                   {{ __('Voir le dossier') }}
                                               </a>`
                                            : ''
                                        }
                                    </div>
                                `;
                            }
                        } else {
                            alert(data.message || '{{ __("Une erreur est survenue lors de la création du profil client.") }}');
                            this.disabled = false;
                            this.classList.remove('opacity-60', 'pointer-events-none');
                            this.textContent = '{{ __("Créer un profil") }}';
                        }
                    } catch (e) {
                        console.error(e);
                        alert('{{ __("Erreur réseau. Merci de réessayer.") }}');
                        this.disabled = false;
                        this.classList.remove('opacity-60', 'pointer-events-none');
                        this.textContent = '{{ __("Créer un profil") }}';
                    }
                });
            });
        });
    </script>

    {{-- FontAwesome (si pas déjà chargé globalement) --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</x-app-layout>
