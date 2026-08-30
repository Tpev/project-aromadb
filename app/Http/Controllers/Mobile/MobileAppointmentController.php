<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\Product;
use App\Models\User;
use App\Models\Availability;
use App\Models\Unavailability;
use App\Models\SpecialAvailability;
use App\Models\Meeting;
use App\Models\Invoice;
use App\Models\PracticeLocation;
use App\Mail\AppointmentCreatedTherapistMail;
use App\Notifications\AppointmentBooked;
use App\Services\AppointmentQuestionnaireAutomationService;
use App\Services\AppointmentIcsService;
use App\Services\CabinetAccessService;
use App\Services\SharedCabinetSchedulingService;
use Stripe\StripeClient;

class MobileAppointmentController extends Controller
{
    /**
     * 1) From therapist mobile public profile (slug) → show booking form.
     *    Route idea: GET /mobile/therapeute/{slug}/prendre-rdv
     */
    public function createFromTherapistSlug(Request $request, string $slug)
    {
        $therapist = User::where('slug', $slug)
            ->where('is_therapist', true)
            ->where('accept_online_appointments', true)
            ->firstOrFail();

        // Products shown to clients (same logic as createPatient, but ordered)
        $productsQuery = Product::where('user_id', $therapist->id);
        if (app(\App\Support\BookingV2Access::class)->enabledFor($therapist)) {
            $productsQuery->where('can_be_booked_online', true);
        }
        $products = $productsQuery->orderBy('display_order', 'asc')->get();

        // Cabinet locations (for "cabinet" mode)
        $practiceLocations = app(CabinetAccessService::class)->accessibleLocations($therapist);
        $compatibleLocationsByProduct = app(\App\Support\BookingV2Access::class)->enabledFor($therapist)
            ? app(\App\Services\BookingLocationService::class)->compatibleLocationsByProduct($therapist, $products)
            : [];
        $preferredProduct = null;
        if ($request->filled('product_id')) {
            $preferredProduct = $products->first(fn (Product $product): bool =>
                (int) $product->id === (int) $request->query('product_id')
                && (bool) $product->can_be_booked_online
            );
        }

        return view('mobile.appointments.create', [
            'therapist'         => $therapist,
            'products'          => $products,
            'practiceLocations' => $practiceLocations,
            'compatibleLocationsByProduct' => $compatibleLocationsByProduct,
            'preferredProduct' => $preferredProduct,
        ]);
    }

