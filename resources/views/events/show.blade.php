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
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Détails de l\'Événement') }}
        </h2>
    </x-slot>

    <div class="page-container">
        <div class="event-layout">

            {{-- LEFT : Infos événement --}}
            <div class="event-panel">
                @if(session('success'))
                    <div class="alert alert-success animate__animated animate__fadeInDown">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger animate__animated animate__shakeX">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Event Title -->
                <h1 class="event-title">{{ $event->name }}</h1>

                <!-- Event Image -->
                @if($event->image)
                    <div class="event-image">
                        <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->name }}">
                    </div>
                @endif

                <!-- Event Information -->
                <div class="event-info mt-4">
                    @if($event->description)
                        <div class="info-box">
                            <h3 class="info-title"><i class="fas fa-info-circle mr-2"></i>{{ __('Description') }}</h3>
                            <p class="info-text">{{ $event->description }}</p>
                        </div>
                    @endif

                    <div class="info-box">
                        <h3 class="info-title"><i class="fas fa-calendar-alt mr-2"></i>{{ __('Date et Heure') }}</h3>
                        <p class="info-text">{{ \Carbon\Carbon::parse($event->start_date_time)->format('d/m/Y à H:i') }}</p>
                    </div>

                    <div class="info-box">
                        <h3 class="info-title"><i class="fas fa-hourglass-half mr-2"></i>{{ __('Durée') }}</h3>
                        <p class="info-text">{{ $event->duration }} {{ __('minutes') }}</p>
                    </div>

                    <div class="info-box">
                        <h3 class="info-title"><i class="fas fa-map-marker-alt mr-2"></i>{{ __('Lieu') }}</h3>
                        <p class="info-text">{{ $event->location }}</p>
                    </div>

                    <div class="info-box">
                        <h3 class="info-title"><i class="fas fa-ticket-alt mr-2"></i>{{ __('Réservation Requise') }}</h3>
                        <p class="info-text">{{ $event->booking_required ? __('Oui') : __('Non') }}</p>
                    </div>

                    <div class="info-box">
                        <h3 class="info-title"><i class="fas fa-users mr-2"></i>{{ __('Places Limitées') }}</h3>
                        <p class="info-text">{{ $event->limited_spot ? __('Oui') : __('Non') }}</p>
                        @if($event->limited_spot)
                            <p class="info-text">{{ __('Nombre de Places :') }} {{ $event->number_of_spot }}</p>
                        @endif
                    </div>

                    @if($event->associatedProduct)
                        <div class="info-box">
                            <h3 class="info-title"><i class="fas fa-box mr-2"></i>{{ __('Produit Associé') }}</h3>
                            <p class="info-text"><strong>{{ $event->associatedProduct->name }}</strong></p>
                            <p class="info-text">
                                {{ __('Prix TTC :') }}
                                {{ number_format($event->associatedProduct->price_incl_tax, 2, ',', ' ') }} €
                            </p>
                            @if($event->associatedProduct->description)
                                <p class="info-text">
                                    {{ __('Description du Produit :') }} {{ $event->associatedProduct->description }}
                                </p>
                            @endif
                        </div>
                    @endif

                    <div class="info-box">
                        <h3 class="info-title"><i class="fas fa-globe mr-2"></i>{{ __('Affiché sur le Portail') }}</h3>
                        <p class="info-text">{{ $event->showOnPortail ? __('Oui') : __('Non') }}</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons mt-6">
                    <a href="{{ route('events.edit', $event->id) }}" class="btn-primary">
                        <i class="fas fa-edit mr-2"></i>{{ __('Modifier') }}
                    </a>
                    <form action="{{ route('events.destroy', $event->id) }}" method="POST" class="inline-form"
                          onsubmit="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer cet événement ?') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">
                            <i class="fas fa-trash-alt mr-2"></i>{{ __('Supprimer') }}
                        </button>
                    </form>
                    <a href="{{ route('events.index') }}" class="btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>{{ __('Retour à la liste') }}
                    </a>
                </div>
            </div>

            {{-- RIGHT : Réservations --}}
            @if(Auth::id() === $event->user_id)
                <div class="reservations-panel">
                    <h2 class="section-title">{{ __('Liste des Réservations') }}</h2>

                    @php
                        $totalReservations = $event->reservations->count();
                        $availableSpots = $event->limited_spot ? $event->number_of_spot : '∞';
                    @endphp

                    <p class="booking-counter">
                        {{ __('Total Réservations :') }} {{ $totalReservations }} / {{ $availableSpots }}
                    </p>

                    @if($event->reservations->count() > 0)
                        <div class="table-wrapper">
                            <table class="table table-bordered mt-3">
                                <thead>
                                    <tr>
                                        <th>{{ __('N°') }}</th>
                                        <th>{{ __('Nom Complet') }}</th>
                                        <th>{{ __('Email') }}</th>
                                        <th>{{ __('Téléphone') }}</th>
                                        <th>{{ __('Date de Réservation') }}</th>
                                        <th>{{ __('Dossier client') }}</th>
                                        <th>{{ __('Actions') }}</th>
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
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $reservation->full_name }}</td>
                                            <td>{{ $reservation->email }}</td>
                                            <td>{{ $reservation->phone ?? __('N/A') }}</td>
                                            <td>{{ $reservation->created_at->format('d/m/Y H:i') }}</td>

                                            {{-- Colonne "Dossier client" --}}
                                            <td class="client-cell">
                                                @if($existingClientId)
                                                    <span class="pill pill-success">
                                                        {{ __('Client existant') }}
                                                    </span>
                                                    <a href="{{ route('client_profiles.show', $existingClientId) }}"
                                                       class="pill-link"
                                                       title="{{ __('Ouvrir le dossier client') }}">
                                                        {{ __('Voir le dossier') }}
                                                    </a>
                                                @elseif($reservation->email)
                                                    <button type="button"
                                                            class="btn-primary btn-xs js-create-client-from-reservation"
                                                            data-route="{{ route('reservations.createClient', ['event' => $event->id, 'reservation' => $reservation->id]) }}">
                                                        {{ __('Créer un profil') }}
                                                    </button>
                                                @else
                                                    <span class="pill pill-muted">
                                                        {{ __('Email manquant') }}
                                                    </span>
                                                @endif
                                            </td>

                                            <td>
                                                <form action="{{ route('reservations.destroy', $reservation->id) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer cette réservation ?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-danger btn-xs">
                                                        {{ __('Supprimer') }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p>{{ __('Aucune réservation pour le moment.') }}</p>
                    @endif
                </div>
            @endif
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
                                    <span class="pill pill-success">{{ __('Client créé') }}</span>
                                    ${data.client_profile_url
                                        ? `<a href="${data.client_profile_url}" class="pill-link">{{ __('Voir le dossier') }}</a>`
                                        : ''
                                    }
                                `;
                            }
                        } else {
                            alert(data.message || '{{ __("Une erreur est survenue lors de la création du profil client.") }}');
                            this.disabled = false;
                            this.textContent = '{{ __("Créer un profil") }}';
                        }
                    } catch (e) {
                        console.error(e);
                        alert('{{ __("Erreur réseau. Merci de réessayer.") }}');
                        this.disabled = false;
                        this.textContent = '{{ __("Créer un profil") }}';
                    }
                });
            });
        });
    </script>

    <!-- Custom Styles -->
    <style>
        /* Global page container: full width but centered, with breathing space */
        .page-container {
            width: 100%;
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        /* Two-column layout on desktop, stacked on mobile */
        .event-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.4fr);
            gap: 2rem;
            align-items: flex-start;
        }

        .event-panel,
        .reservations-panel {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 24px 24px 28px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        }

        .event-panel {
            border-left: 4px solid #647a0b;
        }
        .reservations-panel {
            border-left: 4px solid #854f38;
        }

        .event-title {
            font-size: 2rem;
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 16px;
        }

        .event-image img {
            width: 100%;
            max-height: 260px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .event-info {
            margin-top: 12px;
        }

        .info-box {
            margin-bottom: 16px;
            padding: 12px 14px;
            background-color: #f7fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .info-title {
            font-weight: 600;
            color: #647a0b;
            margin-bottom: 6px;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-text {
            font-size: 0.95rem;
            color: #4a5568;
            line-height: 1.5;
        }

        .section-title {
            font-size: 1.4rem;
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 8px;
        }

        .booking-counter {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 14px;
            color: #647a0b;
        }

        /* Table wrapper: handles horizontal overflow on small screens */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            min-width: 700px;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 0.9rem;
        }

        table thead {
            background-color: #647a0b;
            color: #fff;
        }

        table th,
        table td {
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            text-align: left;
            vertical-align: middle;
            white-space: nowrap;
        }

        table tbody tr:nth-child(even) {
            background-color: #f7fafc;
        }

        /* Pills */
        .pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
            white-space: nowrap;
        }
        .pill-success {
            background-color: #c6f6d5;
            color: #22543d;
        }
        .pill-muted {
            background-color: #edf2f7;
            color: #4a5568;
        }
        .pill-link {
            margin-left: 6px;
            font-size: 0.75rem;
            color: #647a0b;
            text-decoration: underline;
        }

        .client-cell {
            max-width: 220px;
        }

        /* Buttons */
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 24px;
        }

        .inline-form {
            display: inline-block;
        }

        .btn-primary,
        .btn-secondary,
        .btn-danger {
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: background-color 0.2s, color 0.2s, border-color 0.2s;
            border: none;
            white-space: nowrap;
        }

        .btn-xs {
            padding: 6px 10px;
            font-size: 0.8rem;
        }

        .btn-primary {
            background-color: #647a0b;
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #536507;
        }

        .btn-secondary {
            background-color: transparent;
            color: #854f38;
            border: 2px solid #854f38;
        }

        .btn-secondary:hover {
            background-color: #854f38;
            color: #fff;
        }

        .btn-danger {
            background-color: #e3342f;
            color: #fff;
        }

        .btn-danger:hover {
            background-color: #cc1f1a;
        }

        /* Alerts */
        .alert {
            padding: 10px 12px;
            border-radius: 6px;
            margin-bottom: 14px;
            font-size: 0.9rem;
            text-align: left;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .event-layout {
                grid-template-columns: minmax(0, 1fr);
            }

            .event-panel,
            .reservations-panel {
                padding: 18px 16px 22px;
            }

            .event-title {
                font-size: 1.6rem;
            }

            table {
                min-width: 600px;
            }
        }

        @media (max-width: 640px) {
            .page-container {
                padding: 0 1rem;
            }

            .event-title {
                font-size: 1.4rem;
            }

            .section-title {
                font-size: 1.2rem;
            }

            table {
                min-width: 520px;
            }

            .action-buttons {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-primary,
            .btn-secondary,
            .btn-danger {
                width: 100%;
            }
        }
    </style>

    <!-- Include FontAwesome and Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
</x-app-layout>
