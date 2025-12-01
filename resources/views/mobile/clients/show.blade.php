{{-- resources/views/mobile/clients/show.blade.php --}}
@php
    use Illuminate\Support\Str;

    $client    = $clientProfile;
    $fullName  = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''));
    $initials  = strtoupper(
        mb_substr($client->first_name ?? '', 0, 1) .
        mb_substr($client->last_name ?? '', 0, 1)
    );
    $companyName = (!empty($client->company_id) && $client->company)
        ? $client->company->name
        : null;

    $nextAppt = $appointments
        ->filter(fn($a) => $a->appointment_date && $a->appointment_date->isFuture())
        ->sortBy('appointment_date')
        ->first();

    $lastAppt = $appointments
        ->filter(fn($a) => $a->appointment_date && $a->appointment_date->isPast())
        ->sortByDesc('appointment_date')
        ->first();

    // Portal / invitation state
    $hasEmail          = !empty($client->email);
    $hasPortalAccount  = !empty($client->password);
    $hasInviteToken    = $client->password_setup_token_hash && $client->password_setup_expires_at;
    $inviteStillValid  = $hasInviteToken && $client->password_setup_expires_at->isFuture();
    $inviteSentAt      = $hasInviteToken
        ? $client->password_setup_expires_at->copy()->subDays(3)
        : null;

    // Testimonial request
    $testimonialRequest = $client->testimonialRequests()->latest()->first();

    // Messages (last 10)
    $messages = $client->messages->sortBy('created_at');
    $latestMessages = $messages->take(10);

    // Files / documents counts
    $filesCount = $client->clientFiles()->count();
    $documentsCount = \App\Models\Document::where('client_profile_id', $client->id)->count();
@endphp