    /**
     * 2) Store a newly created appointment from a patient (MOBILE).
     *    This is basically your storePatient, but:
     *    - kept in Mobile namespace
     *    - redirects to mobile views/routes for confirmation.
     */
    public function store(Request $request)
    {
        // Messages d'erreur personnalisés
        $messages = [
            'therapist_id.required'   => 'Le professionnel est requis.',
            'therapist_id.exists'     => 'Le professionnel sélectionné est invalide.',
            'first_name.required'     => 'Le prénom est requis.',
            'last_name.required'      => 'Le nom est requis.',
            'email.email'             => 'Veuillez fournir une adresse e-mail valide.',
            'phone.max'               => 'Le numéro de téléphone ne doit pas dépasser 20 caractères.',
            'appointment_date.required' => 'La date du rendez-vous est requise.',
            'appointment_time.required' => 'L’heure du rendez-vous est requise.',
            'product_id.exists'       => 'Le produit sélectionné est invalide.',
        ];

        // Validation de base
        $request->validate([
            'therapist_id'     => 'required|exists:users,id',
            'first_name'       => 'required|string|max:255',
            'last_name'        => 'required|string|max:255',
            'email'            => 'nullable|email|max:255',
            'phone'            => 'nullable|string|max:20',
            'address'          => 'nullable|string',
            'birthdate'        => 'nullable|date',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i',
            'product_id'       => 'required|exists:products,id',
            'notes'            => 'nullable|string',
            // 'type' may come from the form; if not we will infer it from product flags
            'type'             => 'nullable|string',
            // practice_location_id is validated conditionally (see below)
            'practice_location_id' => 'nullable|integer',
            'wants_earlier_slot' => 'nullable|boolean',
        ], $messages);

        // Produit & thérapeute
        $product   = Product::findOrFail($request->product_id);
        $therapist = User::findOrFail($request->therapist_id);
        if (
            app(\App\Support\BookingV2Access::class)->enabledFor($therapist)
            && ((int) $product->user_id !== (int) $therapist->id || ! $product->can_be_booked_online)
        ) {
            return back()->withErrors([
                'product_id' => 'Cette prestation n’est plus disponible à la réservation en ligne.',
            ])->withInput();
        }
        $wantsEarlierSlot = config('appointments.earlier_slots.enabled', false)
            && $request->boolean('wants_earlier_slot');

        if ($wantsEarlierSlot) {
            $request->validate([
                'email' => 'required|email|max:255',
            ], [
                'email.required' => 'Votre adresse e-mail est nécessaire pour recevoir une proposition de créneau.',
            ]);
        }

        // Inférer le "type" (mode) à partir du produit si non fourni
        $mode = $request->input('type');
        if (!$mode) {
            if (!empty($product->dans_le_cabinet)) {
                $mode = 'cabinet';
            } elseif (!empty($product->visio) || !empty($product->en_visio)) {
                $mode = 'visio';
            } elseif (!empty($product->adomicile)) {
                $mode = 'domicile';
            } elseif (!empty($product->en_entreprise)) {
                $mode = 'entreprise';
            } else {
                $mode = 'autre';
            }
        }

        // Si le produit nécessite une adresse (domicile), exiger l'adresse
        if ($mode === 'domicile' || $mode === 'entreprise' || !empty($product->adomicile) || !empty($product->en_entreprise)) {
            $request->validate([
                'address' => 'required|string|max:255',
            ], $messages);
        }

        // Si le mode est au cabinet, EXIGER un practice_location_id accessible à ce thérapeute
        $practiceLocationId = $request->input('practice_location_id');
        if ($mode === 'cabinet') {
            $request->validate([
                'practice_location_id' => [
                    'required',
                    'integer',
                    'exists:practice_locations,id',
                ],
            ], [
                'practice_location_id.required' => 'Veuillez sélectionner un cabinet.',
                'practice_location_id.exists'   => 'Le cabinet sélectionné est invalide.',
            ]);

            $location = PracticeLocation::query()->find((int) $practiceLocationId);
            if (!$location || !app(CabinetAccessService::class)->canAccessLocation($therapist, $location)) {
                return back()->withErrors([
                    'practice_location_id' => 'Le cabinet sélectionné est invalide.',
                ])->withInput();
            }
        } else {
            // pour visio/domicile, on ignore le cabinet éventuel envoyé
            $practiceLocationId = null;
        }

        // Combiner la date et l'heure
        $appointmentDateTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $request->appointment_date . ' ' . $request->appointment_time
        );

        // Valider la disponibilité du thérapeute (on passe le practice_location_id pour le cabinet)
        if (!$this->isAvailable(
            $appointmentDateTime,
            $product->duration,
            $therapist->id,
            $product->id,
            null,
            $practiceLocationId,
            $mode
        )) {
            return back()->withErrors([
                'appointment_date' => 'Le créneau horaire est indisponible ou entre en conflit avec un autre rendez-vous.',
            ])->withInput();
        }

        // Créer / retrouver le ClientProfile (lié au thérapeute)
        $clientProfile = ClientProfile::firstOrCreate(
            [
                'email'   => $request->email,
                'user_id' => $therapist->id,
            ],
            [
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'phone'      => $request->phone,
                'address'    => $request->address,
                'birthdate'  => $request->birthdate,
                'notes'      => $request->notes,
            ]
        );

        // Créer le rendez-vous (statut 'pending' si paiement, sinon on confirmera plus bas)
        $appointmentAttributes = [
            'client_profile_id'     => $clientProfile->id,
            'user_id'               => $therapist->id,
            'practice_location_id'  => $practiceLocationId,   // ← ENREGISTRÉ ICI POUR LE CABINET
            'appointment_date'      => $appointmentDateTime,
            'status'                => Appointment::STATUS_PENDING_PAYMENT,
            'notes'                 => $request->notes,
            'type'                  => $mode,                 // ← on stocke le mode
            'duration'              => $product->duration,
            'product_id'            => $product->id,
        ];
        if (config('appointments.earlier_slots.enabled', false)) {
            $appointmentAttributes['wants_earlier_slot'] = $wantsEarlierSlot;
            $appointmentAttributes['earlier_slot_opted_in_at'] = $wantsEarlierSlot ? now() : null;
        }
        $appointment = app(\App\Services\BookingAppointmentCreationService::class)->create(
            $appointmentAttributes,
            $therapist,
            $product,
            $appointmentDateTime,
            $mode,
            $practiceLocationId
        );

