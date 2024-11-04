{{-- resources/views/reservations/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            <i class="fas fa-calendar-plus mr-2"></i>{{ __('Réserver une Place pour :') }} {{ $event->name }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <!-- Event Details Recap -->
        <div class="event-recap mx-auto p-6 mb-6">
            <h1 class="event-title text-3xl font-bold text-center mb-4">{{ $event->name }}</h1>

            <!-- Event Image -->
            @if($event->image)
                <div class="event-image mb-4">
                    <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->name }}" class="w-full h-64 object-cover rounded-lg shadow-lg">
                </div>
            @endif

            <!-- Event Information -->
            <div class="event-info grid grid-cols-1 md:grid-cols-2 gap-6">
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
                <div class0="info-box">
                    <h3 class="info-title"><i class="fas fa-map-marker-alt mr-2"></i>{{ __('Lieu') }}</h3>
                    <p class="info-text">{{ $event->location }}</p>
                </div>

                <!-- Price -->
                @if($event->associatedProduct && $event->associatedProduct->price > 0)
                    <div class="info-box">
                        <h3 class="info-title"><i class="fas fa-tag mr-2"></i>{{ __('Prix') }}</h3>
                        <p class="info-text">{{ number_format($event->associatedProduct->price_incl_tax, 2, ',', ' ') }} €</p>
                    </div>
                @endif

                <!-- Booking Required -->
                <div class="info-box">
                    <h3 class="info-title"><i class="fas fa-ticket-alt mr-2"></i>{{ __('Réservation Requise') }}</h3>
                    <p class="info-text">{{ $event->booking_required ? __('Oui') : __('Non') }}</p>
                </div>

                <!-- Limited Spots -->
                @if($event->limited_spot)
                    <div class="info-box">
                        <h3 class="info-title"><i class="fas fa-users mr-2"></i>{{ __('Nombre de Places Disponibles') }}</h3>
                        @php
                            $spotsLeft = $event->number_of_spot - $event->reservations->count();
                        @endphp
                        <p class="info-text">{{ $spotsLeft }} </p>
                    </div>
                @endif
            </div>

            <!-- Event Description -->
            @if($event->description)
                <div class="event-description mt-6">
                    <h3 class="info-title"><i class="fas fa-info-circle mr-2"></i>{{ __('Description') }}</h3>
                    <p class="info-text">{{ $event->description }}</p>
                </div>
            @endif
        </div>

        <!-- Reservation Form -->
        <div class="reservation-form mx-auto p-6">
            <h1 class="form-title text-2xl font-bold text-center mb-4">
                <i class="fas fa-ticket-alt mr-2"></i>{{ __('Réserver une Place') }}
            </h1>

            @if(session('success'))
                <div class="alert alert-success animate__animated animate__fadeInDown">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger animate__animated animate__shakeX">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                </div>
            @endif

            <form action="{{ route('events.reserve.store', $event->id) }}" method="POST">
                @csrf

                <!-- Full Name -->
                <div class="form-group">
                    <label for="full_name" class="form-label">
                        <i class="fas fa-user mr-2"></i>{{ __('Nom Complet') }}
                    </label>
                    <input type="text" id="full_name" name="full_name" class="form-control" value="{{ old('full_name') }}" required>
                    @error('full_name')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope mr-2"></i>{{ __('Email') }}
                    </label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    @error('email')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div class="form-group">
                    <label for="phone" class="form-label">
                        <i class="fas fa-phone mr-2"></i>{{ __('Téléphone (Optionnel)') }}
                    </label>
                    <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone') }}">
                    @error('phone')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-primary mt-4">
                    <i class="fas fa-paper-plane mr-2"></i>{{ __('Réserver') }}
                </button>
                <a href="{{ route('therapist.show', $event->user->slug) }}" class="btn-secondary mt-4">
                    <i class="fas fa-arrow-left mr-2"></i>{{ __('Retour au profil du thérapeute') }}
                </a>
            </form>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        /* General Container */
        .container {
            max-width: 800px;
            animation: fadeIn 1s ease-in;
            padding: 0 15px;
        }

        /* Event Recap */
        .event-recap {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            animation: slideInDown 0.5s ease-in-out;
        }

        .event-title {
            color: #647a0b;
        }

        .info-box {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            text-align: left;
        }

        .info-title {
            font-weight: bold;
            color: #854f38;
            margin-bottom: 8px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
        }

        .info-text {
            color: #4a5568;
            font-size: 1rem;
        }

        /* Event Description */
        .event-description {
            margin-top: 20px;
        }

        /* Reservation Form */
        .reservation-form {
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
            animation: slideInUp 0.5s ease-in-out;
        }

        .form-title {
            color: #647a0b;
        }

        /* Form Group */
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        /* Form Label */
        .form-label {
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 10px;
            display: block;
            font-size: 1.1rem;
        }

        /* Form Control */
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: #854f38;
            outline: none;
            box-shadow: 0 0 5px rgba(133, 79, 56, 0.5);
        }

        /* Buttons */
        .btn-primary, .btn-secondary {
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s, color 0.3s;
            margin-right: 10px;
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

        /* Alerts */
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 1rem;
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

        /* Error Text */
        .text-red-500 {
            color: #e3342f;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideInUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes slideInDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .event-info {
                grid-template-columns: 1fr;
            }

            .form-title {
                font-size: 1.8rem;
            }

            .btn-primary, .btn-secondary {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>

    <!-- FontAwesome and Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
</x-app-layout>
