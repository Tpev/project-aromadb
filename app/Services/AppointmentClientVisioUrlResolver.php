<?php

namespace App\Services;

use App\Models\Appointment;

class AppointmentClientVisioUrlResolver
{
    public function resolve(Appointment $appointment): ?string
    {
        $appointment->loadMissing(['product', 'meeting']);

        $resolvedMode = method_exists($appointment, 'getResolvedMode')
            ? $appointment->getResolvedMode()
            : ($appointment->type ?? null);

        $isVisio = in_array((string) $resolvedMode, ['visio', 'video', 'teleconsultation'], true)
            || in_array((string) ($appointment->type ?? ''), ['visio', 'video', 'teleconsultation'], true)
            || (bool) ($appointment->product?->visio ?? false);

        if (! $isVisio) {
            return null;
        }

        if (filled($appointment->meeting?->room_token)) {
            $room = (string) $appointment->meeting->room_token;

            try {
                $jwt = app(JitsiJwtService::class)->makeJwtForClient([
                    'room' => $room,
                    'appointment' => $appointment,
                ]);
                $baseUrl = rtrim((string) config(
                    'services.jitsi.base_url',
                    'https://'.config('services.jitsi.domain', 'visio.aromamade.com')
                ), '/');

                return "{$baseUrl}/{$room}?jwt={$jwt}";
            } catch (\Throwable) {
                return route('webrtc.room', ['room' => $room]);
            }
        }

        return $appointment->meeting_link
            ?? $appointment->meeting?->join_url
            ?? $appointment->meeting?->url
            ?? null;
    }
}