<x-mobile-layout :title="$fullName ?: __('Client')">
    <div class="px-4 pt-4 pb-24 space-y-4">

        {{-- =======================
             HEADER / IDENTITÉ
             ======================= --}}
        <div class="rounded-2xl border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="w-12 h-12 rounded-full bg-[#647a0b]/10 flex items-center justify-center text-[15px] font-semibold text-[#647a0b]">
                    {{ $initials ?: 'C' }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-base font-semibold text-gray-900 truncate">
                        {{ $fullName ?: __('Client sans nom') }}
                    </p>

                    @if($companyName)
                        <p class="mt-0.5 text-[11px] text-[#647a0b] flex items-center gap-1.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#647a0b]/10 text-[#647a0b]">
                                👔 {{ $companyName }}
                            </span>
                        </p>
                    @else
                        <p class="mt-0.5 text-[11px] text-gray-500">
                            {{ __('Client particulier') }}
                        </p>
                    @endif

                    @if($nextAppt)
                        <p class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-[#f5f7eb] px-2.5 py-1 text-[10px] text-[#4b5722]">
                            <i class="fas fa-calendar-check text-[10px]"></i>
                            {{ __('Prochain RDV :') }}
                            {{ $nextAppt->appointment_date->format('d/m/Y H:i') }}
                        </p>
                    @elseif($lastAppt)
                        <p class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-2.5 py-1 text-[10px] text-slate-600">
                            <i class="fas fa-history text-[10px]"></i>
                            {{ __('Dernier RDV :') }}
                            {{ $lastAppt->appointment_date->format('d/m/Y H:i') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- =======================
             COORDONNÉES
             ======================= --}}
        <div class="rounded-2xl border border-[#e4e8d5] bg-white p-4 shadow-sm space-y-3">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                {{ __('Coordonnées') }}
            </h2>

            <div class="space-y-2 text-xs">
                <div class="flex items-center gap-2">
                    <i class="fas fa-envelope text-[11px] text-gray-400 w-4"></i>
                    <span class="text-gray-800 break-all">
                        {{ $client->email ?: __('Email non renseigné') }}
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <i class="fas fa-phone text-[11px] text-gray-400 w-4"></i>
                    <span class="text-gray-800">
                        {{ $client->phone ?: __('Téléphone non renseigné') }}
                    </span>
                </div>

                <div class="flex items-start gap-2">
                    <i class="fas fa-map-marker-alt text-[11px] text-gray-400 w-4 mt-0.5"></i>
                    <span class="text-gray-800 text-xs">
                        {{ $client->address ?: __('Adresse non renseignée') }}
                        @if($client->birthdate)
                            <br>
                            <span class="text-[11px] text-gray-500">
                                🎂 {{ __('Né(e) le') }} {{ $client->birthdate }}
                            </span>
                        @endif
                    </span>
                </div>
            </div>

            {{-- Quick actions --}}
            <div class="mt-3 grid grid-cols-2 gap-2 text-[11px]">
                @if($client->email)
                    <a href="mailto:{{ $client->email }}"
                       class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-[#647a0b]/10 text-[#647a0b] font-medium active:scale-[0.99]">
                        <i class="fas fa-paper-plane text-[10px] mr-1.5"></i>
                        {{ __('Envoyer un email') }}
                    </a>
                @endif

                @if($client->phone)
                    <a href="tel:{{ $client->phone }}"
                       class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-white border border-[#e4e8d5] text-gray-800 font-medium active:scale-[0.99]">
                        <i class="fas fa-phone-alt text-[10px] mr-1.5"></i>
                        {{ __('Appeler') }}
                    </a>
                @endif
            </div>
        </div>

        {{-- =======================
             COMPTE & INVITATION
             ======================= --}}
        <div class="rounded-2xl border border-[#e4e8d5] bg-white p-4 shadow-sm space-y-3">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                {{ __('Espace client & invitation') }}
            </h2>

            @if(!$hasEmail)
                <p class="text-xs text-red-600">
                    {{ __('Ce client n’a pas d’adresse email, l’invitation ne peut pas être envoyée.') }}
                </p>
            @elseif($hasPortalAccount)
                <p class="text-xs text-green-700">
                    ✅ {{ __('Ce client a déjà activé son compte.') }}
                </p>
            @elseif($inviteStillValid)
                <p class="text-xs text-yellow-700">
                    🔁 {{ __('Invitation déjà envoyée.') }}<br>
                    <span class="text-[11px] text-gray-600">
                        {{ __('Valable jusqu’au') }} {{ $client->password_setup_expires_at->format('d/m/Y H:i') }}.
                    </span>
                </p>

                <form action="{{ route('client.invite', $client) }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-white border border-[#e4e8d5] text-[11px] font-medium text-gray-800 active:scale-[0.99] w-full">
                        <i class="fas fa-sync text-[10px] mr-1.5"></i>
                        {{ __('Renvoyer une invitation') }}
                    </button>
                </form>
            @else
                <p class="text-xs text-gray-600">
                    {{ __('Ce client n’a pas encore été invité à créer son compte ou l’invitation est expirée.') }}
                </p>

                @if($hasEmail)
                    <form action="{{ route('client.invite', $client) }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-[#647a0b] text-white text-[11px] font-medium active:scale-[0.99] w-full">
                            <i class="fas fa-envelope-open-text text-[10px] mr-1.5"></i>
                            {{ __('Envoyer une invitation') }}
                        </button>
                    </form>
                @endif
            @endif

            {{-- Témoignage --}}
            @can('requestTestimonial', $client)
                <div class="mt-3 border-t border-[#f1f3e6] pt-3 text-xs">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-gray-700 font-medium">
                            ⭐ {{ __('Demande de témoignage') }}
                        </span>

                        @if(is_null($testimonialRequest))
                            <form action="{{ route('testimonial.request', ['clientProfile' => $client->id]) }}"
                                  method="POST" class="flex-shrink-0">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center px-2.5 py-1 rounded-full bg-white border border-[#e4e8d5] text-[10px] text-gray-800">
                                    {{ __('Demander un témoignage') }}
                                </button>
                            </form>
                        @elseif($testimonialRequest->status === 'pending')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 text-[10px]">
                                {{ __('Demande envoyée le') }} {{ $testimonialRequest->created_at->format('d/m/Y') }}
                            </span>
                        @elseif($testimonialRequest->status === 'completed')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-green-50 text-green-700 text-[10px]">
                                {{ __('Témoignage reçu le') }} {{ $testimonialRequest->updated_at->format('d/m/Y') }}
                            </span>
                        @endif
                    </div>
                </div>
            @endcan
        </div>

        {{-- =======================
             RENDEZ-VOUS RÉCENTS
             ======================= --}}
        <div class="rounded-2xl border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    {{ __('Rendez-vous récents') }}
                </h2>

                <a href="{{ route('client_profiles.show', $client->id) }}#Rendez-vous"
                   class="text-[11px] text-[#647a0b] inline-flex items-center gap-1">
                    {{ __('Ouvrir sur le web') }}
                    <i class="fas fa-external-link-alt text-[9px]"></i>
                </a>
            </div>

            @if($appointments->isEmpty())
                <p class="text-xs text-gray-500">
                    {{ __('Aucun rendez-vous trouvé pour ce client.') }}
                </p>
            @else
                <div class="space-y-2">
                    @foreach($appointments as $appt)
                        @php
                            $date   = $appt->appointment_date;
                            $status = ucfirst($appt->status ?? 'en attente');
                            $statusClasses = match ($appt->status) {
                                'Complété'    => 'bg-green-50 text-green-700 border-green-100',
                                'Annulé'      => 'bg-red-50 text-red-700 border-red-100',
                                default       => 'bg-slate-50 text-slate-700 border-slate-100',
                            };
                        @endphp

                        <a href="{{ route('mobile.appointments.show', $appt->id) }}"
                           class="block rounded-xl border border-[#e4e8d5] bg-white px-3 py-2.5 text-[11px] active:scale-[0.99]">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="text-[10px] text-gray-400 flex items-center gap-1.5">
                                        <i class="fas fa-calendar-alt text-[9px]"></i>
                                        {{ $date?->format('d/m/Y') ?? '—' }}
                                    </p>
                                    <p class="mt-0.5 text-[11px] text-gray-800 flex items-center gap-1.5">
                                        <i class="fas fa-clock text-[9px] text-[#647a0b]"></i>
                                        {{ $date?->format('H:i') ?? '—' }}
                                        @if($appt->duration)
                                            <span class="mx-1 text-gray-300">•</span>
                                            <span class="text-gray-500">{{ $appt->duration }} min</span>
                                        @endif
                                    </p>
                                    <p class="mt-0.5 text-[11px] text-gray-600 truncate">
                                        {{ optional($appt->product)->name ?? __('Sans prestation') }}
                                    </p>
                                </div>

                                <span class="inline-flex items-center px-2 py-0.5 rounded-full border {{ $statusClasses }} text-[10px] font-semibold whitespace-nowrap">
                                    {{ $status }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="mt-3 grid grid-cols-2 gap-2 text-[11px]">
                <a href="{{ route('appointments.create', $client->id) }}"
                   class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-[#647a0b] text-white font-medium active:scale-[0.99]">
                    <i class="fas fa-plus text-[10px] mr-1.5"></i>
                    {{ __('Nouveau rendez-vous') }}
                </a>

                <a href="{{ route('client_profiles.show', $client->id) }}#Rendez-vous"
                   class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-white border border-[#e4e8d5] text-gray-800 font-medium active:scale-[0.99]">
                    <i class="fas fa-table text-[10px] mr-1.5"></i>
                    {{ __('Voir tous les RDV ') }}
                </a>
            </div>
        </div>

        {{-- =======================
             MESSAGERIE (APERÇU)
             ======================= --}}
        <div class="rounded-2xl border border-[#e4e8d5] bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    {{ __('Messagerie') }}
                </h2>

                <a href="{{ route('client_profiles.show', $client->id) }}#Messagerie"
                   class="text-[11px] text-[#647a0b] inline-flex items-center gap-1">
                    {{ __('Voir sur le web') }}
                    <i class="fas fa-external-link-alt text-[9px]"></i>
                </a>
            </div>

            <div id="mobileMessageList"
                 class="space-y-2 mb-3 max-h-56 overflow-y-auto text-[11px]">
                @forelse($latestMessages as $msg)
                    <div class="{{ $msg->sender_type === 'client' ? 'text-right' : 'text-left' }}">
                        <div class="inline-block px-3 py-2 rounded-lg
                            {{ $msg->sender_type === 'client' ? 'bg-indigo-100 text-gray-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $msg->content }}
                        </div>
                        <div class="text-[10px] text-gray-400 mt-1">
                            {{ $msg->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-500">
                        {{ __('Aucun message échangé pour le moment.') }}
                    </p>
                @endforelse
            </div>

            <form id="mobileTherapistMessageForm" class="mt-1">
                @csrf
                <textarea name="content"
                          id="mobileTherapistMessageContent"
                          rows="2"
                          class="w-full border border-[#e4e8d5] rounded-xl px-3 py-2 text-[12px]"
                          placeholder="Écrivez un message..."></textarea>
                <button type="submit"
                        class="mt-2 inline-flex items-center justify-center w-full px-3 py-2 rounded-xl bg-indigo-600 text-white text-[12px] font-medium active:scale-[0.99]">
                    <i class="fas fa-paper-plane text-[11px] mr-1.5"></i>
                    {{ __('Envoyer') }}
                </button>
            </form>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const form   = document.getElementById('mobileTherapistMessageForm');
                    const input  = document.getElementById('mobileTherapistMessageContent');
                    const list   = document.getElementById('mobileMessageList');
                    const csrf   = form.querySelector('input[name="_token"]').value;

                    const scrollToBottom = () => {
                        if (list) list.scrollTop = list.scrollHeight;
                    };
                    scrollToBottom();

                    form.addEventListener('submit', function (e) {
                        e.preventDefault();
                        const content = input.value.trim();
                        if (!content.length) return;

                        fetch("{{ route('messages.therapist.store', $client->id) }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({content})
                        }).then(res => res.json())
                          .then(res => {
                              if (res.success) {
                                  const now = new Date().toLocaleString('fr-FR');
                                  const bubble = `
                                      <div class="text-sm text-left">
                                          <div class="inline-block px-3 py-2 rounded-lg bg-gray-100 text-gray-800">
                                              ${content.replace(/</g, '&lt;')}
                                          </div>
                                          <div class="text-[10px] text-gray-400 mt-1">${now}</div>
                                      </div>`;
                                  list.insertAdjacentHTML('beforeend', bubble);
                                  input.value = '';
                                  scrollToBottom();
                              }
                          });
                    });
                });
            </script>
        </div>

        {{-- =======================
             FICHIERS & DOCUMENTS (SYNTHÈSE)
             ======================= --}}
        <div class="rounded-2xl border border-[#e4e8d5] bg-white p-4 shadow-sm space-y-3">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                {{ __('Fichiers & documents') }}
            </h2>

            <div class="grid grid-cols-2 gap-3 text-[11px]">
                <div class="rounded-xl border border-[#f1f3e6] bg-[#fafcf4] px-3 py-2">
                    <p class="text-[10px] text-gray-500">
                        {{ __('Fichiers importés') }}
                    </p>
                    <p class="mt-0.5 text-sm font-semibold text-gray-900">
                        {{ $filesCount }}
                    </p>
                </div>

                <div class="rounded-xl border border-[#f1f3e6] bg-[#fafcf4] px-3 py-2">
                    <p class="text-[10px] text-gray-500">
                        {{ __('Documents à signer / signés') }}
                    </p>
                    <p class="mt-0.5 text-sm font-semibold text-gray-900">
                        {{ $documentsCount }}
                    </p>
                </div>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-2 text-[11px]">
                <a href="{{ route('client_profiles.show', $client->id) }}#Fichiers-&-Documents"
                   class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-white border border-[#e4e8d5] text-gray-800 font-medium active:scale-[0.99]">
                    <i class="fas fa-folder-open text-[10px] mr-1.5"></i>
                    {{ __('Gérer sur le web') }}
                </a>

                <a href="{{ route('client_profiles.show', $client->id) }}#documents-signing"
                   class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-white border border-[#e4e8d5] text-gray-800 font-medium active:scale-[0.99]">
                    <i class="fas fa-file-signature text-[10px] mr-1.5"></i>
                    {{ __('Signatures ') }}
                </a>
            </div>
        </div>

        {{-- =======================
             ACTIONS BAS DE PAGE
             ======================= --}}
        <div class="space-y-2">
            <a href="{{ route('mobile.clients.index') }}"
               class="inline-flex items-center justify-center w-full px-3 py-2 rounded-xl bg-white border border-[#e4e8d5] text-[12px] font-medium text-gray-800 active:scale-[0.99]">
                <i class="fas fa-arrow-left text-[11px] mr-1.5"></i>
                {{ __('Retour à la liste des clients') }}
            </a>

            <a href="{{ route('client_profiles.edit', $client->id) }}"
               class="inline-flex items-center justify-center w-full px-3 py-2 rounded-xl bg-white border border-[#e4e8d5] text-[12px] font-medium text-gray-800 active:scale-[0.99]">
                <i class="fas fa-edit text-[11px] mr-1.5"></i>
                {{ __('Modifier le profil ') }}
            </a>
        </div>
    </div>
</x-mobile-layout>
