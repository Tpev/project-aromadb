<?php

namespace App\Services;

use App\Models\Appointment;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Log;

class AppointmentMailDeliveryGuard
{
    public const APPOINTMENT_HEADER = 'X-Olithea-Appointment-Id';

    public const MESSAGE_HEADER = 'X-Olithea-Appointment-Message';

    public function handle(MessageSending $event): ?bool
    {
        $headers = $event->message->getHeaders();
        $appointmentHeader = $headers->get(self::APPOINTMENT_HEADER);
        $messageHeader = $headers->get(self::MESSAGE_HEADER);

        if (!$appointmentHeader || !$messageHeader) {
            return null;
        }

        $appointmentId = (int) $appointmentHeader->getBodyAsString();
        $messageType = trim($messageHeader->getBodyAsString());
        $appointment = Appointment::query()->find($appointmentId);

        $allowed = $appointment && match ($messageType) {
            'confirmation' => !$appointment->isCancelled()
                && !$appointment->isCompleted()
                && !$appointment->isPendingPayment(),
            'reminder' => $this->isReminderEligible($appointment),
            'active-update' => !$appointment->isCancelled()
                && !$appointment->isPendingPayment(),
            default => true,
        };

        if (!$allowed) {
            Log::info('Appointment email stopped before transport.', [
                'appointment_id' => $appointmentId,
                'message_type' => $messageType,
                'status' => $appointment?->status,
            ]);
        }

        return $allowed ? null : false;
    }

    private function isReminderEligible(Appointment $appointment): bool
    {
        if ($appointment->isCancelled() || $appointment->isCompleted()
            || $appointment->isPendingPayment() || !$appointment->appointment_date) {
            return false;
        }

        $minutesUntilAppointment = now()->diffInMinutes($appointment->appointment_date, false);

        return ($minutesUntilAppointment >= 23 * 60 && $minutesUntilAppointment <= 25 * 60)
            || ($minutesUntilAppointment >= 50 && $minutesUntilAppointment <= 70);
    }
}
