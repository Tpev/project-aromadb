<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Détails du profil client') }} - {{ $clientProfile->first_name }} {{ $clientProfile->last_name }}
        </h2>
    </x-slot>

    {{-- Font Awesome for icons (used by info boxes) --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">
                {{ __('Profil de ') }}{{ $clientProfile->first_name }} {{ $clientProfile->last_name }}
            </h1>
@if($clientProfile->company)
    <div class="bg-white border-l-4 border-[#647a0b] p-4 rounded-md mb-4 flex items-center justify-between">
        <div>
            <p class="font-semibold text-[#647a0b]">
                👔 Client rattaché à une entreprise
            </p>
            <p class="text-sm text-gray-700">
                {{ __('Entreprise :') }}
                <a href="{{ route('corporate-clients.show', $clientProfile->company) }}"
                   class="underline text-[#854f38]">
                    {{ $clientProfile->company->name }}
                </a>
            </p>
        </div>
        <span class="badge badge-required">
            Entreprise
        </span>
    </div>
@endif

<hr class="my-6">




            <!-- ==========================
                 SECTION: Invitation au compte
                 ========================== -->
            <section class="bg-white p-6 rounded-2xl shadow mt-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">🧾 Invitation à l’espace client</h2>

                @if(!$clientProfile->email)
                    <p class="text-red-600 font-semibold">Ce client n’a pas d’adresse email, l’invitation ne peut pas être envoyée.</p>
                @elseif($clientProfile->password)
                    <p class="text-green-700 font-semibold">
                        ✅ Ce client a déjà activé son compte.
                    </p>
                @elseif($clientProfile->password_setup_token_hash && $clientProfile->password_setup_expires_at && $clientProfile->password_setup_expires_at->isFuture())
                    <p class="text-yellow-700">
                        🔁 Une invitation a déjà été envoyée le
                        <strong>{{ $clientProfile->password_setup_expires_at->subDays(3)->format('d/m/Y H:i') }}</strong>. <br>
                        Elle est valable jusqu’au <strong>{{ $clientProfile->password_setup_expires_at->format('d/m/Y H:i') }}</strong>.
                    </p>

                    <form action="{{ route('client.invite', $clientProfile) }}" method="POST" class="mt-4">
                        @csrf
                        <button type="submit" class="btn-secondary">
                            ❗Renvoyer une nouvelle invitation
                        </button>
                    </form>
                @else
                    <p class="text-gray-600">Ce client n’a pas encore été invité à créer son compte.</p>

                    <form action="{{ route('client.invite', $clientProfile) }}" method="POST" class="mt-4">
                        @csrf
                        <button type="submit" class="btn-primary">
                            📩 Envoyer une invitation
                        </button>
                    </form>
                @endif
            </section>

            <!-- ==========================
                 Infos profil (cartes)
                 ========================== -->
            <div class="profile-info-boxes row mt-4">
                <div class="col-md-6">
                    <div class="profile-box">
                        <i class="fas fa-user-circle icon"></i>
                        <div class="profile-details">
                            <p class="profile-label">{{ __('Prénom') }}</p>
                            <p class="profile-value">{{ $clientProfile->first_name }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="profile-box">
                        <i class="fas fa-user icon"></i>
                        <div class="profile-details">
                            <p class="profile-label">{{ __('Nom') }}</p>
                            <p class="profile-value">{{ $clientProfile->last_name }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="profile-box">
                        <i class="fas fa-envelope icon"></i>
                        <div class="profile-details">
                            <p class="profile-label">{{ __('Email') }}</p>
                            <p class="profile-value">{{ $clientProfile->email ?? 'Non spécifié' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="profile-box">
                        <i class="fas fa-phone icon"></i>
                        <div class="profile-details">
                            <p class="profile-label">{{ __('Téléphone') }}</p>
                            <p class="profile-value">{{ $clientProfile->phone ?? 'Non spécifié' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="profile-box">
                        <i class="fas fa-birthday-cake icon"></i>
                        <div class="profile-details">
                            <p class="profile-label">{{ __('Date de naissance') }}</p>
                            <p class="profile-value">
                                {{ $clientProfile->birthdate ? $clientProfile->birthdate : 'Non spécifié' }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="profile-box">
                        <i class="fas fa-map-marker-alt icon"></i>
                        <div class="profile-details">
                            <p class="profile-label">{{ __('Adresse') }}</p>
                            <p class="profile-value">{{ $clientProfile->address ?? 'Non spécifié' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @can('requestTestimonial', $clientProfile)
                @if(is_null($testimonialRequest))
                    <!-- Aucun demande envoyée -->
                    <form action="{{ route('testimonial.request', ['clientProfile' => $clientProfile->id]) }}" method="POST" class="mt-6">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            {{ __('Demander un Témoignage') }}
                        </button>
                    </form>
                @elseif($testimonialRequest->status === 'pending')
                    <!-- Demande envoyée -->
                    <p class="mt-6 text-lg text-gray-600">
                        {{ __('Demande envoyée le') }} {{ $testimonialRequest->created_at->format('d/m/Y') }}.
                    </p>
                @elseif($testimonialRequest->status === 'completed')
                    <!-- Témoignage fait -->
                    <p class="mt-6 text-lg text-gray-600">
                        {{ __('Témoignage fait le') }} {{ $testimonialRequest->updated_at->format('d/m/Y') }}.
                    </p>
                @endif
            @endcan
            <!-- ==========================
                 Rendez-vous
                 ========================== -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h2 class="details-title">{{ __('Rendez-vous de ce client') }}</h2>
                    <a href="{{ route('appointments.create', $clientProfile->id) }}" class="btn btn-primary mb-3">
                        {{ __('Créer un Rendez-vous') }}
                    </a>

                    @if($appointments->isEmpty())
                        <p>Aucun rendez-vous trouvé pour ce client.</p>
                    @else
                        <div class="table-responsive mx-auto">
                            <table class="table table-bordered table-hover" id="appointmentsTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Statut</th>
                                        <th>Prestation</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($appointments as $appointment)
                                        @php
                                            $em = \App\Models\Emargement::where('appointment_id', $appointment->id)->latest()->first();
                                            $requires = (bool) optional($appointment->product)->requires_emargement;
                                            $hasEmail = !empty(optional($appointment->clientProfile)->email);
                                            $canSend  = $requires && !$em && $hasEmail;

                                            $emBadge = '';
                                            if ($em) {
                                                if ($em->status === 'signed') {
                                                    $emBadge = '<span class="badge badge-signed">Signé</span>';
                                                } elseif ($em->status === 'expired') {
                                                    $emBadge = '<span class="badge badge-expired">Expiré</span>';
                                                } else {
                                                    $emBadge = '<span class="badge badge-pending">Émargement en attente</span>';
                                                }
                                            } elseif ($requires) {
                                                $emBadge = '<span class="badge badge-required">Émargement requis</span>';
                                            }
                                        @endphp

                                        <tr>
                                            <td>{{ $appointment->appointment_date }}</td>
                                            <td>{{ ucfirst($appointment->status) }}</td>
                                            <td>{{ optional($appointment->product)->name ?? '—' }}</td>
                                            <td>{{ $appointment->notes ?? 'Pas de notes' }}</td>
                                            <td style="min-width:280px; text-align:center">
                                                <a href="{{ route('appointments.show', $appointment->id) }}" class="btn btn-primary">Voir</a>
                                                <a href="{{ route('appointments.edit', $appointment->id) }}" class="btn btn-secondary">Modifier</a>

                                                @if($canSend || $em)
                                                    <div class="mt-2">
                                                        {!! $emBadge !!}
                                                    </div>

                                                    @if($canSend)
                                                        <form action="{{ route('emargement.send', $appointment->id) }}"
                                                              method="POST"
                                                              style="display:inline-block; margin-top:6px;">
                                                            @csrf
                                                            <button type="submit" class="btn-primary" title="Envoyer la feuille d’émargement">
                                                                Envoyer signature
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if($em && $em->status === 'pending' && method_exists($em, 'isExpired') && !$em->isExpired())
                                                        <form action="{{ route('emargement.resend', $em->id) }}"
                                                              method="POST"
                                                              style="display:inline-block; margin-top:6px;">
                                                            @csrf
                                                            <button type="submit" class="btn-secondary" title="Renvoyer le lien de signature">
                                                                Renvoyer lien
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if($em && $em->pdf_path)
                                                        <a href="{{ route('emargement.download', $em->id) }}"
                                                           class="btn-secondary"
                                                           style="margin-top:6px; display:inline-block;"
                                                           title="Télécharger le justificatif PDF">
                                                            Télécharger PDF
                                                        </a>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>

                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- ==========================
                 Notes de séance
                 ========================== -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h2 class="details-title">{{ __('Notes de Séance de ce client') }}</h2>
                    <a href="{{ route('session_notes.create', $clientProfile->id) }}" class="btn btn-primary mb-3">
                        {{ __('Créer une Note de Séance') }}
                    </a>

                    @if($sessionNotes->isEmpty())
                        <p>Aucune note trouvée pour ce client.</p>
                    @else
                        <div class="table-responsive mx-auto">
                            <table class="table table-bordered table-hover" id="sessionNotesTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Note</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sessionNotes as $sessionNote)
                                        <tr>
                                            <td>{{ $sessionNote->created_at }}</td>
                                            <td>{!! Str::limit(strip_tags($sessionNote->note), 50) !!}</td>
                                            <td>
                                                <a href="{{ route('session_notes.show', $sessionNote->id) }}" class="btn btn-primary">Voir</a>
                                                <a href="{{ route('session_notes.edit', $sessionNote->id) }}" class="btn btn-secondary">Modifier</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- ==========================
                 Réponses questionnaires
                 ========================== -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h2 class="details-title">{{ __('Réponses aux Questionnaires de ce client') }}</h2>
                    <a href="{{ route('questionnaires.send.show', $clientProfile->id) }}" class="btn btn-primary mb-3">
                        {{ __('Remplir / Envoyer un questionnaire') }}
                    </a>

                    @if($responses->isEmpty())
                        <p>Aucune réponse trouvée pour ce client.</p>
                    @else
                        <div class="table-responsive mx-auto">
                            <table class="table table-bordered table-hover" id="responsesTable">
                                <thead>
                                    <tr>
                                        <th>Questionnaire</th>
                                        <th>Date de Soumission</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($responses as $response)
                                        <tr>
                                            <td>{{ $response->questionnaire->title }}</td>
                                            <td>{{ $response->created_at }}</td>
                                            <td>
                                                @if($response->answers === '[]')
                                                    <span class="text-muted">{{ __('Pas encore rempli') }}</span>
                                                @else
                                                    <a href="{{ route('questionnaires.responses.show', $response->id) }}" class="btn btn-primary">Voir Réponse</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- ==========================
                 Conseils envoyés
                 ========================== -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h2 class="details-title">{{ __('Conseils Envoyés à ce client') }}</h2>
                    <a href="{{ route('client_profiles.conseils.sendform', $clientProfile->id) }}" class="btn btn-primary mb-3">
                        {{ __('Envoyer un Conseil') }}
                    </a>

                    @php $conseilsSent = $clientProfile->conseilsSent ?? collect(); @endphp

                    @if($conseilsSent->isEmpty())
                        <p>Aucun conseil envoyé à ce client pour le moment.</p>
                    @else
                        <div class="table-responsive mx-auto">
                            <table class="table table-bordered table-hover" id="conseilsSentTable">
                                <thead>
                                    <tr>
                                        <th>Nom du Conseil</th>
                                        <th>Tag</th>
                                        <th>Date d'Envoi</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($conseilsSent as $conseil)
                                        <tr>
                                            <td>{{ $conseil->name }}</td>
                                            <td>{{ $conseil->tag ?? '—' }}</td>
                                            <td>
                                                {{ optional($conseil->pivot)->created_at
                                                    ? $conseil->pivot->created_at->format('d/m/Y')
                                                    : '—'
                                                }}
                                            </td>
                                            <td>
                                                <a href="{{ route('conseils.show', $conseil->id) }}" class="btn btn-primary">Voir Conseil</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- ==========================
                 Mesures (metrics)
                 ========================== -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h2 class="details-title">{{ __('Mesures du client') }}</h2>

                    <a href="{{ route('client_profiles.metrics.create', $clientProfile->id) }}" class="btn btn-primary mb-3">
                        {{ __('Créer une Nouvelle Mesure') }}
                    </a>

                    @if($clientProfile->metrics->isEmpty())
                        <p>Aucune mesure trouvée pour ce client.</p>
                    @else
                        <div class="table-responsive mx-auto">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Objectif</th>
                                        <th>Dernière Entrée</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($clientProfile->metrics as $metric)
                                        @php $lastEntry = $metric->entries->sortByDesc('entry_date')->first(); @endphp
                                        <tr>
                                            <td>{{ $metric->name }}</td>
                                            <td>{{ $metric->goal ?? 'N/A' }}</td>
                                            <td>
                                                @if($lastEntry)
                                                    {{ $lastEntry->value }} ( {{ $lastEntry->entry_date }} )
                                                @else
                                                    Aucune entrée
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('client_profiles.metrics.show', [$clientProfile->id, $metric->id]) }}"
                                                   class="btn btn-primary">Voir</a>

                                                <a href="{{ route('client_profiles.metrics.edit', [$clientProfile->id, $metric->id]) }}"
                                                   class="btn btn-secondary">Modifier</a>

                                                <form action="{{ route('client_profiles.metrics.destroy', [$clientProfile->id, $metric->id]) }}"
                                                      method="POST" style="display: inline-block;"
                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette mesure ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-danger">Supprimer</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- ==========================
                 Messagerie
                 ========================== -->
            <section class="bg-white p-6 rounded-2xl shadow">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">💬 Messagerie</h2>

                <div id="messageList" class="space-y-2 mb-4 max-h-64 overflow-y-auto">
                    @foreach($clientProfile->messages->sortBy('created_at') as $msg)
                        <div class="text-sm {{ $msg->sender_type === 'client' ? 'text-right' : 'text-left' }}" data-id="{{ $msg->id }}">
                            <div class="inline-block px-3 py-2 rounded-lg {{ $msg->sender_type === 'client' ? 'bg-indigo-100' : 'bg-gray-100' }}">
                                {{ $msg->content }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $msg->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <form id="therapistMessageForm">
                    @csrf
                    <textarea name="content" id="therapistMessageContent" rows="2" class="w-full border rounded px-3 py-2" placeholder="Écrivez un message..."></textarea>
                    <button type="submit" class="mt-2 bg-indigo-600 text-white px-4 py-2 rounded">Envoyer</button>
                </form>
            </section>

            <script>
            document.getElementById('therapistMessageForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const content = document.getElementById('therapistMessageContent').value;
                const csrf = document.querySelector('input[name="_token"]').value;

                fetch("{{ route('messages.therapist.store', $clientProfile->id) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ content })
                }).then(res => res.json())
                .then(res => {
                    if (res.success) {
                        const list = document.getElementById('messageList');
                        const now = new Date().toLocaleString('fr-FR');
                        const bubble = `
                            <div class="text-sm text-left">
                                <div class="inline-block px-3 py-2 rounded-lg bg-gray-100">
                                    ${content}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">${now}</div>
                            </div>`;
                        list.insertAdjacentHTML('beforeend', bubble);
                        document.getElementById('therapistMessageContent').value = '';
                        list.scrollTop = list.scrollHeight;
                    }
                });
            });

            window.addEventListener('DOMContentLoaded', () => {
                const messageList = document.getElementById('messageList');
                if (messageList) {
                    messageList.scrollTop = messageList.scrollHeight;
                }
            });

            function fetchLatestMessages() {
                fetch("{{ route('therapist.messages.fetch', $clientProfile->id) }}")
                    .then(res => res.json())
                    .then(messages => {
                        const list = document.getElementById('messageList');
                        list.innerHTML = '';

                        messages.forEach(msg => {
                            const bubble = `
                                <div class="text-sm ${msg.sender_type === 'client' ? 'text-right' : 'text-left'}">
                                    <div class="inline-block px-3 py-2 rounded-lg ${msg.sender_type === 'client' ? 'bg-indigo-100' : 'bg-gray-100'}">
                                        ${msg.content}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">${msg.timestamp}</div>
                                </div>`;
                            list.insertAdjacentHTML('beforeend', bubble);
                        });

                        list.scrollTop = list.scrollHeight;
                    });
            }

            window.addEventListener('DOMContentLoaded', fetchLatestMessages);
            setInterval(fetchLatestMessages, 10000);
            </script>

            <!-- ==========================
                 Fichiers classiques (si tu veux garder)
                 ========================== -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h2 class="details-title">Fichiers du Client</h2>

                    <form
                        action="{{ route('client_profiles.files.store', $clientProfile->id) }}"
                        method="POST"
                        enctype="multipart/form-data"
                        class="mb-4"
                    >
                        @csrf
                        <div class="form-group">
                            <label for="file">Choisir un fichier</label>
                            <input
                                type="file"
                                name="file"
                                id="file"
                                class="form-control"
                                required
                            >
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">
                            Importer
                        </button>
                    </form>

                    @if (session('success'))
                        <div class="mb-4 text-green-600 font-bold">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($clientProfile->clientFiles->isEmpty())
                        <p>Aucun fichier n’a encore été téléchargé pour ce client.</p>
                    @else
                        <div class="table-responsive mx-auto">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Nom du Fichier</th>
                                        <th>Taille</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($clientProfile->clientFiles as $file)
                                        <tr>
                                            <td>{{ $file->original_name }}</td>
                                            <td>
                                                @if($file->size)
                                                    {{ round($file->size / 1024, 2) }} KB
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <a
                                                    href="{{ route('client_profiles.files.download', [$clientProfile->id, $file->id]) }}"
                                                    class="btn btn-primary"
                                                >
                                                    Télécharger
                                                </a>

                                                <form
                                                    action="{{ route('client_profiles.files.destroy', [$clientProfile->id, $file->id]) }}"
                                                    method="POST"
                                                    style="display:inline-block;"
                                                    onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce fichier ?');"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-danger">
                                                        Supprimer
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                </div>
            </div>
{{-- =========================================================
     SECTION: Documents à signer (toujours possible d’en créer un nouveau)
	 
     ========================================================= --}}
<section class="bg-white p-6 rounded-2xl shadow mt-6" id="documents-signing">
                    <h2 class="details-title">Signature de document</h2>
    <div class="flex items-center justify-between mb-2 gap-3 flex-wrap">
        <h2 class="text-xl font-semibold text-gray-800">📑 Documents à signer</h2>

        <div class="flex items-center gap-2">
            <button type="button"
                    class="btn-secondary"
                    onclick="document.getElementById('docfile')?.click()">
                ➕ Nouveau document
            </button>
        </div>
    </div>

    {{-- Rappel email d’envoi --}}
    <div class="text-sm text-gray-600 mb-4">
        Adresse d’envoi :
        @if($clientProfile->email)
            <strong>{{ $clientProfile->email }}</strong>
        @else
            <strong class="text-red-600">Aucune — ajoutez un email au profil pour activer l’envoi</strong>
        @endif
    </div>

    {{-- 🔽 FORMULAIRE D’IMPORT — visible en permanence, totalement indépendant des autres docs --}}
    <form action="{{ route('documents.store', $clientProfile->id) }}"
          method="POST" enctype="multipart/form-data"
          class="grid gap-4 md:grid-cols-12 border border-dashed border-gray-300 rounded-xl p-4 mb-5">
        @csrf
        <div class="md:col-span-6">
            <label for="docfile" class="block font-semibold mb-1">Sélectionner un PDF</label>
            <input type="file" id="docfile" name="file" accept="application/pdf" required
                   class="w-full border rounded px-3 py-2">
            <p class="text-sm text-muted mt-1">Formats acceptés : PDF (max. 20 Mo).</p>
        </div>

        <div class="md:col-span-4">
            <label for="appointment_id" class="block font-semibold mb-1">Lier à un rendez-vous (optionnel)</label>
            <select id="appointment_id" name="appointment_id" class="w-full border rounded px-3 py-2">
                <option value="">— Aucun —</option>
                @foreach($appointments as $appt)
                    <option value="{{ $appt->id }}">
                        #{{ $appt->id }} — {{ $appt->appointment_date }} — {{ optional($appt->product)->name ?? 'Prestation' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-2 flex items-end">
            <button type="submit" class="btn-primary w-full">Importer</button>
        </div>
    </form>

    {{-- Flash / erreurs --}}
    @if(session('success'))
        <div class="mt-3 text-green-700 font-semibold">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mt-3 text-red-700 font-semibold">
            {{ implode(' ', $errors->all()) }}
        </div>
    @endif

@php
    $documents = \App\Models\Document::with(['signing','signEvents'])
        ->where('client_profile_id', $clientProfile->id)
        ->latest()
        ->get();

    $statusColor = [
        'draft'             => 'badge-required',
        'sent'              => 'badge-pending',
        'partially_signed'  => 'badge-pending',
        'signed'            => 'badge-signed',
        'expired'           => 'badge-expired',
        'cancelled'         => 'badge-expired',
    ];

    $newId = session('new_document_id'); // optional highlight
@endphp

    @if($documents->isEmpty())
        <p class="text-muted">Aucun document n’a encore été importé pour ce client.</p>
    @else
        <div class="table-responsive mt-2">
            <table class="table table-bordered table-hover" id="documentsTable">
                <thead>
                <tr>
                    <th>Fichier</th>
                    <th>Statut</th>
                    <th>Signature(s)</th>
                    <th>Échéance</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($documents as $doc)
                    @php
                        $signing    = $doc->signing; // dernière demande (1:1 avec ce doc)
                        $events     = $doc->signEvents;
                        $signedCnt  = $events->count();
                        $expires    = $signing?->expires_at;
                        $isExpired  = $signing?->isExpired() ?? false;
                        $badgeClass = $statusColor[$doc->status] ?? 'badge-required';
                        $publicUrl  = $signing? route('documents.sign.form', $signing->token) : null;

                        // Permissions d’action
                        $canResend    = $signing && !$isExpired && $signing->status !== 'signed';
                        $canDownload  = $doc->final_pdf_path && \Storage::disk('public')->exists($doc->final_pdf_path);

                        // 🔒 Envoi autorisé uniquement si email ET statut = draft
                        $canSend      = $clientProfile->email && $doc->status === 'draft';
                    @endphp
                    <tr data-doc-id="{{ $doc->id }}" @if($newId && $newId == $doc->id) style="outline:3px solid #647a0b44;" @endif>
                        <td title="{{ $doc->original_name }}">
                            <div class="flex items-center gap-2">
                                <i class="far fa-file-pdf"></i>
                                <a href="{{ asset('storage/'.$doc->storage_path) }}" target="_blank">
                                    {{ \Illuminate\Support\Str::limit($doc->original_name, 48) }}
                                </a>
                            </div>
                            @if($publicUrl)
                                <div class="text-xs text-muted mt-1">
                                    Lien de signature :
                                    <span class="underline break-all">{{ $publicUrl }}</span>
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $badgeClass }}">
                                {{ match($doc->status){
                                    'draft' => 'Brouillon',
                                    'sent' => 'Envoyé',
                                    'partially_signed' => 'Partiellement signé',
                                    'signed' => 'Signé',
                                    'expired' => 'Expiré',
                                    'cancelled' => 'Annulé',
                                    default => ucfirst($doc->status)
                                } }}
                            </span>
                        </td>
                        <td>
                            {{ $signedCnt }} évènement(s)
                            @if($signedCnt > 0)
                                <span class="text-muted">
                                    — dernier : {{ optional($events->sortByDesc('signed_at')->first()?->signed_at)->format('d/m/Y H:i') ?? '—' }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($expires)
                                @if($isExpired)
                                    <span class="text-red-600 font-semibold">Expiré</span>
                                @else
                                    <span class="text-muted">Expire le {{ $expires->format('d/m/Y H:i') }}</span>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td style="min-width:420px">
                            {{-- Voir l’original --}}
                            <a href="{{ asset('storage/'.$doc->storage_path) }}" target="_blank" class="btn-secondary">Voir l’original</a>

                            {{-- Envoyer au client (désactivé dès que le doc n’est plus en brouillon) --}}
                            <form action="{{ route('documents.send', $doc->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                <button type="submit"
                                        class="{{ $canSend ? 'btn-primary' : 'btn-secondary opacity-60 cursor-not-allowed' }}"
                                        {{ $canSend ? '' : 'disabled' }}
                                        title="{{ $canSend ? 'Envoyer au client' : 'Déjà envoyé ou en cours de signature' }}">
                                    Envoyer au client
                                </button>
                            </form>

                            {{-- Renvoyer le lien (si en cours et non expiré) --}}
                            @if($canResend)
                                <form action="{{ route('documents.resend', $signing->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="btn-secondary">Renvoyer</button>
                                </form>
                            @endif

                            {{-- Copier le lien (si déjà généré) --}}
                            @if($publicUrl)
                                <button type="button" class="btn-secondary copy-link-btn" data-url="{{ $publicUrl }}">
                                    Copier le lien
                                </button>
                            @endif

                            {{-- Télécharger le PDF final (si dispo) --}}
                            @if($canDownload)
                                <a href="{{ route('documents.download.final', $doc->id) }}" class="btn-secondary">
                                    Télécharger le PDF final
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

{{-- Helpers UI: copy + surlignage nouveau document --}}
<script>
document.addEventListener('click', function(e){
    const btn = e.target.closest('.copy-link-btn');
    if(!btn) return;
    const url = btn.getAttribute('data-url');
    if(!url) return;
    navigator.clipboard.writeText(url).then(()=>{
        btn.textContent = 'Lien copié';
        setTimeout(()=>{ btn.textContent = 'Copier le lien'; }, 1500);
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const newId = @json(session('new_document_id'));
    if(newId){
        const row = document.querySelector(`[data-doc-id="${newId}"]`);
        if(row){
            row.scrollIntoView({behavior:'smooth', block:'center'});
            row.animate([{outlineColor:'#647a0b00'},{outlineColor:'#647a0b'},{outlineColor:'#647a0b00'}],
                        {duration:1600, iterations:1});
        }
    }
});
</script>

            <!-- ==========================
                 Actions bas de page
                 ========================== -->
            <div class="row mt-4">
                <div class="col-md-12 text-center">
                    <a href="{{ route('client_profiles.index') }}" class="btn-primary">
                        {{ __('Retour à la liste') }}
                    </a>
                    <a href="{{ route('client_profiles.edit', $clientProfile->id) }}" class="btn-secondary">
                        {{ __('Modifier le profil') }}
                    </a>
                    <form action="{{ route('client_profiles.destroy', $clientProfile->id) }}"
                          method="POST"
                          style="display: inline-block;"
                          onsubmit="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer ce profil client ? Cette action est irréversible.') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">{{ __('Supprimer le profil') }}</button>
                    </form>
                </div>
            </div>

        </div>
    </div>

<!-- Custom Styles -->
<style>
  /* ====== Design tokens ====== */
  :root{
    --brand:#647a0b;
    --brand-2:#854f38;
    --ink:#222;
    --muted:#666;
    --bg:#f9f9f9;
    --white:#fff;
    --radius:12px;
    --shadow:0 6px 18px rgba(0,0,0,.08);
  }

  /* ====== Badges (shown only when relevant) ====== */
  .badge{display:inline-block;padding:.35rem .6rem;border-radius:999px;font-size:.75rem;font-weight:700;line-height:1;}
  .badge-required{background:#fff3cd;color:#856404;border:1px solid #ffeeba;}
  .badge-pending {background:#e8f0fe;color:#1a73e8;border:1px solid #c6dafc;}
  .badge-signed  {background:#d4edda;color:#155724;border:1px solid #c3e6cb;}
  .badge-expired {background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;}

  /* ====== Container / Section ====== */
  .container{max-width:100%;width:100%;padding:0 1rem;}
  @media (min-width:768px){ .container{padding:0 2rem;} }

  .details-container{
    background:var(--bg);
    border-radius:var(--radius);
    padding:1.25rem;
    box-shadow:var(--shadow);
    margin:0 auto;
  }
  @media (min-width:992px){
    .details-container{padding:2rem;}
  }

  .details-title{
    font-size:clamp(1.35rem, 1.2rem + .8vw, 2rem);
    font-weight:800;
    color:var(--brand);
    margin:0 0 1rem;
    text-align:center;
  }

  /* ====== Profile info cards (grid) ====== */
  .profile-info-boxes{
    display:grid;
    grid-template-columns:1fr;
    gap:14px;
  }
  @media (min-width:640px){
    .profile-info-boxes{ grid-template-columns:repeat(2,minmax(0,1fr)); }
  }
  @media (min-width:1024px){
    .profile-info-boxes{ grid-template-columns:repeat(3,minmax(0,1fr)); }
  }

  .profile-box{
    display:flex; align-items:center; gap:14px;
    background:var(--white);
    border-radius:var(--radius);
    box-shadow:0 3px 10px rgba(0,0,0,.05);
    padding:16px 18px;
    transition:transform .2s ease, box-shadow .2s ease;
  }
  @media (hover:hover){
    .profile-box:hover{ transform:translateY(-2px); box-shadow:0 8px 20px rgba(0,0,0,.08); }
  }

  .icon{font-size:1.6rem;color:var(--brand-2);}
  .profile-details{text-align:left;}
  .profile-label{font-weight:700;color:var(--brand);margin:0 0 2px;}
  .profile-value{color:var(--ink);font-size:.975rem;line-height:1.35;}

  /* ====== Tables (full-width + mobile friendly) ====== */
  .table-responsive{
    width:100%; max-width:100%;
    overflow-x:auto; -webkit-overflow-scrolling:touch;
    background:var(--white);
    border-radius:10px;
    box-shadow:var(--shadow);
    padding:0; margin:0 auto;
  }
  .table{
    width:100%;
    border-collapse:collapse;
    min-width:720px; /* pour permettre le scroll horizontal sur mobile */
  }
  .table thead{ background:var(--brand); color:#fff; }
  .table th, .table td{
    padding:.85rem .9rem;
    text-align:left;
    border-bottom:1px solid #eee;
    white-space:nowrap;
  }
  .table tbody tr:nth-child(odd){ background:#fafafa; }
  .table tbody tr:hover{ background:#f3f7ea; }

  /* ====== Buttons ====== */
  .btn-primary,
  .btn-secondary,
  .btn-danger{
    display:inline-block; cursor:pointer; text-decoration:none;
    padding:.6rem 1rem; border-radius:8px; font-weight:700; line-height:1;
    transition:filter .15s ease, transform .05s ease;
  }
  .btn-primary{ background:var(--brand); color:#fff; border:0; }
  .btn-primary:hover{ filter:brightness(.95); }
  .btn-secondary{ background:transparent; color:var(--brand-2); border:1px solid var(--brand-2); }
  .btn-secondary:hover{ background:var(--brand-2); color:#fff; }
  .btn-danger{ background:#e3342f; color:#fff; border:0; }
  .btn-danger:hover{ background:#cc1f1a; }
  .btn-primary:active, .btn-secondary:active, .btn-danger:active{ transform:translateY(1px); }

  /* ====== Spacing helpers for sections ====== */
  section.bg-white{
    background:#fff;
    border-radius:var(--radius);
    box-shadow:var(--shadow);
    padding:1rem;
    margin-top:1.25rem;
  }
  @media (min-width:992px){
    section.bg-white{ padding:1.25rem 1.5rem; }
  }

  /* ====== Utility ====== */
  .text-muted{ color:var(--muted); }
  .text-center{ text-align:center; }

  /* Narrow screens: tighten paddings */
  @media (max-width:480px){
    .details-container{ padding:1rem; }
    .btn-primary, .btn-secondary, .btn-danger{ width:100%; text-align:center; }
  }
</style>

</x-app-layout>
