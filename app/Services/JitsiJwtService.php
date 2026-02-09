<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Illuminate\Support\Str;

class JitsiJwtService
{
    /**
     * Low-level generator (you can still use it directly if you want).
     */
    public function generate(array $claims = [], int $ttlSeconds = 60 * 60 * 24 * 365): string
    {
        $appId  = config('services.jitsi.app_id');
        $secret = config('services.jitsi.secret');

        if (empty($appId) || empty($secret)) {
            throw new \RuntimeException('Missing Jitsi JWT config. Please set JITSI_APP_ID and JITSI_APP_SECRET.');
        }

        $now = time();

        $payload = array_merge([
            'iss' => $appId,          // app_id
            'aud' => 'jitsi',
            'iat' => $now,
            'nbf' => $now - 5,
            'exp' => $now + $ttlSeconds,
        ], $claims);

        // Jitsi expects "room" (specific room or "*")
        if (!array_key_exists('room', $payload) || $payload['room'] === null || $payload['room'] === '') {
            $payload['room'] = '*';
        }

        return JWT::encode($payload, $secret, 'HS256');
    }

    /**
     * Therapist token => moderator.
     *
     * Usage:
     * $jwt = $jitsi->makeJwtForTherapist(['room' => $room, 'appointment' => $appointment]);
     */
    public function makeJwtForTherapist(array $opts): string
    {
        $room = (string)($opts['room'] ?? '');
        $appointment = $opts['appointment'] ?? null;

        // Try to infer therapist info from appointment->user
        $therapist = $appointment?->user ?? auth()->user();

        $displayName =
            trim(($therapist?->first_name ?? '') . ' ' . ($therapist?->last_name ?? ''))
            ?: ($therapist?->name ?? 'Thérapeute');

        $email = $therapist?->email ?? null;

        return $this->generate([
            'room' => $room,

            // optional but common in examples:
            'sub' => config('services.jitsi.domain', 'visio.aromamade.com'),

            'context' => [
                'user' => [
                    'id' => (string)($therapist?->id ?? Str::uuid()),
                    'name' => $displayName,
                    'email' => $email,
                    'moderator' => true,
                ],
                'group' => 'therapist',
            ],
        ]);
    }

    /**
     * Client token => NOT moderator.
     *
     * Usage:
     * $jwt = $jitsi->makeJwtForClient(['room' => $room, 'appointment' => $appointment]);
     */
    public function makeJwtForClient(array $opts): string
    {
        $room = (string)($opts['room'] ?? '');
        $appointment = $opts['appointment'] ?? null;

        $client = $appointment?->clientProfile ?? null;

        $displayName =
            trim(($client?->first_name ?? '') . ' ' . ($client?->last_name ?? ''))
            ?: 'Client';

        $email = $client?->email ?? null;

        return $this->generate([
            'room' => $room,

            // optional but common in examples:
            'sub' => config('services.jitsi.domain', 'visio.aromamade.com'),

            'context' => [
                'user' => [
                    'id' => (string)($client?->id ?? Str::uuid()),
                    'name' => $displayName,
                    'email' => $email,
                    'moderator' => false,
                ],
                'group' => 'client',
            ],
        ]);
    }
}
