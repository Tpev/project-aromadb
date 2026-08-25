<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Product;
use App\Models\User;
use App\Support\BookingV2Access;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingAppointmentCreationService
{
    public function __construct(
        private readonly BookingV2Access $access,
        private readonly AppointmentAvailabilityService $availability,
        private readonly BookingReservationLockService $reservationLocks,
    ) {
    }

    public function create(
        array $attributes,
        User $practitioner,
        Product $product,
        Carbon $start,
        ?string $mode,
        ?int $locationId,
        bool $skipAvailability = false,
    ): Appointment {
        if ($skipAvailability || ! $this->access->enabledFor($practitioner)) {
            return Appointment::create($attributes);
        }

        return $this->reservationLocks->run(
            (int) $practitioner->id,
            $start,
            $mode,
            $locationId,
            function () use (
                $attributes,
                $practitioner,
                $product,
                $start,
                $mode,
                $locationId,
            ): Appointment {
                return DB::transaction(function () use (
                    $attributes,
                    $practitioner,
                    $product,
                    $start,
                    $mode,
                    $locationId,
                ): Appointment {
                    $this->availability->assertAvailable(
                        $this->availabilityTemplate($practitioner, $product, $mode, $locationId),
                        $start,
                        true,
                    );

                    return Appointment::create($attributes);
                }, 3);
            },
        );
    }

    private function availabilityTemplate(
        User $practitioner,
        Product $product,
        ?string $mode,
        ?int $locationId,
    ): Appointment {
        $appointment = new Appointment([
            'user_id' => $practitioner->id,
            'product_id' => $product->id,
            'duration' => $product->duration,
            'type' => $mode,
            'practice_location_id' => $mode === 'cabinet' ? $locationId : null,
        ]);
        $appointment->setRelation('user', $practitioner);
        $appointment->setRelation('product', $product);

        return $appointment;
    }
}
