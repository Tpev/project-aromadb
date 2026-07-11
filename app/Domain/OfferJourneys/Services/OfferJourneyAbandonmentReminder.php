<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourneyAbandonmentCandidate;
use App\Domain\OfferJourneys\Models\OfferJourneyConversion;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageDelivery;
use App\Domain\OfferJourneys\Models\OfferJourneySuppression;
use App\Mail\OfferJourneyMessageMail;
use App\Models\NewsletterOptOut;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Throwable;

class OfferJourneyAbandonmentReminder
{
    public function __construct(private readonly OfferJourneySendingPolicy $sendingPolicy)
    {
    }

    public function send(int $candidateId): void
    {
        if (! config('offer_journeys.abandonment_reminders_enabled', false)
            || ! config('offer_journeys.email_enabled', false)
            || config('offer_journeys.pause_all_marketing_emails', true)) {
            return;
        }

        $candidate = OfferJourneyAbandonmentCandidate::query()->with(['user', 'journey', 'contact'])->find($candidateId);
        if (! $candidate || $candidate->state !== 'started' || $candidate->reminder_due_at->isFuture()) {
            return;
        }

        if (OfferJourneyConversion::query()->where('convertible_type', $candidate->source_type)->where('convertible_id', $candidate->source_id)->where('status', 'confirmed')->exists()) {
            $candidate->update(['state' => 'completed', 'completed_at' => now(), 'stop_reason' => 'conversion_confirmed']);
            return;
        }

        $contact = $candidate->contact;
        $email = Str::lower(trim((string) $contact->email));
        $frequencySince = now()->subHours(max(1, (int) config('offer_journeys.contact_frequency_hours', 72)));
        $blocked = ! filter_var($email, FILTER_VALIDATE_EMAIL)
            || ! $contact->consents()->where('purpose', 'marketing_follow_up')->where('status', 'granted')->whereNull('withdrawn_at')->exists()
            || OfferJourneySuppression::query()->where('user_id', $candidate->user_id)->where('email_normalized', $email)->exists()
            || NewsletterOptOut::query()->where('user_id', $candidate->user_id)->whereRaw('LOWER(email) = ?', [$email])->exists()
            || $contact->messageDeliveries()->where('category', 'marketing')->where('is_test', false)->whereNotNull('sent_at')->where('sent_at', '>=', $frequencySince)->exists()
            || $this->sendingPolicy->blockingReason($candidate->user, 'marketing');

        if ($blocked) {
            $candidate->update(['state' => 'cancelled', 'cancelled_at' => now(), 'stop_reason' => 'sending_guard']);
            return;
        }

        $delivery = OfferJourneyMessageDelivery::query()->firstOrCreate([
            'idempotency_key' => 'oj:abandonment:'.$candidate->id,
        ], [
            'user_id' => $candidate->user_id,
            'offer_journey_id' => $candidate->offer_journey_id,
            'offer_journey_contact_id' => $contact->id,
            'node_key' => 'abandonment_reminder',
            'category' => 'marketing',
            'status' => 'sending',
            'recipient_email' => $contact->email,
            'subject' => 'Votre demande concernant '.$candidate->journey->name,
            'is_test' => false,
            'metadata' => ['abandonment_candidate_id' => $candidate->id],
        ]);
        if (! $delivery->wasRecentlyCreated) {
            return;
        }

        try {
            $url = route('offer-journeys.public.show', ['therapist' => $candidate->user, 'journeySlug' => $candidate->journey->slug]);
            $body = "Bonjour ".($contact->first_name ?: '').",\n\nVous aviez commencé une demande concernant « {$candidate->journey->name} » sans la terminer. Si vous le souhaitez, vous pouvez la reprendre ici : {$url}\n\nSi ce n’est plus d’actualité, vous n’avez rien à faire.\n\n".($candidate->user->company_name ?: $candidate->user->name);
            Mail::to($contact->email)->send(new OfferJourneyMessageMail(
                $candidate->user,
                $delivery->subject,
                $body,
                URL::temporarySignedRoute('offer-journeys.unsubscribe.show', now()->addDays(90), ['contact' => $contact]),
                'marketing',
                $delivery->id
            ));
            $delivery->update(['status' => 'sent', 'sent_at' => now()]);
            $candidate->update(['state' => 'reminded', 'reminded_at' => now()]);
        } catch (Throwable $exception) {
            $delivery->update(['status' => 'failed', 'failed_at' => now(), 'failure_reason' => Str::limit($exception->getMessage(), 255)]);
        }
    }
}
