<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Mail\Mailables\Address;

final class PractitionerReplyToResolver
{
    public function resolve(?User $practitioner): ?Address
    {
        if (! config('mail.practitioner_reply_to.enabled', true)) {
            return null;
        }

        $address = $this->firstValidAddress([
            $practitioner?->company_email,
            $practitioner?->email,
            config('mail.from.address'),
            'contact@olithea.fr',
        ]);

        $name = $this->firstNonEmptyName([
            $practitioner?->company_name,
            $practitioner?->business_name,
            $practitioner?->name,
            config('mail.from.name'),
            'Olithea',
        ]);

        return new Address($address, $name);
    }

    private function firstValidAddress(array $candidates): string
    {
        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);

            if (filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                return $candidate;
            }
        }

        return 'contact@olithea.fr';
    }

    private function firstNonEmptyName(array $candidates): string
    {
        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);
            $candidate = preg_replace('/[\r\n]+/', ' ', $candidate) ?? '';

            if ($candidate !== '') {
                return $candidate;
            }
        }

        return 'Olithea';
    }
}
