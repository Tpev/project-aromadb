<?php

namespace App\Jobs;

use App\Mail\AppointmentCreatedPatientMail;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class SendAppointmentConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public int $appointmentId)
    {
    }

    public function backoff(): array
    {
        return [60, 300, 900, 1800];
    }

    public function handle(): void
    {
        Cache::lock("appointment-confirmation:{$this->appointmentId}", 120)->block(10, function () {
            $appointment = Appointment::query()
                ->with(['clientProfile', 'user', 'product', 'practiceLocation', 'meeting'])
                ->find($this->appointmentId);

            if (!$appointment || $appointment->client_confirmation_sent_at || $appointment->isCancelled()
                || $appointment->isCompleted() || $appointment->isPendingPayment()) {
                Log::info('Stale or duplicate appointment confirmation skipped.', [
                    'appointment_id' => $this->appointmentId,
                    'status' => $appointment?->status,
                ]);
                return;
            }

            if ($appointment->clientProfile?->email) {
                Mail::to($appointment->clientProfile->email)->send(new AppointmentCreatedPatientMail($appointment));
                Appointment::query()
                    ->whereKey($appointment->id)
                    ->notCancelled()
                    ->whereNull('client_confirmation_sent_at')
                    ->where(function ($query) {
                        $query->whereNull('status')
                            ->orWhereNotIn(
                                'status',
                                Appointment::statusValuesFor(Appointment::STATUS_PENDING_PAYMENT)
                            );
                    })
                    ->update(['client_confirmation_sent_at' => now()]);
            }
        });
    }
}
