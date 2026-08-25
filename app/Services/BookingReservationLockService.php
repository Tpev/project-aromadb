<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class BookingReservationLockService
{
    public function run(
        int $practitionerId,
        CarbonInterface $start,
        ?string $mode,
        ?int $locationId,
        Closure $callback,
    ): mixed {
        $keys = [
            'booking-v2-final:practitioner:'.$practitionerId.':'.$start->format('Ymd'),
        ];

        if ($mode === 'cabinet' && $locationId) {
            $keys[] = 'booking-v2-final:location:'.$locationId.':'.$start->format('Ymd');
        }

        sort($keys, SORT_STRING);

        try {
            return $this->withLocks($keys, $callback);
        } catch (LockTimeoutException) {
            throw ValidationException::withMessages([
                'appointment_time' => 'Ce créneau est en cours de réservation. Veuillez réessayer dans quelques secondes.',
            ]);
        }
    }

    private function withLocks(array $keys, Closure $callback, int $offset = 0): mixed
    {
        if (! isset($keys[$offset])) {
            return $callback();
        }

        return Cache::lock($keys[$offset], 30)->block(
            5,
            fn () => $this->withLocks($keys, $callback, $offset + 1),
        );
    }
}
