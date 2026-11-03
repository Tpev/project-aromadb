<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClientBookingBlockService
{
    public const REFUSAL_MESSAGE = 'Désolé, nous ne pouvons pas prendre votre réservation pour le moment.';

    public function normalizeEmail(?string $email): string
    {
        return Str::lower(trim($email ?? ''));
    }

    public function isBlocked(int $therapistId, ?string $email): bool
    {
        $email = $this->normalizeEmail($email);

        return $email !== '' && DB::table('client_booking_blocks')
            ->where('user_id', $therapistId)
            ->where('email', $email)
            ->exists();
    }

    public function assertAllowed(int $therapistId, ?string $email): void
    {
        if ($this->isBlocked($therapistId, $email)) {
            throw ValidationException::withMessages(['booking' => self::REFUSAL_MESSAGE]);
        }
    }

    public function setBlocked(int $therapistId, ?string $email, bool $blocked): void
    {
        $email = $this->normalizeEmail($email);
        if (validator(['email' => $email], ['email' => 'required|email|max:255'])->fails()) {
            throw ValidationException::withMessages([
                'booking_block' => 'Renseignez une adresse email valide sur la fiche client pour gérer le blocage.',
            ]);
        }

        if ($blocked) {
            // The unique pair makes repeated clicks safe and covers duplicate client profiles.
            DB::table('client_booking_blocks')->insertOrIgnore([
                'user_id' => $therapistId,
                'email' => $email,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('client_booking_blocks')->where('user_id', $therapistId)->where('email', $email)->delete();
        }
    }
}
