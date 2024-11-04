{{-- resources/views/events/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Détails de l\'Événement') }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="event-details mx-auto p-4">
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
                <!-- Description -->
                @if($event->description)
                    <div class="info-box">
                        <h3 class="info-title"><i class="fas fa-info-circle mr-2"></i>{{ __('Description') }}</h3>
                        <p class="info-text">{{ $event->description }}</p>
                    </div>
                @endif

                <!-- Date and Time -->
                <div class="info-box">
                    <h3 class="info-title"><i class="fas fa-calendar-alt mr-2"></i>{{ __('Date et Heure') }}</h3>
                    <p class="info-text">{{ \Carbon\Carbon::parse($event->start_date_time)->format('d/m/Y à H:i') }}</p>
                </div>

                <!-- Duration -->
                <div class="info-box">
                    <h3 class="info-title"><i class="fas fa-hourglass-half mr-2"></i>{{ __('Durée') }}</h3>
                    <p class="info-text">{{ $event->duration }} {{ __('minutes') }}</p>
                </div>

                <!-- Location -->
                <div class="info-box">
                    <h3 class="info-title"><i class="fas fa-map-marker-alt mr-2"></i>{{ __('Lieu') }}</h3>
                    <p class="info-text">{{ $event->location }}</p>
                </div>

                <!-- Booking Required -->
                <div class="info-box">
                    <h3 class="info-title"><i class="fas fa-ticket-alt mr-2"></i>{{ __('Réservation Requise') }}</h3>
                    <p class="info-text">{{ $event->booking_required ? __('Oui') : __('Non') }}</p>
                </div>

                <!-- Limited Spots and Number of Spots -->
                <div class="info-box">
                    <h3 class="info-title"><i class="fas fa-users mr-2"></i>{{ __('Places Limitées') }}</h3>
                    <p class="info-text">{{ $event->limited_spot ? __('Oui') : __('Non') }}</p>
                    @if($event->limited_spot)
                        <p class="info-text">{{ __('Nombre de Places :') }} {{ $event->number_of_spot }}</p>
                    @endif
                </div>

                <!-- Associated Product -->
                @if($event->associatedProduct)
                    <div class="info-box">
                        <h3 class="info-title"><i class="fas fa-box mr-2"></i>{{ __('Produit Associé') }}</h3>
                        <p class="info-text"><strong>{{ $event->associatedProduct->name }}</strong></p>
                        <p class="info-text">{{ __('Prix TTC :') }} {{ number_format($event->associatedProduct->price_incl_tax, 2, ',', ' ') }} €</p>
                        @if($event->associatedProduct->description)
                            <p class="info-text">{{ __('Description du Produit :') }} {{ $event->associatedProduct->description }}</p>
                        @endif
                    </div>
                @endif

                <!-- Show on Portal -->
                <div class="info-box">
                    <h3 class="info-title"><i class="fas fa-globe mr-2"></i>{{ __('Affiché sur le Portail') }}</h3>
                    <p class="info-text">{{ $event->showOnPortail ? __('Oui') : __('Non') }}</p>
                </div>
            </div>

            <!-- Reservations Section -->
            @if(Auth::id() === $event->user_id)
                <div class="reservations mt-5">
                    <h2 class="section-title">{{ __('Liste des Réservations') }}</h2>

                    {{-- Booking Counter --}}
                    @php
                        $totalReservations = $event->reservations->count();
                        $availableSpots = $event->limited_spot ? $event->number_of_spot : '∞';
                    @endphp
                    <p class="booking-counter">
                        {{ __('Total Réservations :') }} {{ $totalReservations }} / {{ $availableSpots }}
                    </p>

                    @if($event->reservations->count() > 0)
                        <table class="table table-bordered mt-3">
                            <thead>
                                <tr>
                                    <th>{{ __('N°') }}</th>
                                    <th>{{ __('Nom Complet') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Téléphone') }}</th>
                                    <th>{{ __('Date de Réservation') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($event->reservations as $index => $reservation)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $reservation->full_name }}</td>
                                        <td>{{ $reservation->email }}</td>
                                        <td>{{ $reservation->phone ?? __('N/A') }}</td>
                                        <td>{{ $reservation->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <form action="{{ route('reservations.destroy', $reservation->id) }}" method="POST" onsubmit="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer cette réservation ?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-danger">
                                                    {{ __('Supprimer') }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>{{ __('Aucune réservation pour le moment.') }}</p>
                    @endif
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="action-buttons mt-6">
                <a href="{{ route('events.edit', $event->id) }}" class="btn-primary">
                    <i class="fas fa-edit mr-2"></i>{{ __('Modifier') }}
                </a>
                <form action="{{ route('events.destroy', $event->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer cet événement ?') }}');">
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
    </div>

    <!-- Custom Styles -->
    <style>
        .container {
            max-width: 800px;
            padding: 0 15px;
        }

        .event-details {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
            text-align: center;
        }

        .event-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 20px;
        }

        .event-image img {
            max-width: 100%;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .event-info {
            text-align: left;
            margin-top: 20px;
        }

        .info-box {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f7fafc;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .info-title {
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 10px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
        }

        .info-text {
            font-size: 1rem;
            color: #4a5568;
            line-height: 1.6;
        }

        .reservations {
            margin-top: 40px;
            text-align: left;
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Booking Counter */
        .booking-counter {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: #647a0b;
            text-align: center;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table thead {
            background-color: #647a0b;
            color: #fff;
        }

        table th, table td {
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            text-align: left;
        }

        table tbody tr:nth-child(even) {
            background-color: #f7fafc;
        }

        /* Action Buttons */
        .action-buttons {
            text-align: center;
            margin-top: 30px;
        }

        .btn-primary, .btn-secondary, .btn-danger {
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s, color 0.3s;
            margin: 5px;
        }

        .btn-primary {
            background-color: #647a0b;
            color: #fff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #854f38;
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
            border: none;
        }

        .btn-danger:hover {
            background-color: #cc1f1a;
        }

        /* Alerts */
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 1rem;
            text-align: left;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .event-title {
                font-size: 2rem;
            }

            .event-details {
                padding: 20px;
            }

            .info-title {
                font-size: 1.1rem;
            }

            .info-text {
                font-size: 0.95rem;
            }

            .btn-primary, .btn-secondary, .btn-danger {
                padding: 10px 20px;
                font-size: 0.9rem;
            }

            table th, table td {
                padding: 10px;
                font-size: 0.9rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-buttons a, .action-buttons button {
                margin: 10px 0;
            }
        }
    </style>

    <!-- Include FontAwesome and Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
</x-app-layout>