        // Si visio : créer une réunion + lien
        if ($mode === 'visio' || !empty($product->visio) || !empty($product->en_visio)) {
            $token = Str::random(32);
            $meeting = Meeting::create([
                'name'              => 'Réunion pour ' . $appointment->clientProfile->first_name . ' ' . $appointment->clientProfile->last_name,
                'start_time'        => $appointmentDateTime,
                'duration'          => $product->duration,
                'participant_email' => $appointment->clientProfile->email,
                'client_profile_id' => $clientProfile->id,
                'room_token'        => $token,
                'appointment_id'    => $appointment->id,
            ]);
            // $connectionLink = route('webrtc.room', ['room' => $token]) . '#1';
        }

        // Charger pour notif
        $appointment->load('clientProfile', 'user', 'product', 'practiceLocation');

        // Notification au thérapeute (try/catch pour robustesse)
        try {
            $therapist->notify(new AppointmentBooked($appointment));
        } catch (\Exception $e) {
            Log::error('Failed to send appointment notification: ' . $e->getMessage());
        }

        /* ---------------------- Paiement Stripe si requis ---------------------- */
        if (!empty($product->collect_payment)) {
            if ($therapist->stripe_account_id) {
                $stripeSecretKey = config('services.stripe.secret');
                $stripe = new StripeClient($stripeSecretKey);

                $totalAmount = $product->price + ($product->price * $product->tax_rate / 100);

                try {
                    $session = $stripe->checkout->sessions->create([
                        'payment_method_types' => ['card'],
                        'line_items' => [[
                            'price_data' => [
                                'currency'     => 'eur',
                                'product_data' => ['name' => $product->name],
                                'unit_amount'  => intval($totalAmount * 100),
                            ],
                            'quantity' => 1,
                        ]],
                        'mode' => 'payment',
                        'expires_at' => now()->addMinutes(30)->timestamp,
                        // On garde les mêmes routes callbacks (AppointmentController)
                        'success_url' => route('appointments.success') . '?session_id={CHECKOUT_SESSION_ID}&account_id=' . $therapist->stripe_account_id,
                        'cancel_url'  => route('appointments.cancel', ['appointment_id' => $appointment->id, 'token' => $appointment->token]),
                        'payment_intent_data' => [
                            'metadata' => [
                                'appointment_id' => $appointment->id,
                                'patient_email'  => $appointment->clientProfile->email,
                            ],
                        ],
                    ], [
                        'stripe_account' => $therapist->stripe_account_id,
                    ]);

                    $appointment->stripe_session_id = $session->id;
                    $appointment->save();

                    return redirect($session->url);

                } catch (\Exception $e) {
                    Log::error('Stripe Checkout creation failed: ' . $e->getMessage());
                    return back()->withErrors([
                        'payment' => 'Erreur lors de la création de la session de paiement. Veuillez réessayer.',
                    ])->withInput();
                }
            } else {
                // Pas de Stripe connecté : on confirme directement
                Log::warning("Thérapeute {$therapist->id} sans compte Stripe. Confirmation sans paiement.");
                $appointment->update(['status' => Appointment::STATUS_CONFIRMED]);

                try {
                    if ($appointment->clientProfile->email) {
                        \App\Jobs\SendAppointmentConfirmationJob::dispatch($appointment->id)->afterCommit();
                    }
                    if ($therapist->email) {
                        Mail::to($therapist->email)->queue(new AppointmentCreatedTherapistMail($appointment));
                    }
                } catch (\Exception $e) {
                    Log::error("Erreur envoi emails : " . $e->getMessage());
                }

                app(AppointmentQuestionnaireAutomationService::class)->dispatchForConfirmedAppointment($appointment);

                // MOBILE confirmation page
                return redirect()
                    ->route('mobile.appointments.show', $appointment->token)
                    ->with('success', 'Votre rendez-vous a été réservé avec succès.');
            }
        }

        /* ---------------------- Pas de paiement requis ---------------------- */
        $appointment->update(['status' => Appointment::STATUS_CONFIRMED]);

