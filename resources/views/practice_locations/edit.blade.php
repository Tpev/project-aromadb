<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#647a0b] leading-tight">
            {{ __('Modifier le cabinet') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="rounded bg-green-100 text-green-800 px-4 py-2">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="rounded bg-red-100 text-red-800 px-4 py-2">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow rounded-lg p-6">
                <form method="POST" action="{{ route('practice-locations.update', $location) }}">
                    @csrf
                    @method('PUT')
                    @include('practice_locations._form', ['location' => $location])
                </form>

                @if(config('features.shared_cabinets_v1'))
                    <hr class="my-6">

                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ __('Cabinet partagé') }}</h3>
                            <p class="text-sm text-gray-600">
                                {{ __('Invitez des thérapeutes déjà inscrits sur AromaMade pour partager ce cabinet. Une fois acceptée, la réservation d’un créneau au cabinet bloquera automatiquement ce même créneau pour tous les membres.') }}
                            </p>
                        </div>

                        @if($location->is_shared)
                            <div class="rounded-lg border bg-gray-50 p-4">
                                <form method="POST" action="{{ route('practice-locations.invites.store', $location) }}" class="grid grid-cols-1 md:grid-cols-[1fr_auto] gap-3 items-end">
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-medium mb-1">{{ __('Inviter un thérapeute (email du compte existant)') }}</label>
                                        <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded px-3 py-2" placeholder="therapeute@email.com" required>
                                    </div>
                                    <button class="px-4 py-2 rounded-lg bg-[#647a0b] text-white hover:bg-[#8ea633] transition">
                                        {{ __('Envoyer l’invitation') }}
                                    </button>
                                </form>
                            </div>

                            <div>
                                <h4 class="font-semibold text-gray-800 mb-2">{{ __('Membres du cabinet') }}</h4>
                                <div class="space-y-3">
                                    @forelse($location->memberships->whereNotNull('accepted_at') as $membership)
                                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border p-3">
                                            <div>
                                                <div class="font-medium text-gray-800">
                                                    {{ $membership->user->company_name ?: trim(($membership->user->first_name ?? '').' '.($membership->user->last_name ?? '')) ?: $membership->user->email }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $membership->user->email }} — {{ $membership->role === 'owner' ? __('Propriétaire') : __('Membre') }}
                                                </div>
                                            </div>
                                            @if($membership->role !== 'owner' && (int) $membership->user_id !== (int) $location->user_id)
                                                <form method="POST" action="{{ route('practice-locations.members.remove', [$location, $membership]) }}" onsubmit="return confirm('Retirer ce membre du cabinet ?');">
                                                    @csrf
                                                    <button class="px-3 py-1.5 rounded border border-red-300 text-red-700 hover:bg-red-50">
                                                        {{ __('Retirer') }}
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @empty
                                        <p class="text-sm text-gray-500">{{ __('Aucun membre supplémentaire pour le moment.') }}</p>
                                    @endforelse
                                </div>
                            </div>

                            <div>
                                <h4 class="font-semibold text-gray-800 mb-2">{{ __('Invitations en attente') }}</h4>
                                <div class="space-y-3">
                                    @forelse($location->pendingInvites as $invite)
                                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border p-3">
                                            <div>
                                                <div class="font-medium text-gray-800">{{ $invite->invited_email }}</div>
                                                <div class="text-sm text-gray-500">
                                                    {{ __('Expire le') }} {{ optional($invite->expires_at)->format('d/m/Y H:i') ?? '—' }}
                                                </div>
                                            </div>
                                            <form method="POST" action="{{ route('practice-locations.invites.cancel', $invite) }}" onsubmit="return confirm('Annuler cette invitation ?');">
                                                @csrf
                                                <button class="px-3 py-1.5 rounded border border-red-300 text-red-700 hover:bg-red-50">
                                                    {{ __('Annuler') }}
                                                </button>
                                            </form>
                                        </div>
                                    @empty
                                        <p class="text-sm text-gray-500">{{ __('Aucune invitation en attente.') }}</p>
                                    @endforelse
                                </div>
                            </div>
                        @else
                            <div class="rounded-lg border border-dashed p-4 text-sm text-gray-600">
                                {{ __('Activez l’option “cabinet partagé” ci-dessus pour inviter d’autres thérapeutes sur ce lieu.') }}
                            </div>
                        @endif
                    </div>
                @endif

                <hr class="my-6">

                <form method="POST" action="{{ route('practice-locations.destroy', $location) }}"
                      onsubmit="return confirm('Supprimer ce cabinet ?');">
                    @csrf
                    @method('DELETE')
                    <button class="px-4 py-2 rounded-lg border border-red-300 text-red-700 hover:bg-red-50">
                        {{ __('Supprimer ce cabinet') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
