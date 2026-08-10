<?php

namespace App\Jobs;

use App\Mail\AppointmentReminderClientMail;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(
        public int $appointmentId,
        public string $reminderType,
        public string $expectedStart,
        public string $claimedAt,
    ) {
        $this->onQueue('default');
    }

    public function backoff(): array
    {
        return [60, 300, 900, 1800];
    }

    public function handle(): void
    {
        $appointment = Appointment::query()
            ->with(['clientProfile', 'user', 'product', 'practiceLocation', 'meeting'])
            ->find($this->appointmentId);

        if (!$appointment || !$this->isStillEligible($appointment)) {
            $this->releaseClaim($appointment);
            return;
        }

        $email = $appointment->clientProfile?->email;
        if (!$email) {
            $this->releaseClaim($appointment);
            return;
        }

        Mail::to($email)->send(new AppointmentReminderClientMail($appointment));

        $sentColumn = $this->reminderType === '24h' ? 'reminder_24h_sent_at' : 'reminder_1h_sent_at';
        $queuedColumn = $this->reminderType === '24h' ? 'reminder_24h_queued_at' : 'reminder_1h_queued_at';

        Appointment::query()
            ->whereKey($appointment->id)
            ->notCancelled()
            ->where('appointment_date', Carbon::parse($this->expectedStart))
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhereNotIn(
                        'status',
                        Appointment::statusValuesFor(Appointment::STATUS_PENDING_PAYMENT)
                    );
            })
            ->update([
                $sentColumn => now(),
                $queuedColumn => null,
            ]);
    }

    private function isStillEligible(Appointment $appointment): bool
    {
        if ($appointment->isCancelled() || $appointment->isCompleted()
            || $appointment->isPendingPayment() || !$appointment->appointment_date) {
            return false;
        }

        if (!$appointment->appointment_date->equalTo(Carbon::parse($this->expectedStart))) {
            return false;
        }

        $minutes = now()->diffInMinutes($appointment->appointment_date, false);

        return $this->reminderType === '24h'
            ? $minutes >= 23 * 60 && $minutes <= 25 * 60
            : $minutes >= 50 && $minutes <= 70;
    }

    private function releaseClaim(?Appointment $appointment): void
    {
        if (!$appointment) {
            return;
        }

        $queuedColumn = $this->reminderType === '24h' ? 'reminder_24h_queued_at' : 'reminder_1h_queued_at';

        Appointment::query()
            ->whereKey($appointment->id)
            ->whereBetween($queuedColumn, [
                Carbon::parse($this->claimedAt)->subSecond(),
                Carbon::parse($this->claimedAt)->addSecond(),
            ])
            ->update([$queuedColumn => null]);

        Log::info('Stale appointment reminder skipped.', [
            'appointment_id' => $appointment->id,
            'reminder_type' => $this->reminderType,
            'status' => $appointment->status,
        ]);
    }
}
