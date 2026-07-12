<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageCampaign;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageDelivery;
use App\Domain\OfferJourneys\Models\OfferJourneySuppression;
use App\Mail\OfferJourneyMessageMail;
use App\Models\NewsletterOptOut;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Throwable;

class OfferJourneyCampaignSender
{
    public function __construct(
        private readonly OfferJourneySendingPolicy $sendingPolicy,
        private readonly OfferJourneyCampaignAudience $audience
    ) {
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

        $campaign->load(['user', 'journeys', 'segment.rules']);
        $segmentCampaignsDisabled = $campaign->audience_type === 'segment'
            && ! config('offer_journeys.segment_campaigns_enabled', false);
        $emailEditorDisabled = $campaign->content_json
            && ! config('offer_journeys.email_editor_enabled', false);
        $hasAudience = $campaign->audience_type === 'segment'
            ? $campaign->segment !== null
            : $campaign->journeys->isNotEmpty();
        $reason = $this->sendingPolicy->blockingReason($campaign->user, 'marketing');

        if ($segmentCampaignsDisabled || $emailEditorDisabled || ! $hasAudience || $reason) {
            $campaign->update([
                'status' => 'scheduled',
                'processing_started_at' => null,
                'summary_json' => [
                    'blocking_reason' => $segmentCampaignsDisabled
                        ? 'segment_campaigns_disabled'
                        : ($emailEditorDisabled ? 'email_editor_disabled' : ($reason ?: 'no_audience')),
                ],
            ]);

            return;
        }

        $frequencySince = now()->subHours(max(1, (int) config('offer_journeys.contact_frequency_hours', 72)));
        $resolved = $this->audience->resolve(
            $this->audience->queryForCampaign($campaign),
            (int) $campaign->user_id,
            $frequencySince
        );
        $campaign->update([
            'eligible_count' => $resolved['summary']['eligible'],
            'summary_json' => $resolved['summary'],
        ]);

        $sent = 0;
        $failed = 0;
        $skipped = max(0, $resolved['summary']['matching'] - $resolved['summary']['eligible']);

        foreach ($resolved['eligible'] as $contact) {
            if ($this->blockedNow($campaign, $contact, $frequencySince)) {
                $skipped++;
                continue;
            }

            $journey = $this->messageJourney($campaign, $contact);
            $key = 'oj:campaign:'.$campaign->id.':contact:'.$contact->id;
            $delivery = OfferJourneyMessageDelivery::query()->firstOrCreate(['idempotency_key' => $key], [
                'user_id' => $campaign->user_id,
                'offer_journey_id' => $journey?->id,
                'offer_journey_contact_id' => $contact->id,
                'offer_journey_message_campaign_id' => $campaign->id,
                'node_key' => 'campaign_'.$campaign->id,
                'category' => 'marketing',
                'status' => 'sending',
                'recipient_email' => $contact->email,
                'subject' => $this->render($campaign->subject, $campaign, $contact, $journey),
                'is_test' => false,
                'metadata' => ['campaign_id' => $campaign->id, 'audience_type' => $campaign->audience_type],
            ]);
            if (! $delivery->wasRecentlyCreated || $delivery->status === 'sent') {
                continue;
            }

            try {
                $variables = $this->variables($campaign, $contact, $journey);
                Mail::to($contact->email)->send(new OfferJourneyMessageMail(
                    $campaign->user,
                    $delivery->subject,
                    $this->render($campaign->body, $campaign, $contact, $journey),
                    URL::temporarySignedRoute('offer-journeys.unsubscribe.show', now()->addDays(90), ['contact' => $contact]),
                    'marketing',
                    $delivery->id,
                    $campaign,
                    $variables
                ));
                $delivery->update(['status' => 'sent', 'sent_at' => now()]);
                $sent++;
            } catch (Throwable $exception) {
                $delivery->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'failure_reason' => Str::limit($exception->getMessage(), 255),
                ]);
                $failed++;
                $skipped++;
            }
        }

        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
            'sent_count' => $sent,
            'skipped_count' => $skipped,
            'summary_json' => [
                ...$resolved['summary'],
                'failed' => $failed,
                'frequency_hours' => config('offer_journeys.contact_frequency_hours', 72),
            ],
        ]);
    }

    private function blockedNow(
        OfferJourneyMessageCampaign $campaign,
        OfferJourneyContact $contact,
        CarbonInterface $frequencySince
    ): bool {
        if (! filter_var($contact->email, FILTER_VALIDATE_EMAIL)
            || $this->sendingPolicy->blockingReason($campaign->user, 'marketing')) {
            return true;
        }

        $email = Str::lower(trim((string) $contact->email));

        return ! $contact->consents()->where('purpose', 'marketing_follow_up')->where('status', 'granted')->whereNull('withdrawn_at')->exists()
            || OfferJourneySuppression::query()->where('user_id', $campaign->user_id)->where('email_normalized', $email)->exists()
            || NewsletterOptOut::query()->where('user_id', $campaign->user_id)->whereRaw('LOWER(email) = ?', [$email])->exists()
            || $contact->messageDeliveries()->where('category', 'marketing')->where('is_test', false)->whereNotNull('sent_at')->where('sent_at', '>=', $frequencySince)->exists();
    }

    private function messageJourney(OfferJourneyMessageCampaign $campaign, OfferJourneyContact $contact): ?OfferJourney
    {
        if ($campaign->audience_type === 'segment') {
            return $campaign->journeys->first() ?: $contact->entries->first()?->journey;
        }

        $journeyIds = $campaign->journeys->pluck('id');
        $entry = $contact->entries->first(fn ($entry) => $journeyIds->contains($entry->offer_journey_id));

        return $campaign->journeys->firstWhere('id', $entry?->offer_journey_id) ?: $campaign->journeys->first();
    }

    private function render(
        string $text,
        OfferJourneyMessageCampaign $campaign,
        OfferJourneyContact $contact,
        ?OfferJourney $journey
    ): string {
        return strtr($text, collect($this->variables($campaign, $contact, $journey))
            ->mapWithKeys(fn ($value, $key) => ['{{'.$key.'}}' => $value])->all());
    }

    private function variables(
        OfferJourneyMessageCampaign $campaign,
        OfferJourneyContact $contact,
        ?OfferJourney $journey
    ): array {
        return [
            'prenom' => $contact->first_name ?: 'à vous',
            'offre' => $journey?->name ?: $campaign->name,
            'nom_praticien' => $campaign->user->company_name ?: $campaign->user->name,
            'lien_offre' => $journey
                ? route('offer-journeys.public.show', ['therapist' => $campaign->user, 'journeySlug' => $journey->slug])
                : '',
        ];
    }
}
