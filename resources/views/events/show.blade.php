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

    // Format / Visio
    $eventType = $event->event_type ?? 'in_person';
    $isVisio   = $eventType === 'visio';

    $displayLocation = $isVisio
        ? ($event->location ?: __('En ligne (Visio)'))
        : ($event->location ?: '—');

    // Two-link system
    // Host link = therapist / moderator
    // Public link = participant / non-moderator
    $visioHostLink   = $event->visio_host_link ?? null;
    $visioPublicLink = $event->visio_public_link ?? null;

    $visioProvider = $event->visio_provider ?? null;
    $isAromaMadeVisio = $isVisio && $visioProvider === 'aromamade' && !empty($event->visio_token);
    $isExternalVisio  = $isVisio && $visioProvider === 'external' && !empty($event->visio_url);
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

        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 shadow-sm">
                <div class="flex items-center gap-2 text-sm font-semibold">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-emerald-600 text-white">
                        <i class="fas fa-check"></i>
                    </span>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 shadow-sm">
                <div class="flex items-center gap-2 text-sm font-semibold">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-red-600 text-white">
                        <i class="fas fa-exclamation-triangle"></i>
                    </span>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <div class="rounded-2xl border border-[#dbe3b8] bg-white shadow-md overflow-hidden">
            <div class="px-6 py-4 sm:px-8 sm:py-5" style="background-color: #647a0b;">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between text-white">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold">
                            {{ $event->name }}
                        </h1>
                        <p class="mt-1 text-xs text-white/80">
                            {{ \Carbon\Carbon::parse($event->start_date_time)->format('d/m/Y à H:i') }}
                            · {{ $displayLocation }}
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
                            @if($isVisio)
                                <i class="fas fa-video mr-1 text-indigo-200"></i>
                                {{ __('Visio') }}
                            @else
                                <i class="fas fa-map-marker-alt mr-1 text-amber-200"></i>
                                {{ __('Présentiel') }}
                            @endif
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

            <div class="px-4 py-5 sm:px-8 sm:py-6">
                <div class="grid gap-6 lg:grid-cols-3 lg:items-start">
                    <div class="lg:col-span-2 space-y-5">

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

                            {{-- Visio / Lieu (two links) --}}
                            <div class="rounded-xl border border-[#e2ecc3] bg-[#fbfff6] px-4 py-3 md:col-span-2">
                                <h3 class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-[#647a0b]">
                                    @if($isVisio)
                                        <i class="fas fa-video text-[#647a0b]"></i>
                                        {{ __('Visio') }}
                                    @else
                                        <i class="fas fa-map-marker-alt text-[#647a0b]"></i>
                                        {{ __('Lieu') }}
                                    @endif
                                </h3>

                                @if(!$isVisio)
                                    <p class="mt-2 text-sm font-medium text-slate-900">
                                        {{ $displayLocation }}
                                    </p>
                                @else
                                    <div class="mt-2 space-y-4">

                                        <div>
                                            <p class="text-sm font-medium text-slate-900">
                                                {{ $displayLocation }}
                                            </p>
                                            <p class="text-xs text-slate-500 mt-1">
                                                @if($isAromaMadeVisio)
                                                    {{ __('Type : Visio AromaMade ') }}
                                                @elseif($isExternalVisio)
                                                    {{ __('Type : Lien externe') }}
                                                @else
                                                    {{ __('Type : Visio') }}
                                                @endif
                                            </p>
                                        </div>

                                        {{-- Host link --}}
                                        <div class="rounded-xl border border-[#e2ecc3] bg-white p-3">
                                            <p class="text-xs font-semibold text-slate-700">
                                                {{ __('Lien thérapeute (host)') }}
                                            </p>

                                            @if($visioHostLink)
                                                <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center">
                                                    <a href="{{ $visioHostLink }}"
                                                       target="_blank"
                                                       rel="noopener noreferrer"
                                                       class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-3 py-2 text-sm font-semibold text-[#647a0b] border border-[#e2ecc3] hover:bg-[#f3f9dd] transition">
                                                        <i class="fas fa-external-link-alt text-[12px]"></i>
                                                        <span>{{ __('Ouvrir') }}</span>
                                                    </a>

                                                    <button type="button"
                                                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#647a0b] px-3 py-2 text-sm font-semibold text-white hover:bg-[#4f6409] transition"
                                                            onclick="copyToClipboard('{{ $visioHostLink }}')">
                                                        <i class="far fa-copy text-[12px]"></i>
                                                        <span>{{ __('Copier') }}</span>
                                                    </button>
                                                </div>

                                                <p class="mt-2 text-xs text-slate-500 break-all">
                                                    {{ $visioHostLink }}
                                                </p>

                                                @if($isAromaMadeVisio)
                                                    <p class="mt-1 text-[11px] text-slate-400">
                                                        {{ __(' ') }}
                                                    </p>
                                                @endif
                                            @else
                                                <p class="mt-2 text-xs text-slate-500">
                                                    {{ __('Aucun lien host disponible (vérifiez le provider et le token).') }}
                                                </p>
                                            @endif
                                        </div>

                                        {{-- Public link --}}
                                        <div class="rounded-xl border border-[#e2ecc3] bg-white p-3">
                                            <p class="text-xs font-semibold text-slate-700">
                                                {{ __('Lien participants (public)') }}
                                            </p>

                                            @if($visioPublicLink)
                                                <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center">
                                                    <a href="{{ $visioPublicLink }}"
                                                       target="_blank"
                                                       rel="noopener noreferrer"
                                                       class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-3 py-2 text-sm font-semibold text-[#647a0b] border border-[#e2ecc3] hover:bg-[#f3f9dd] transition">
                                                        <i class="fas fa-external-link-alt text-[12px]"></i>
                                                        <span>{{ __('Ouvrir') }}</span>
                                                    </a>

                                                    <button type="button"
                                                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#647a0b] px-3 py-2 text-sm font-semibold text-white hover:bg-[#4f6409] transition"
                                                            onclick="copyToClipboard('{{ $visioPublicLink }}')">
                                                        <i class="far fa-copy text-[12px]"></i>
                                                        <span>{{ __('Copier') }}</span>
                                                    </button>
                                                </div>

                                                <p class="mt-2 text-xs text-slate-500 break-all">
                                                    {{ $visioPublicLink }}
                                                </p>

                                                @if($isAromaMadeVisio)
                                                    <p class="mt-1 text-[11px] text-slate-400">
                                                        {{ __('C’est ce lien que vous envoyez aux participants.') }}
                                                    </p>
                                                @endif
                                            @else
                                                <p class="mt-2 text-xs text-slate-500">
                                                    {{ __('Aucun lien public disponible (vérifiez le provider et le token).') }}
                                                </p>
                                            @endif
                                        </div>

                                    </div>
                                @endif
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

                        {{-- Reservations --}}
                        <div class="rounded-xl border border-[#e2ecc3] bg-white px-4 py-4 sm:px-5 sm:py-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-[#647a0b]">
                                        <i class="fas fa-clipboard-list text-[#647a0b]"></i>
                                        {{ __('Réservations') }}
                                    </h3>
                                    <p class="mt-1 text-sm text-slate-600">
                                        {{ __('Liste des participants et infos associées.') }}
                                    </p>
                                </div>

                                <div class="flex md:hidden items-center gap-2">
                                    <a href="{{ route('events.edit', $event->id) }}"
                                       class="inline-flex items-center gap-2 rounded-full bg-[#647a0b] px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-[#4f6409] transition">
                                        <i class="fas fa-edit text-[11px]"></i>
                                        <span>{{ __('Modifier') }}</span>
                                    </a>
                                </div>
                            </div>
@php
    $clientsForSelect = \App\Models\ClientProfile::where('user_id', auth()->id())
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->get();
@endphp

<div class="mt-4 rounded-xl border border-[#e2ecc3] bg-[#fbfff6] p-4">
    <div class="flex items-start justify-between gap-4 flex-col sm:flex-row">
        <div>
            <p class="text-sm font-semibold text-slate-900">Ajouter un participant depuis vos clients</p>
            <p class="text-xs text-slate-600 mt-0.5">
                Le client sera ajouté comme réservation confirmée.
            </p>
        </div>
    </div>

    <form method="POST"
          action="{{ route('events.reservations.addFromClient', $event->id) }}"
          class="mt-3 flex flex-col sm:flex-row gap-3">
        @csrf

        <div class="flex-1">
            <select name="client_profile_id"
                    class="w-full rounded-lg border border-[#e2ecc3] bg-white px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#647a0b]/30">
                <option value="">Sélectionner un client…</option>
                @foreach($clientsForSelect as $c)
                    @php
                        $labelName  = trim(($c->first_name ?? '').' '.($c->last_name ?? ''));
                        $labelEmail = $c->email ? ' — '.$c->email : ' — (sans email)';
                    @endphp
                    <option value="{{ $c->id }}">
                        {{ $labelName ?: '(Sans nom)' }}{{ $labelEmail }}
                    </option>
                @endforeach
            </select>

            @error('client_profile_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-full bg-[#647a0b] px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-[#556a0a] focus:outline-none focus:ring-2 focus:ring-[#647a0b]/30">
            <i class="fas fa-user-plus text-[11px]"></i>
            Ajouter
        </button>
    </form>
</div>

                            <div class="mt-4 overflow-hidden rounded-xl border border-[#e2ecc3]">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-[#fbfff6] text-slate-700">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Nom') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Email') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Téléphone') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Statut') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">{{ __('Client') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-[#e2ecc3] bg-white">
                                            @forelse($event->reservations as $reservation)
                                                @php
                                                    $emailLower = strtolower($reservation->email ?? '');
                                                    $clientId = $emailLower && isset($clientEmailsMap[$emailLower]) ? $clientEmailsMap[$emailLower] : null;
                                                @endphp
                                                <tr class="hover:bg-[#fbfff6]/60 transition">
                                                    <td class="px-4 py-3 font-medium text-slate-900">
                                                        {{ $reservation->full_name ?: '—'}}
                                                    </td>
                                                    <td class="px-4 py-3 text-slate-700">
                                                        {{ $reservation->email ?: '—' }}
                                                    </td>
                                                    <td class="px-4 py-3 text-slate-700">
                                                        {{ $reservation->phone ?? '—' }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        @if(($reservation->status ?? null) === 'cancelled')
                                                            <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-700 border border-red-100">
                                                                {{ __('Annulé') }}
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 border border-emerald-100">
                                                                {{ __('Confirmé') }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 client-cell">
                                                        @if($clientId)
                                                            <div class="flex flex-col gap-1">
                                                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 border border-emerald-100">
                                                                    {{ __('Client existant') }}
                                                                </span>
                                                                <a href="{{ route('client_profiles.show', $clientId) }}"
                                                                   class="text-xs font-medium text-[#647a0b] hover:underline">
                                                                    {{ __('Voir le dossier') }}
                                                                </a>
                                                            </div>
                                                        @else
														<button type="button"
																class="js-create-client-from-reservation inline-flex items-center gap-2 rounded-full bg-[#647a0b] px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-[#556a0a] focus:outline-none focus:ring-2 focus:ring-[#647a0b]/30"
																data-route="{{ route('reservations.createClient', [$event->id, $reservation->id]) }}">
															<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
																<path d="M10 2a5 5 0 100 10 5 5 0 000-10zM3 16a7 7 0 0114 0v1H3v-1z"/>
															</svg>
															{{ __('Créer un profil') }}
														</button>

                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                                        {{ __('Aucune réservation pour le moment.') }}
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Right: share / actions --}}
                    <div class="space-y-4">
                        <div class="rounded-xl border border-[#e2ecc3] bg-white px-4 py-4 shadow-sm">
                            <h3 class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-[#647a0b]">
                                <i class="fas fa-share-alt text-[#647a0b]"></i>
                                {{ __('Partager') }}
                            </h3>

                            <div class="mt-4 space-y-2">
                                <a href="{{ $facebookShareUrl }}"
                                   target="_blank"
                                   rel="noopener"
                                   class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#1877F2] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#145DBF] transition">
                                    <i class="fab fa-facebook-f text-sm"></i>
                                    <span>{{ __('Partager sur Facebook') }}</span>
                                </a>

                                <div class="rounded-xl border border-[#e2ecc3] bg-[#fbfff6] p-3">
                                    <p class="text-xs font-semibold text-slate-700">
                                        {{ __('Lien public (inscription) :') }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-600 break-all">
                                        {{ $eventPublicUrl }}
                                    </p>
                                    <button type="button"
                                            class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#647a0b] px-3 py-2 text-sm font-semibold text-white hover:bg-[#4f6409] transition"
                                            onclick="copyToClipboard('{{ $eventPublicUrl }}')">
                                        <i class="far fa-copy text-[12px]"></i>
                                        <span>{{ __('Copier') }}</span>
                                    </button>
                                </div>

                                {{-- Shortcut: copy participant visio link --}}
                                @if($isVisio && $visioPublicLink)
                                    <div class="rounded-xl border border-[#e2ecc3] bg-[#fbfff6] p-3">
                                        <p class="text-xs font-semibold text-slate-700">
                                            {{ __('Lien visio (participants) :') }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-600 break-all">
                                            {{ $visioPublicLink }}
                                        </p>
                                        <button type="button"
                                                class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#647a0b] px-3 py-2 text-sm font-semibold text-white hover:bg-[#4f6409] transition"
                                                onclick="copyToClipboard('{{ $visioPublicLink }}')">
                                            <i class="far fa-copy text-[12px]"></i>
                                            <span>{{ __('Copier le lien visio') }}</span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="rounded-xl border border-[#e2ecc3] bg-white px-4 py-4 shadow-sm">
                            <h3 class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-[#854f38]">
                                <i class="fas fa-bolt text-[#854f38]"></i>
                                {{ __('Actions') }}
                            </h3>

                            <div class="mt-4 space-y-2">
                                <a href="{{ route('events.edit', $event->id) }}"
                                   class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#647a0b] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#4f6409] transition">
                                    <i class="fas fa-edit text-sm"></i>
                                    <span>{{ __('Modifier') }}</span>
                                </a>                                
								<a href="{{ route('events.duplicate', $event->id) }}"
                                   class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#647a0b] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#4f6409] transition">
                                    <i class="fas fa-edit text-sm"></i>
                                    <span>{{ __('Dupliquer') }}</span>
                                </a>
                                <form action="{{ route('events.destroy', $event->id) }}" method="POST"
                                      onsubmit="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer cet événement ?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                        <span>{{ __('Supprimer') }}</span>
                                    </button>
                                </form>


                                <a href="{{ route('events.index') }}"
                                   class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-[#854f38]/40 bg-white px-4 py-2.5 text-sm font-semibold text-[#854f38] hover:bg-[#854f38] hover:text-white transition">
                                    <i class="fas fa-arrow-left text-xs"></i>
                                    <span>{{ __('Retour à la liste des événements') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            if (!text) return;
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    alert('{{ __("Lien copié dans le presse-papiers.") }}');
                }).catch(() => {
                    fallbackCopy(text);
                });
            } else {
                fallbackCopy(text);
            }

            function fallbackCopy(t) {
                const ta = document.createElement('textarea');
                ta.value = t;
                ta.style.position = 'fixed';
                ta.style.top = '-1000px';
                ta.style.left = '-1000px';
                document.body.appendChild(ta);
                ta.focus();
                ta.select();
                try {
                    document.execCommand('copy');
                    alert('{{ __("Lien copié dans le presse-papiers.") }}');
                } catch (e) {
                    alert('{{ __("Impossible de copier automatiquement. Copiez le lien manuellement.") }}');
                }
                document.body.removeChild(ta);
            }
        }

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
</x-app-layout>
