<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\AppointmentEarlierSlotService;
use App\Services\AppointmentAvailabilityService;
use App\Services\AppointmentLifecycleService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Stripe\StripeClient;

class AppointmentManagementController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function rescheduleForm(
        Request $request,
        string $token,
        AppointmentAvailabilityService $availability
    ) {
        $appointment = $this->appointmentForToken($token);

        if (!$appointment->canBeManagedOnline()) {
            return redirect()->route('appointments.showPatient', $token)
                ->with('error', $this->managementUnavailableMessage($appointment));
        }

        $data = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $selectedDate = $data['date'] ?? $appointment->appointment_date->toDateString();
        $date = Carbon::createFromFormat('Y-m-d', $selectedDate)->startOfDay();

        return view('appointments.reschedule_patient', [
            'appointment' => $appointment,
            'selectedDate' => $selectedDate,
            'slots' => $availability->slotsForDate($appointment, $date),
        ]);
    }

    public function availableSlots(
        Request $request,
        string $token,
        AppointmentAvailabilityService $availability
    ) {
        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $appointment = $this->appointmentForToken($token);
        if (!$appointment->canBeManagedOnline()) {
            return response()->json(['slots' => [], 'message' => $this->managementUnavailableMessage($appointment)], 422);
        }

        return response()->json([
            'slots' => $availability->slotsForDate(
                $appointment,
                Carbon::createFromFormat('Y-m-d', $data['date'])->startOfDay()
            ),
        ]);
    }

    public function reschedule(
        Request $request,
        string $token,
        AppointmentLifecycleService $lifecycle
    ) {
        $data = $request->validate([
            'appointment_date' => ['required', 'date_format:Y-m-d'],
            'appointment_time' => ['required', 'date_format:H:i'],
        ]);

        $appointment = $this->appointmentForToken($token);
        $newStart = Carbon::createFromFormat(
            'Y-m-d H:i',
            $data['appointment_date'].' '.$data['appointment_time']
        );

        try {
            $result = $lifecycle->reschedule($appointment, $newStart, 'token');
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        $message = $result['changed']
            ? 'Votre rendez-vous a bien été déplacé.'
            : 'Ce créneau est déjà celui de votre rendez-vous.';

        return redirect()->route('appointments.showPatient', $token)->with('success', $message);
    }

    public function cancelByToken(
        Request $request,
        string $token,
        AppointmentLifecycleService $lifecycle
    ) {
        $data = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $appointment = $this->appointmentForToken($token);

        try {
            $result = $lifecycle->cancel(
                $appointment,
                'token',
                null,
                $data['cancellation_reason'] ?? null
            );
        } catch (ValidationException $exception) {
            return redirect()->route('appointments.showPatient', $token)
                ->with('error', collect($exception->errors())->flatten()->first());
        }

        $message = $result['changed']
            ? 'Votre rendez-vous a bien été annulé.'
            : 'Ce rendez-vous était déjà annulé.';

        return redirect()->route('appointments.showPatient', $token)->with('success', $message);
    }

    public function updateEarlierSlotPreference(
        Request $request,
        string $token,
        AppointmentEarlierSlotService $earlierSlots
    ): RedirectResponse {
        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);
        $appointment = $this->appointmentForToken($token);
        $enabled = (bool) $data['enabled'];

        if (! $earlierSlots->updatePreference($appointment, $enabled)) {
            return redirect()->route('appointments.showPatient', $token)
                ->with('error', 'Cette préférence ne peut pas être modifiée pour ce rendez-vous.');
        }

        return redirect()->route('appointments.showPatient', $token)->with(
            'success',
            $enabled
                ? 'Vous serez prévenu si un créneau compatible se libère plus tôt.'
                : 'Vous ne recevrez plus de proposition de créneau plus tôt.'
        );
    }

    public function resumePayment(
        string $token,
        AppointmentLifecycleService $lifecycle
    ) {
        $appointment = $this->appointmentForToken($token);

        if (!$appointment->isPendingPayment()) {
            return redirect()->route('appointments.showPatient', $token)
                ->with('error', 'Ce rendez-vous n’est plus en attente de paiement.');
        }

        if (!$appointment->stripe_session_id || !$appointment->user?->stripe_account_id) {
            return redirect()->route('appointments.showPatient', $token)
                ->with('error', 'Le paiement ne peut pas être repris. Contactez votre praticien.');
        }

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));
            $session = $stripe->checkout->sessions->retrieve(
                $appointment->stripe_session_id,
                [],
                ['stripe_account' => $appointment->user->stripe_account_id]
            );

            if (($session->status ?? null) === 'open' && !empty($session->url)) {
                return redirect()->away($session->url);
            }

            if (($session->payment_status ?? null) === 'paid') {
                return redirect()->route('appointments.showPatient', $token)
                    ->with('success', 'Votre paiement a été reçu et sa confirmation est en cours.');
            }

            $lifecycle->expirePendingPayment($appointment);

            return redirect()->route('appointments.showPatient', $token)
                ->with('error', 'La session de paiement a expiré. Le créneau a été libéré ; vous pouvez effectuer une nouvelle réservation.');
        } catch (\Throwable $exception) {
            Log::warning('Unable to resume appointment Stripe Checkout.', [
                'appointment_id' => $appointment->id,
                'stripe_session_id' => $appointment->stripe_session_id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->route('appointments.showPatient', $token)
                ->with('error', 'Le paiement ne peut pas être repris pour le moment. Réessayez dans quelques instants.');
        }
    }

    public function resumeLegacyPayment(string $sessionId)
    {
        $appointment = Appointment::query()
            ->where('stripe_session_id', $sessionId)
            ->firstOrFail();

        return redirect()->route('appointment.confirmation.payment.resume', $appointment->token);
    }

    public function cancelAsPractitioner(
        Request $request,
        Appointment $appointment,
        AppointmentLifecycleService $lifecycle
    ) {
        $this->authorize('update', $appointment);
        $isPast = $appointment->appointment_date?->isPast() ?? false;

        $data = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $result = $lifecycle->cancel(
                $appointment,
                'practitioner',
                (int) Auth::id(),
                $data['cancellation_reason'] ?? null,
                false,
                true
            );
        } catch (ValidationException $exception) {
            return back()->with('error', collect($exception->errors())->flatten()->first());
        }

        $route = $request->routeIs('mobile.*') ? 'mobile.appointments.show' : 'appointments.show';

        $message = $result['changed']
            ? ($isPast
                ? 'Le rendez-vous passé a été marqué comme annulé. Aucun email n’a été envoyé au client.'
                : 'Le rendez-vous a été annulé.')
            : 'Le rendez-vous était déjà annulé.';

        return redirect()->route($route, $appointment)->with('success', $message);
    }

    public function portalShow(Appointment $appointment)
    {
        $this->authorizePortalAppointment($appointment);

        return redirect()->route('appointments.showPatient', $appointment->token);
    }

    public function portalReschedule(Appointment $appointment)
    {
        $this->authorizePortalAppointment($appointment);

        return redirect()->route('appointment.confirmation.reschedule.form', $appointment->token);
    }

    public function portalCancel(
        Request $request,
        Appointment $appointment,
        AppointmentLifecycleService $lifecycle
    ) {
        $client = $this->authorizePortalAppointment($appointment);
        $data = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $lifecycle->cancel(
                $appointment,
                'client',
                (int) $client->id,
                $data['cancellation_reason'] ?? null
            );
        } catch (ValidationException $exception) {
            return back()->with('error', collect($exception->errors())->flatten()->first());
        }

        return back()->with('success', 'Votre rendez-vous a bien été annulé.');
    }

    private function appointmentForToken(string $token): Appointment
    {
        abort_unless(strlen($token) === 64, 404);

        return Appointment::query()
            ->where('token', $token)
            ->with(['clientProfile', 'user', 'product', 'practiceLocation', 'billingInvoices'])
            ->firstOrFail();
    }

    private function authorizePortalAppointment(Appointment $appointment)
    {
        $client = Auth::guard('client')->user();
        abort_unless($client && (int) $appointment->client_profile_id === (int) $client->id, 404);

        return $client;
    }

    private function managementUnavailableMessage(Appointment $appointment): string
    {
        if ($appointment->isCancelled()) {
            return 'Ce rendez-vous est déjà annulé.';
        }

        if ($appointment->isCompleted() || $appointment->appointment_date?->isPast()) {
            return 'Ce rendez-vous est passé ou terminé.';
        }

        $hours = max(0, (int) ($appointment->user?->cancellation_notice_hours ?? 0));

        return "La modification en ligne n’est plus disponible à moins de {$hours} h du rendez-vous. Contactez directement votre praticien.";
    }
}
