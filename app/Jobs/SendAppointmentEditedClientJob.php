<?php

namespace App\Jobs;

use App\Mail\AppointmentEditedClientMail;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAppointmentEditedClientJob implements ShouldQueue
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
        $appointment = Appointment::query()
            ->with(['clientProfile', 'user', 'product'])
            ->find($this->appointmentId);

        if (!$appointment || $appointment->isCancelled() || $appointment->isPendingPayment()) {
            return;
        }

        if ($appointment->clientProfile?->email) {
            Mail::to($appointment->clientProfile->email)->send(new AppointmentEditedClientMail($appointment));
        }
    }
}
