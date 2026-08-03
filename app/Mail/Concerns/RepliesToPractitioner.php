<?php

namespace App\Mail\Concerns;

use App\Models\User;
use App\Support\PractitionerReplyToResolver;

trait RepliesToPractitioner
{
    protected function applyPractitionerReplyTo(?User $practitioner): static
    {
        $replyTo = app(PractitionerReplyToResolver::class)->resolve($practitioner);

        if ($replyTo) {
            $this->replyTo($replyTo->address, $replyTo->name);
        }

        return $this;
    }

    protected function practitionerReplyToAddresses(?User $practitioner): array
    {
        $replyTo = app(PractitionerReplyToResolver::class)->resolve($practitioner);

        return $replyTo ? [$replyTo] : [];
    }
}
