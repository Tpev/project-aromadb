<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\AppointmentLifecycleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpirePendingAppointmentPayments extends Command
{
    protected $signature = 'appointments:expire-pending-payments {--limit=500}';

    protected $description = 'Cancel expired appointment payment holds and release their reserved slots.';

    public function handle(AppointmentLifecycleService $lifecycle): int
    {
        $minutes = max(30, (int) config('appointments.pending_payment_expiry_minutes', 35));
        $limit = max(1, min(5000, (int) $this->option('limit')));
        $expired = 0;

        Appointment::query()
            ->whereIn('status', Appointment::statusValuesFor(Appointment::STATUS_PENDING_PAYMENT))
            ->whereNotNull('stripe_session_id')
            ->where('created_at', '<=', now()->subMinutes($minutes))
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->each(function (Appointment $appointment) use ($lifecycle, &$expired) {
                try {
                    if ($lifecycle->expirePendingPayment($appointment)['changed']) {
                        $expired++;
                    }
                } catch (\Throwable $exception) {
                    Log::error('Unable to expire a pending appointment payment.', [
                        'appointment_id' => $appointment->id,
                        'error' => $exception->getMessage(),
                    ]);
                }
            });

        $this->info("Expired {$expired} pending appointment payment(s).");

        return self::SUCCESS;
    }
}