        try {
            if ($appointment->clientProfile->email) {
                \App\Jobs\SendAppointmentConfirmationJob::dispatch($appointment->id)->afterCommit();
            }
            if ($therapist->email) {
                Mail::to($therapist->email)->queue(new AppointmentCreatedTherapistMail($appointment));
            }
        } catch (\Exception $e) {
            Log::error("Erreur envoi emails : " . $e->getMessage());
        }

        app(AppointmentQuestionnaireAutomationService::class)->dispatchForConfirmedAppointment($appointment);

        return redirect()
            ->route('mobile.appointments.show', $appointment->token)
            ->with('success', 'Votre rendez-vous a été réservé avec succès.');
    }

    /**
     * 3) Display the specified appointment for a patient (MOBILE) using token.
     *    Route idea: GET /mobile/rdv/{token}
     */
    public function show(string $token)
    {
        $appointment = Appointment::where('token', $token)
            ->with(['clientProfile', 'user', 'product', 'practiceLocation', 'meeting'])
            ->firstOrFail();

        $icsService = app(AppointmentIcsService::class);

        return view('mobile.appointments.show', [
            'appointment' => $appointment,
            'icsUrl' => route('appointments.downloadICS', $appointment->token),
            'googleCalendarUrl' => $icsService->googleCalendarUrl($appointment),
        ]);
    }
    /**
     * 4) Download ICS for the specified appointment (MOBILE).
     *    Route idea: GET /mobile/rdv/{token}/ics
     */
    public function downloadICS(string $token)
    {
        $appointment = Appointment::where('token', $token)
            ->with(['clientProfile', 'user', 'product', 'practiceLocation', 'meeting'])
            ->firstOrFail();

        $icsService = app(AppointmentIcsService::class);
        $icsContent = $icsService->build($appointment);
        $fileName = $icsService->fileName($appointment);

        return response($icsContent)
            ->header('Content-Type', 'text/calendar; charset=UTF-8; method=PUBLISH')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * 5) JSON endpoint for available slots (patient view, MOBILE).
     *    Route idea: GET /mobile/api/slots
     *    Same as getAvailableSlotsForPatient but scoped to Mobile controller.
     */
    public function getAvailableSlotsForPatient(Request $request)
    {
        // 1) Basic validation (core fields)
        $request->validate([
            'therapist_id' => 'required|exists:users,id',
            'date'         => 'required|date_format:Y-m-d',
            'product_id'   => 'required|exists:products,id',
            // 'mode' and 'location_id' handled below after we resolve the mode
        ]);

        $therapistId = (int) $request->therapist_id;
        $therapist   = User::findOrFail($therapistId);
        $product     = Product::findOrFail((int) $request->product_id);
        $duration    = (int) ($product->duration ?? 0);

        if (
            (int) $product->user_id !== $therapistId
            || (app(\App\Support\BookingV2Access::class)->enabledFor($therapist) && ! $product->can_be_booked_online)
        ) {
            return response()->json(['slots' => [], 'message' => 'Cette prestation n’est pas disponible pour ce praticien.'], 422);
        }

        // 2) Resolve mode
        $requestedMode = $request->input('mode');
        $mode = in_array($requestedMode, ['cabinet','visio','domicile','entreprise'], true)
            ? $requestedMode
            : (function() use ($product) {
                $modes = [];
                if ($product->dans_le_cabinet) $modes[] = 'cabinet';
                if ($product->visio)           $modes[] = 'visio';
                if ($product->adomicile)       $modes[] = 'domicile';
                if (!empty($product->en_entreprise)) $modes[] = 'entreprise';
                if (count($modes) === 1) return $modes[0];
                return 'cabinet';
            })();

        // 3) If cabinet mode, validate location_id and ensure it is accessible to the therapist
        $locationId = null;
        if ($mode === 'cabinet') {
            $request->validate([
                'location_id' => ['required','integer','exists:practice_locations,id'],
            ]);
            $locationId = (int) $request->location_id;

            $location = PracticeLocation::query()->find($locationId);
            if (!$location || !app(CabinetAccessService::class)->canAccessLocation($therapist, $location)) {
                return response()->json(['slots' => [], 'message' => 'Invalid location for this therapist.'], 422);
            }
        }

        if (app(\App\Support\BookingV2Access::class)->enabledFor($therapist)) {
            return response()->json([
                'slots' => app(\App\Services\AppointmentAvailabilityService::class)->slotsForDate(
                    $this->bookingAvailabilityTemplate($therapist, $product, $mode, $locationId),
                    Carbon::createFromFormat('Y-m-d', $request->date)
                ),
            ]);
        }

        // 4) Minimum notice + buffer
        $minimumNoticeHours    = (int) ($therapist->minimum_notice_hours ?? 0);
        $bufferMinutes         = (int) ($therapist->buffer_time_between_appointments ?? 0);
        $now                   = Carbon::now();
        $minimumNoticeDateTime = $now->copy()->addHours($minimumNoticeHours);

        // 5) Day + date
        $date       = Carbon::createFromFormat('Y-m-d', $request->date);
        $dayOfWeek0 = $date->dayOfWeekIso - 1; // 0..6
        $dateStr    = $date->format('Y-m-d');

        // 6a) Weekly
        $weeklyQuery = Availability::where('user_id', $therapistId)
            ->where('day_of_week', $dayOfWeek0)
            ->where(function ($query) use ($product) {
                $query->where('applies_to_all', true)
                      ->orWhereHas('products', function ($q) use ($product) {
                          $q->where('products.id', $product->id);
                      });
            });

        if ($mode === 'cabinet' && $locationId) {
            $weeklyQuery->where('practice_location_id', $locationId);
        }

        $weeklyAvailabilities = $weeklyQuery->get();

        // 6b) Specials
        $specialQuery = SpecialAvailability::where('user_id', $therapistId)
            ->whereDate('date', $dateStr)
            ->where(function ($query) use ($product) {
                $query->where('applies_to_all', true)
                      ->orWhereHas('products', function ($q) use ($product) {
                          $q->where('products.id', $product->id);
                      });
            });

        if ($mode === 'cabinet' && $locationId) {
            $specialQuery->where('practice_location_id', $locationId);
        }

        $specialAvailabilities = $specialQuery->get();

        $availabilities = $weeklyAvailabilities->concat($specialAvailabilities);

        if ($availabilities->isEmpty()) {
            return response()->json(['slots' => []]);
        }

        $existingAppointments = $this->buildBlockingAppointmentsQueryForDate(
            $therapistId,
            $request->date,
            $mode,
            $locationId
        )->get();

        // 8) Unavailabilities (support multi-day spans)
        $unavailabilities = Unavailability::where('user_id', $therapistId)
            ->where(function ($query) use ($request) {
                $query->whereDate('start_date', '<=', $request->date)
                      ->whereDate('end_date', '>=', $request->date);
            })
            ->get();

        // 9) Build slots (15-min step)
        $slots = [];

        foreach ($availabilities as $availability) {
            $availabilityStart = Carbon::createFromFormat('H:i:s', $availability->start_time)
                ->setDateFrom($date);
            $availabilityEnd = Carbon::createFromFormat('H:i:s', $availability->end_time)
                ->setDateFrom($date);

            while ($availabilityStart->copy()->addMinutes($duration)->lessThanOrEqualTo($availabilityEnd)) {
                $slotStart = $availabilityStart->copy();
                $slotEnd   = $availabilityStart->copy()->addMinutes($duration);

                // Enforce minimum notice
                if ($slotStart->lt($minimumNoticeDateTime)) {
                    $availabilityStart->addMinutes(15);
                    continue;
                }

                // Check overlap with existing appointments (with buffer)
                $isBooked = $existingAppointments->contains(function ($appointment) use ($slotStart, $slotEnd, $bufferMinutes) {
                    $appointmentStart = Carbon::parse($appointment->appointment_date);
                    $appointmentEnd   = $appointmentStart->copy()->addMinutes($appointment->duration);

                    if ($bufferMinutes > 0) {
                        $appointmentStart = $appointmentStart->copy()->subMinutes($bufferMinutes);
                        $appointmentEnd   = $appointmentEnd->copy()->addMinutes($bufferMinutes);
                    }

                    return $slotStart->lt($appointmentEnd) && $slotEnd->gt($appointmentStart);
                });

                // Check overlap with unavailabilities (no buffer)
                $isUnavailable = $unavailabilities->contains(function ($unavailability) use ($slotStart, $slotEnd) {
                    $unavailabilityStart = Carbon::parse($unavailability->start_date);
                    $unavailabilityEnd   = Carbon::parse($unavailability->end_date);
                    return $slotStart->lt($unavailabilityEnd) && $slotEnd->gt($unavailabilityStart);
                });

                if (!$isBooked && !$isUnavailable) {
                    $slots[] = [
                        'start' => $slotStart->format('H:i'),
                        'end'   => $slotEnd->format('H:i'),
                    ];
                }

                $availabilityStart->addMinutes(15);
            }
        }

        // Optionnel : trier et dédupliquer les slots
        $slots = collect($slots)
            ->unique(fn($s) => $s['start'].'-'.$s['end'])
            ->sortBy('start')
            ->values()
            ->all();

        return response()->json(['slots' => $slots]);
    }

    /**
     * 6) Return concrete dates that have at least one availability (MOBILE).
     *    Essentially your availableConcreteDatesPatient.
     */
    public function availableConcreteDatesPatient(Request $request)
    {
        $request->validate([
            'therapist_id' => 'required|exists:users,id',
            'product_id'   => 'required|exists:products,id',
            'mode'         => 'nullable|string|in:cabinet,visio,domicile,entreprise',
            'location_id'  => 'nullable|integer',
            'days'         => 'nullable|integer|min:1|max:90',
        ]);

        $therapistId = (int) $request->therapist_id;
        $product     = Product::findOrFail((int) $request->product_id);
        $therapist   = User::findOrFail($therapistId);

        if (
            (int) $product->user_id !== $therapistId
            || (app(\App\Support\BookingV2Access::class)->enabledFor($therapist) && ! $product->can_be_booked_online)
        ) {
            return response()->json(['dates' => [], 'message' => 'Cette prestation n’est pas disponible pour ce praticien.'], 422);
        }

        $mode = $this->resolvePatientMode($product, $request->input('mode'));

        $locationId = null;
        if ($mode === 'cabinet') {
            $request->validate([
                'location_id' => ['required','integer','exists:practice_locations,id'],
            ]);

            $locationId = (int) $request->location_id;

            $location = PracticeLocation::query()->find($locationId);

            if (!$location || !app(CabinetAccessService::class)->canAccessLocation($therapist, $location)) {
                return response()->json([
                    'dates' => [],
                    'message' => 'Invalid location for this therapist.',
                ], 422);
            }
        }

        if (app(\App\Support\BookingV2Access::class)->enabledFor($therapist)) {
            $template = $this->bookingAvailabilityTemplate($therapist, $product, $mode, $locationId);
            $result = app(\App\Services\AppointmentAvailabilityService::class)->availableDates(
                $template,
                Carbon::today(),
                (int) $request->input('days', 90),
            );

            return response()->json(['dates' => $result['dates']]);
        }

        $days  = (int) $request->input('days', 90);
        $today = Carbon::today();
        $dates = [];

        for ($i = 0; $i < $days; $i++) {
            $date      = $today->copy()->addDays($i);
            $dayOfWeek = $date->dayOfWeekIso - 1;
            $dateStr   = $date->format('Y-m-d');

            // Weekly
            $weeklyQuery = Availability::where('user_id', $therapistId)
                ->where('day_of_week', $dayOfWeek)
                ->where(function ($q) use ($product) {
                    $q->where('applies_to_all', true)
                      ->orWhereHas('products', function ($qq) use ($product) {
                          $qq->where('products.id', $product->id);
                      });
                });

            if ($mode === 'cabinet' && $locationId) {
                $weeklyQuery->where('practice_location_id', $locationId);
            }

            $hasWeekly = $weeklyQuery->exists();

            // Specials
            $specialQuery = SpecialAvailability::where('user_id', $therapistId)
                ->whereDate('date', $dateStr)
                ->where(function ($q) use ($product) {
                    $q->where('applies_to_all', true)
                      ->orWhereHas('products', function ($qq) use ($product) {
                          $qq->where('products.id', $product->id);
                      });
                });

            if ($mode === 'cabinet' && $locationId) {
                $specialQuery->where('practice_location_id', $locationId);
            }

            $hasSpecial = $specialQuery->exists();

            if ($hasWeekly || $hasSpecial) {
                $dates[] = $dateStr;
            }
        }

        return response()->json(['dates' => $dates]);
    }

    private function buildBlockingAppointmentsQueryForDate(
        int $therapistId,
        string $dateStr,
        ?string $mode = null,
        ?int $locationId = null
    ) {
        $query = Appointment::query()->whereDate('appointment_date', $dateStr);

        if ($mode === 'cabinet' && $locationId) {
            $location = PracticeLocation::query()->find($locationId);

            if ($location && app(SharedCabinetSchedulingService::class)->shouldApplySharedConstraint($mode, $locationId)) {
                $memberIds = app(CabinetAccessService::class)->activeMemberUserIds($location);
                $query->whereIn('user_id', $memberIds)
                    ->where('practice_location_id', $locationId);
            } else {
                $query->where('user_id', $therapistId);
            }
        } else {
            $query->where('user_id', $therapistId);
        }

        return $this->applyBlockingAppointmentsFilter($query);
    }

    /**
     * 8) Core availability check: same logic as in AppointmentController::isAvailable
     */
    private function isAvailable(
        $appointmentDateTime,
        $duration,
        $therapistId,
        $productId,
        $excludeAppointmentId = null,
        $locationId = null,
        $mode = null
    ): bool {
        // Normalise
        $duration = (int) $duration;
        $start    = Carbon::parse($appointmentDateTime);
        $end      = $start->copy()->addMinutes($duration);

        // Buffer time between appointments (in minutes)
        $therapist      = User::findOrFail((int) $therapistId);
        $bufferMinutes  = (int) ($therapist->buffer_time_between_appointments ?? 0);
        $bufferedStart  = $bufferMinutes > 0 ? $start->copy()->subMinutes($bufferMinutes) : $start;
        $bufferedEnd    = $bufferMinutes > 0 ? $end->copy()->addMinutes($bufferMinutes)   : $end;

        // 1) Récup produit & mode
        $product = Product::findOrFail((int) $productId);

        if ((int) $product->user_id !== (int) $therapist->id) {
            return false;
        }

        if (app(\App\Support\BookingV2Access::class)->enabledFor($therapist)) {
            return app(\App\Services\AppointmentAvailabilityService::class)->isAvailable(
                $this->bookingAvailabilityTemplate($therapist, $product, $mode, $locationId, $excludeAppointmentId),
                $start,
                true
            );
        }

        if (!in_array($mode, ['cabinet','visio','domicile','entreprise'], true)) {
            // Déduction simple si le mode n'est pas fourni
            $modes = [];
            if ($product->dans_le_cabinet) $modes[] = 'cabinet';
            if ($product->visio)           $modes[] = 'visio';
            if ($product->adomicile)       $modes[] = 'domicile';
            if (!empty($product->en_entreprise)) $modes[] = 'entreprise';
            $mode = count($modes) === 1 ? $modes[0] : 'cabinet';
        }

        // Cabinet: si multi-lieux, on attend un locationId (mais on reste permissif si vide)
        if ($mode === 'cabinet' && empty($locationId)) {
            // Si tu veux être strict, tu peux faire `return false;` ici
        }

        // 2) Jour de semaine (0..6)
        $dayOfWeek0 = $start->dayOfWeekIso - 1;

        // 3a) Weekly
        $weeklyQuery = Availability::where('user_id', (int) $therapistId)
            ->where('day_of_week', $dayOfWeek0)
            ->where(function ($query) use ($productId) {
                $query->where('applies_to_all', true)
                      ->orWhereHas('products', function ($q) use ($productId) {
                          $q->where('products.id', $productId);
                      });
            });

        if ($mode === 'cabinet' && $locationId) {
            $weeklyQuery->where('practice_location_id', (int) $locationId);
        }

        $weeklyAvailabilities = $weeklyQuery->get();

        // 3b) Specials
        $dateStr = $start->toDateString();

        $specialQuery = SpecialAvailability::where('user_id', (int) $therapistId)
            ->whereDate('date', $dateStr)
            ->where(function ($query) use ($productId) {
                $query->where('applies_to_all', true)
                      ->orWhereHas('products', function ($q) use ($productId) {
                          $q->where('products.id', $productId);
                      });
            });

        if ($mode === 'cabinet' && $locationId) {
            $specialQuery->where('practice_location_id', (int) $locationId);
        }

        $specialAvailabilities = $specialQuery->get();

        // 3c) Merge
        $availabilities = $weeklyAvailabilities->concat($specialAvailabilities);

        if ($availabilities->isEmpty()) {
            return false;
        }

        // 4) Vérifier que le créneau tombe DANS au moins une disponibilité
        $insideAvailability = false;
        foreach ($availabilities as $a) {
            $aStart = Carbon::createFromFormat('H:i:s', $a->start_time)
                ->setDate($start->year, $start->month, $start->day);
            $aEnd = Carbon::createFromFormat('H:i:s', $a->end_time)
                ->setDate($start->year, $start->month, $start->day);

            if ($start->gte($aStart) && $end->lte($aEnd)) {
                $insideAvailability = true;
                break;
            }
        }
        if (!$insideAvailability) {
            return false;
        }

        // 5) Conflits avec d'autres rendez-vous (global par thérapeute, avec buffer)
        $conflictingAppointments = Appointment::where('user_id', (int) $therapistId)
            ->where('appointment_date', '<', $bufferedEnd);

        if ($conflictingAppointments->getConnection()->getDriverName() === 'sqlite') {
            $conflictingAppointments->whereRaw(
                "datetime(appointment_date, '+' || COALESCE(duration, 60) || ' minutes') > ?",
                [$bufferedStart]
            );
        } else {
            $conflictingAppointments->whereRaw(
                'DATE_ADD(appointment_date, INTERVAL COALESCE(duration, 60) MINUTE) > ?',
                [$bufferedStart]
            );
        }

        $this->applyBlockingAppointmentsFilter($conflictingAppointments);

        if ($excludeAppointmentId) {
            $conflictingAppointments->where('id', '!=', $excludeAppointmentId);
        }

        if ($conflictingAppointments->exists()) {
            return false;
        }

        if (
            $mode === 'cabinet'
            && $locationId
            && app(SharedCabinetSchedulingService::class)->hasSharedCabinetConflict(
                $start,
                $duration,
                (int) $locationId,
                $excludeAppointmentId ? (int) $excludeAppointmentId : null
            )
        ) {
            return false;
        }

        // 6) Conflits avec indisponibilités (multi-day) – pas de buffer ici
        $hasUnavailability = Unavailability::where('user_id', (int) $therapistId)
            ->where(function ($q) use ($start, $end) {
                $q->where('start_date', '<', $end)
                  ->where('end_date',   '>', $start);
            })
            ->exists();

        if ($hasUnavailability) {
            return false;
        }

        return true;
    }

    /**
     * Resolve booking mode for patient side based on product flags + requested mode.
     */
    private function resolvePatientMode(Product $product, ?string $requested): string
    {
        if (in_array($requested, ['cabinet', 'visio', 'domicile', 'entreprise'], true)) {
            return $requested;
        }

        $modes = [];
        if ($product->dans_le_cabinet) {
            $modes[] = 'cabinet';
        }
        if ($product->visio || $product->en_visio) {
            $modes[] = 'visio';
        }
        if ($product->adomicile) {
            $modes[] = 'domicile';
        }
        if ($product->en_entreprise) {
            $modes[] = 'entreprise';
        }

        if (count($modes) === 1) {
            return $modes[0];
        }

        // Ambiguous product: default to cabinet
        return 'cabinet';
    }

    private function applyBlockingAppointmentsFilter($query)
    {
        $query->where(function ($statusQuery) {
            $statusQuery->whereNull('status')
                ->orWhereNotIn('status', Appointment::CANCELLED_STATUSES);
        });

        $driver = app('db')->connection()->getDriverName();
        $portableCondition = $driver === 'sqlite'
            ? "NOT (external = 1 AND time(appointment_date) = '00:00:00' AND COALESCE(duration,0) >= 2880 AND (COALESCE(duration,0) % 1440) = 0)"
            : 'NOT (external = 1 AND TIME(appointment_date) = "00:00:00" AND COALESCE(duration,0) >= 2880 AND MOD(COALESCE(duration,0),1440) = 0)';

        return $query->whereRaw(
            $portableCondition
        );
    }

    private function bookingAvailabilityTemplate(
        User $therapist,
        Product $product,
        ?string $mode,
        ?int $locationId,
        ?int $excludeAppointmentId = null
    ): Appointment {
        $appointment = new Appointment([
            'user_id' => $therapist->id,
            'product_id' => $product->id,
            'duration' => $product->duration,
            'type' => $mode,
            'practice_location_id' => $mode === 'cabinet' ? $locationId : null,
        ]);
        $appointment->id = $excludeAppointmentId ?? 0;
        $appointment->setRelation('user', $therapist);
        $appointment->setRelation('product', $product);

        return $appointment;
    }

}
