<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Product;
use App\Models\User;
use App\Support\BookingV2Access;

class BookingSchedulingPolicy
{
    public const MODE_LEGACY = 'legacy';
    public const MODE_FIXED = 'fixed';
    public const MODE_OPTIMIZED = 'optimized';
    public const FIXED_INTERVALS = [15, 30, 45, 60];

    public function __construct(private readonly BookingV2Access $access)
    {
    }

    public function usesV2(User $practitioner): bool
    {
        return $this->access->enabledFor($practitioner)
            && in_array($practitioner->booking_schedule_mode, [self::MODE_FIXED, self::MODE_OPTIMIZED], true);
    }

    public function appliesTo(Appointment $appointment, User $practitioner): bool
    {
        return $this->access->enabledFor($practitioner)
            || $appointment->preparation_time_minutes !== null
            || $appointment->buffer_time_after_minutes !== null;
    }

    public function resolvedMode(Appointment $appointment, User $practitioner): string
    {
        if (
            $this->appliesTo($appointment, $practitioner)
            && in_array($appointment->type, ['cabinet', 'visio', 'domicile', 'entreprise'], true)
        ) {
            return $appointment->type;
        }

        return $appointment->getResolvedMode();
    }

    public function productSupportsMode(Product $product, string $mode): bool
    {
        return match ($mode) {
            'cabinet' => (bool) $product->dans_le_cabinet,
            'visio' => (bool) ($product->visio || $product->en_visio),
            'domicile' => (bool) $product->adomicile,
            'entreprise' => (bool) $product->en_entreprise,
            default => false,
        };
    }

    public function mode(User $practitioner): string
    {
        return $this->usesV2($practitioner)
            ? $practitioner->booking_schedule_mode
            : self::MODE_LEGACY;
    }

    public function fixedInterval(User $practitioner): int
    {
        $interval = (int) $practitioner->booking_slot_interval_minutes;

        return in_array($interval, self::FIXED_INTERVALS, true) ? $interval : 15;
    }

    /** @return array{preparation:int,buffer_after:int,confirmation_note:?string,reminder_note:?string} */
    public function valuesForNewAppointment(User $practitioner, ?Product $product): array
    {
        $globalBuffer = max(0, (int) ($practitioner->buffer_time_between_appointments ?? 0));

        return [
            'preparation' => max(0, (int) ($product?->preparation_time_minutes ?? 0)),
            'buffer_after' => $product?->buffer_time_after_minutes === null
                ? $globalBuffer
                : max(0, (int) $product->buffer_time_after_minutes),
            'confirmation_note' => $this->cleanNote($product?->confirmation_email_note),
            'reminder_note' => $this->cleanNote($product?->reminder_email_note),
        ];
    }

    /** @return array{preparation:int,buffer_after:int} */
    public function valuesForAppointment(Appointment $appointment, User $practitioner): array
    {
        $legacyBuffer = max(0, (int) ($practitioner->buffer_time_between_appointments ?? 0));

        return [
            'preparation' => $appointment->preparation_time_minutes === null
                ? $legacyBuffer
                : max(0, (int) $appointment->preparation_time_minutes),
            'buffer_after' => $appointment->buffer_time_after_minutes === null
                ? $legacyBuffer
                : max(0, (int) $appointment->buffer_time_after_minutes),
        ];
    }

    public function applySnapshots(Appointment $appointment, User $practitioner, ?Product $product): void
    {
        if (! $this->access->enabledFor($practitioner)) {
            return;
        }

        $values = $this->valuesForNewAppointment($practitioner, $product);
        $appointment->preparation_time_minutes = $values['preparation'];
        $appointment->buffer_time_after_minutes = $values['buffer_after'];
        $appointment->confirmation_email_note = $values['confirmation_note'];
        $appointment->reminder_email_note = $values['reminder_note'];
    }

    /** @return array{preparation:int,buffer_after:int} */
    public function valuesForCandidate(Appointment $appointment, User $practitioner, ?Product $product): array
    {
        if ($appointment->preparation_time_minutes !== null || $appointment->buffer_time_after_minutes !== null) {
            return $this->valuesForAppointment($appointment, $practitioner);
        }

        if ($this->access->enabledFor($practitioner)) {
            return array_intersect_key(
                $this->valuesForNewAppointment($practitioner, $product),
                ['preparation' => true, 'buffer_after' => true]
            );
        }

        return $this->valuesForAppointment($appointment, $practitioner);
    }

    private function cleanNote(?string $note): ?string
    {
        $note = trim((string) $note);

        return $note === '' ? null : $note;
    }
}
