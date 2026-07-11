<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageCampaign;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageDelivery;
use App\Domain\OfferJourneys\Models\OfferJourneySuppression;
use App\Mail\OfferJourneyMessageMail;
use App\Models\NewsletterOptOut;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Throwable;

class OfferJourneyCampaignSender
{
    public function __construct(private readonly OfferJourneySendingPolicy $sendingPolicy)
    {
    }

    public function send(int $campaignId): void
    {
        if (! config('offer_journeys.campaigns_enabled', false)
            || ! config('offer_journeys.email_enabled', false)
            || config('offer_journeys.pause_all_marketing_emails', true)) {
            return;
        }

        $campaign = DB::transaction(function () use ($campaignId) {
            $campaign = OfferJourneyMessageCampaign::query()->lockForUpdate()->find($campaignId);
            if (! $campaign || $campaign->status !== 'scheduled' || $campaign->scheduled_at?->isFuture()) {
                return null;
            }
            $campaign->update(['status' => 'processing', 'processing_started_at' => now()]);

            return $campaign;
        });
        if (! $campaign) {
            return;
        }

        $campaign->load(['user', 'journeys']);
        $journeyIds = $campaign->journeys->pluck('id');
        $sent = 0;
        $skipped = 0;

        if ($journeyIds->isEmpty() || ($reason = $this->sendingPolicy->blockingReason($campaign->user, 'marketing'))) {
            $campaign->update([
                'status' => 'scheduled',
                'processing_started_at' => null,
                'summary_json' => ['blocking_reason' => $reason ?? 'no_journey'],
            ]);

            return;
        }

        $frequencySince = now()->subHours(max(1, (int) config('offer_journeys.contact_frequency_hours', 72)));
        $contacts = OfferJourneyContact::query()
            ->where('user_id', $campaign->user_id)
            ->whereHas('entries', fn ($query) => $query->whereIn('offer_journey_id', $journeyIds))
            ->whereHas('consents', fn ($query) => $query
                ->where('purpose', 'marketing_follow_up')
                ->where('status', 'granted')
                ->whereNull('withdrawn_at'))
            ->whereDoesntHave('messageDeliveries', fn ($query) => $query
                ->where('category', 'marketing')
                ->where('is_test', false)
                ->whereNotNull('sent_at')
                ->where('sent_at', '>=', $frequencySince))
            ->with(['entries' => fn ($query) => $query->whereIn('offer_journey_id', $journeyIds)->latest('last_activity_at')])
            ->get()
            ->unique('email_normalized');

        $campaign->update(['eligible_count' => $contacts->count()]);

        foreach ($contacts as $contact) {
            $journey = $campaign->journeys->firstWhere('id', $contact->entries->first()?->offer_journey_id) ?? $campaign->journeys->first();
            if (! $journey || $this->blocked($campaign, $contact, $frequencySince)) {
                $skipped++;
                continue;
            }

            $key = 'oj:campaign:'.$campaign->id.':contact:'.$contact->id;
            $delivery = OfferJourneyMessageDelivery::query()->firstOrCreate(['idempotency_key' => $key], [
                'user_id' => $campaign->user_id,
                'offer_journey_id' => $journey->id,
                'offer_journey_contact_id' => $contact->id,
                'node_key' => 'campaign_'.$campaign->id,
                'category' => 'marketing',
                'status' => 'sending',
                'recipient_email' => $contact->email,
                'subject' => $this->render($campaign->subject, $contact, $journey, $campaign->user),
                'is_test' => false,
                'metadata' => ['campaign_id' => $campaign->id],
            ]);
            if (! $delivery->wasRecentlyCreated || $delivery->status === 'sent') {
                continue;
            }

            try {
                Mail::to($contact->email)->send(new OfferJourneyMessageMail(
                    $campaign->user,
                    $delivery->subject,
                    $this->render($campaign->body, $contact, $journey, $campaign->user),
                    URL::temporarySignedRoute('offer-journeys.unsubscribe.show', now()->addDays(90), ['contact' => $contact]),
                    'marketing',
                    $delivery->id
                ));
                $delivery->update(['status' => 'sent', 'sent_at' => now()]);
                $sent++;
            } catch (Throwable $exception) {
                $delivery->update(['status' => 'failed', 'failed_at' => now(), 'failure_reason' => Str::limit($exception->getMessage(), 255)]);
                $skipped++;
            }
        }

        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
            'sent_count' => $sent,
            'skipped_count' => $skipped,
            'summary_json' => ['frequency_hours' => config('offer_journeys.contact_frequency_hours', 72)],
        ]);
    }

    private function blocked(OfferJourneyMessageCampaign $campaign, OfferJourneyContact $contact, $frequencySince): bool
    {
        if ($campaign->status === 'cancelled' || ! filter_var($contact->email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        if ($this->sendingPolicy->blockingReason($campaign->user, 'marketing')) {
            return true;
        }
        $email = Str::lower(trim((string) $contact->email));

        return ! $contact->consents()->where('purpose', 'marketing_follow_up')->where('status', 'granted')->whereNull('withdrawn_at')->exists()
            || OfferJourneySuppression::query()->where('user_id', $campaign->user_id)->where('email_normalized', $email)->exists()
            || NewsletterOptOut::query()->where('user_id', $campaign->user_id)->whereRaw('LOWER(email) = ?', [$email])->exists()
            || $contact->messageDeliveries()->where('category', 'marketing')->where('is_test', false)->whereNotNull('sent_at')->where('sent_at', '>=', $frequencySince)->exists();
    }

    private function render(string $text, OfferJourneyContact $contact, $journey, $user): string
    {
        return strtr($text, [
            '{{prenom}}' => $contact->first_name ?: 'bonjour',
            '{{offre}}' => $journey->name,
            '{{nom_praticien}}' => $user->company_name ?: $user->name,
            '{{lien_offre}}' => route('offer-journeys.public.show', ['therapist' => $user, 'journeySlug' => $journey->slug]),
        ]);
    }
}
