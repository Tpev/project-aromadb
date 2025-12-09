{{-- resources/views/digital-trainings/enrollments/index.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-semibold text-xl" style="color: #647a0b;">
                    {{ __('Participants de la formation') }} – {{ $training->title }}
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    {{ __('Invitez vos clients et suivez qui a accès à cette formation digitale.') }}
                </p>
            </div>

            <a href="{{ route('digital-trainings.builder', $training) }}"
               class="inline-flex items-center rounded-full border border-slate-200 px-4 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                🧱 {{ __('Retour au builder') }}
            </a>
        </div>
    </x-slot>

    <div class="container mt-6">
        <div class="mx-auto max-w-5xl space-y-6">

            @if(session('success'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Invite block --}}
            <div class="bg-white shadow-sm rounded-2xl border border-slate-100 p-5">
                <h3 class="text-sm font-semibold text-slate-800 mb-1">
                    {{ __('Inviter un client à cette formation') }}
                </h3>
                <p class="text-xs text-slate-500 mb-4">
                    {{ __('Vous pouvez sélectionner un profil client existant ou saisir manuellement un nom et un email.') }}
                </p>

                <form action="{{ route('digital-trainings.enrollments.store', $training) }}"
                      method="POST"
                      class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">
                                {{ __('Sélectionner un profil client (optionnel)') }}
                            </label>
                            <select name="client_profile_id"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40">
                                <option value="">{{ __('Aucun / saisir manuellement') }}</option>
                                @foreach($clientProfiles as $client)
                                    <option value="{{ $client->id }}" {{ old('client_profile_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->last_name }} {{ $client->first_name }}
                                        @if($client->email)
                                            – {{ $client->email }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-[11px] text-slate-500">
                                {{ __('Si vous choisissez un profil, le nom et l’email seront pré-remplis automatiquement.') }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">
                                {{ __('Nom du participant (si différent ou sans profil)') }}
                            </label>
                            <input type="text"
                                   name="participant_name"
                                   value="{{ old('participant_name') }}"
                                   class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40"
                                   placeholder="{{ __('Ex : Marie Dupont') }}">
                            <p class="mt-1 text-[11px] text-slate-500">
                                {{ __('Optionnel si un profil client est sélectionné.') }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">
                                {{ __('Email du participant') }} *
                            </label>
                            <input type="email"
                                   name="participant_email"
                                   value="{{ old('participant_email') }}"
                                   required
                                   class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#647a0b]/40"
                                   placeholder="prenom.nom@email.com">
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit"
                                class="rounded-full bg-[#647a0b] px-5 py-2 text-sm font-semibold text-white hover:bg-[#506108]">
                            {{ __('Créer l’accès et envoyer l’invitation') }}
                        </button>
                    </div>
                </form>
            </div>

            {{-- List of enrollments --}}
            <div class="bg-white shadow-sm rounded-2xl border border-slate-100 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-800">
                        {{ __('Participants ayant accès à cette formation') }}
                    </h3>
                    <span class="text-xs text-slate-500">
                        {{ $training->enrollments->count() }} {{ __('participant(s)') }}
                    </span>
                </div>

                @if($training->enrollments->isEmpty())
                    <p class="text-sm text-slate-500">
                        {{ __('Aucun participant pour le moment. Invitez un premier client ci-dessus.') }}
                    </p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs border border-slate-100 rounded-lg overflow-hidden">
                            <thead class="bg-slate-50 text-slate-600">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold">{{ __('Nom') }}</th>
                                    <th class="px-3 py-2 text-left font-semibold">{{ __('Email') }}</th>
                                    <th class="px-3 py-2 text-left font-semibold">{{ __('Source') }}</th>
                                    <th class="px-3 py-2 text-left font-semibold">{{ __('Progression') }}</th>
                                    <th class="px-3 py-2 text-left font-semibold">{{ __('Dernier accès') }}</th>
                                    <th class="px-3 py-2 text-left font-semibold">{{ __('Créé le') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($training->enrollments as $enrollment)
                                    <tr class="hover:bg-slate-50/60">
                                        <td class="px-3 py-2">
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-slate-800">
                                                    {{ $enrollment->participant_name ?? '—' }}
                                                </span>
                                                @if($enrollment->clientProfile)
                                                    <span class="text-[11px] text-slate-500">
                                                        {{ __('Profil client :') }} {{ $enrollment->clientProfile->last_name }} {{ $enrollment->clientProfile->first_name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 text-slate-700">
                                            {{ $enrollment->participant_email }}
                                        </td>
                                        <td class="px-3 py-2 text-slate-600">
                                            {{ ucfirst($enrollment->source) }}
                                        </td>
                                        <td class="px-3 py-2 text-slate-700">
                                            {{ $enrollment->progress_percent }}%
                                            @if($enrollment->completed_at)
                                                <span class="ml-1 text-[11px] text-emerald-600">
                                                    {{ __('Terminé') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-slate-600">
                                            @if($enrollment->last_accessed_at)
                                                {{ $enrollment->last_accessed_at->format('d/m/Y H:i') }}
                                            @else
                                                <span class="text-[11px] text-slate-400">
                                                    {{ __('Jamais') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-slate-600">
                                            {{ $enrollment->created_at->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
