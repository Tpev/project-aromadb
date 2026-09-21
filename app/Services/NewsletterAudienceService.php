<?php

namespace App\Services;

use App\Models\Audience;
use App\Models\ClientProfile;
use App\Models\Newsletter;
use App\Models\NewsletterContact;
use App\Models\NewsletterOptOut;
use Illuminate\Support\Collection;

class NewsletterAudienceService
{
    public function recipients(Newsletter $newsletter): Collection
    {
        $userId = $newsletter->user_id;
        $optOuts = NewsletterOptOut::where('user_id', $userId)->pluck('email')
            ->map(fn ($email) => NewsletterContact::normalizeEmail($email))->flip();
        $clients = ClientProfile::where('user_id', $userId)->whereNotNull('email');
        $contacts = collect();
        if ($newsletter->audience_id) {
            $audience = Audience::where('user_id', $userId)->findOrFail($newsletter->audience_id);
            $clients->whereIn('id', $audience->clients()->pluck('client_profiles.id'));
            $contacts = $audience->newsletterContacts()->where('newsletter_contacts.user_id', $userId)
                ->where('status', 'active')->get();
        }

        // "Tous les clients" keeps its scope. Imported contacts belong to explicit audiences.
        return $clients->get()->map(fn ($client) => (object) [
            'client_profile_id' => $client->id, 'newsletter_contact_id' => null,
            'email' => NewsletterContact::normalizeEmail($client->email),
            'first_name' => $client->first_name, 'last_name' => $client->last_name,
        ])->concat($contacts->map(fn ($contact) => (object) [
            'client_profile_id' => null, 'newsletter_contact_id' => $contact->id,
            'email' => NewsletterContact::normalizeEmail($contact->email),
            'first_name' => $contact->first_name, 'last_name' => $contact->last_name,
        ]))->filter(fn ($recipient) => filter_var($recipient->email, FILTER_VALIDATE_EMAIL)
            && ! $optOuts->has($recipient->email))
            ->unique('email')->values();
    }
}
