<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyConsent;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyContactActivity;
use App\Domain\OfferJourneys\Models\OfferJourneyEntry;
use App\Domain\OfferJourneys\Models\OfferJourneyEvent;
use App\Domain\OfferJourneys\Models\OfferJourneyPageVersion;
use App\Domain\OfferJourneys\Models\OfferJourneyFormAnswer;
use App\Models\User;
use App\Support\OfferJourneys\OfferJourneyAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class OfferJourneyContactCapture
{
    public function __construct(
        private readonly OfferJourneyPipeline $pipeline,
        private readonly OfferJourneyCampaignResolver $campaignResolver,
        private readonly OfferJourneyAutomationStarter $automationStarter,
        private readonly OfferJourneyTransitionResolver $transitionResolver
    ) {
    }

    public function capture(
        User $therapist,
        OfferJourney $journey,
        OfferJourneyPageVersion $page,
        array $data,
        Request $request
    ): array {
        return DB::transaction(function () use ($therapist, $journey, $page, $data, $request) {
            $email = Str::lower(trim($data['email']));
            $this->pipeline->ensureDefaults($therapist);
            $newStageId = $therapist->getKey()
                ? \App\Domain\OfferJourneys\Models\OfferJourneyPipelineStage::query()
                    ->where('user_id', $therapist->id)
                    ->where('system_key', 'new')
                    ->value('id')
                : null;

            $contact = OfferJourneyContact::withTrashed()
                ->where('user_id', $therapist->id)
                ->where('email_normalized', $email)
                ->first();

            if (! $contact) {
                $contact = OfferJourneyContact::query()->create([
                    'user_id' => $therapist->id,
                    'pipeline_stage_id' => $newStageId,
                    'email' => $email,
                    'email_normalized' => $email,
                    'first_name' => $data['first_name'] ?? null,
                    'last_name' => $data['last_name'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'phone_normalized' => $this->normalizePhone($data['phone'] ?? null),
                    'contact_preference' => $data['contact_preference'] ?? 'email',
                    'city' => $data['city'] ?? null,
                    'postal_code' => $data['postal_code'] ?? null,
                    'status' => 'new',
                    'last_activity_at' => now(),
                ]);
            } else {
                if ($contact->trashed()) {
                    $contact->restore();
                }

                $contact->fill([
                    'email' => $email,
                    'first_name' => ($data['first_name'] ?? null) ?: $contact->first_name,
                    'last_name' => ($data['last_name'] ?? null) ?: $contact->last_name,
                    'phone' => ($data['phone'] ?? null) ?: $contact->phone,
                    'phone_normalized' => $this->normalizePhone(($data['phone'] ?? null) ?: $contact->phone),
                    'last_activity_at' => now(),
                ])->save();
            }

            $attribution = $this->campaignResolver->attribution($journey, $request);
            $entry = OfferJourneyEntry::query()->firstOrCreate([
                'offer_journey_id' => $journey->id,
                'offer_journey_contact_id' => $contact->id,
            ], [
                'current_page_id' => $page->offer_journey_page_id,
                'status' => 'active',
                'first_utm_source' => $attribution['utm_source'],
                'first_utm_medium' => $attribution['utm_medium'],
                'first_utm_campaign' => $attribution['utm_campaign'],
                'entered_at' => now(),
                'last_activity_at' => now(),
            ]);

            $entry->update([
                'current_page_id' => $page->offer_journey_page_id,
                'last_utm_source' => $attribution['utm_source'],
                'last_utm_medium' => $attribution['utm_medium'],
                'last_utm_campaign' => $attribution['utm_campaign'],
                'last_activity_at' => now(),
            ]);

            $form = $page->content_json['_form'] ?? [];
            $this->recordConsent(
                $contact,
                $journey,
                'requested_response',
                'granted',
                (string) ($form['privacy_text'] ?? config('offer_journeys.legal.request_privacy_text')),
                $request,
                $attribution,
                'precontractual_request'
            );

            if (! empty($data['marketing_consent'])) {
                $this->recordConsent(
                    $contact,
                    $journey,
                    'marketing_follow_up',
                    'granted',
                    (string) config('offer_journeys.legal.marketing_consent_text'),
                    $request,
                    $attribution,
                    'consent'
                );
            }

            foreach ($form['fields'] ?? [] as $field) {
                $fieldName = (string) ($field['name'] ?? '');
                if (! str_starts_with($fieldName, 'custom_') || ! array_key_exists($fieldName, $data)) {
                    continue;
                }
                $value = $data[$fieldName];
                OfferJourneyFormAnswer::query()->create([
                    'offer_journey_contact_id' => $contact->id,
                    'offer_journey_id' => $journey->id,
                    'offer_journey_page_version_id' => $page->id,
                    'field_name' => $fieldName,
                    'field_label' => (string) ($field['label'] ?? $fieldName),
                    'field_type' => (string) ($field['type'] ?? 'text'),
                    'purpose' => (string) ($field['purpose'] ?? 'repondre a la demande'),
                    'value_json' => is_array($value) ? ['values' => array_values($value)] : ['value' => $value],
                    'answered_at' => now(),
                ]);
            }

            OfferJourneyContactActivity::query()->create([
                'offer_journey_contact_id' => $contact->id,
                'offer_journey_id' => $journey->id,
                'type' => 'lead_captured',
                'title' => 'Contact recueilli depuis le parcours '.$journey->name,
                'metadata' => ['page_id' => $page->offer_journey_page_id],
                'occurred_at' => now(),
            ]);

            $this->recordAnalytics($journey, $page, $contact, $entry, $request);
            $this->automationStarter->start($journey->loadMissing('user'), $contact, $entry);

            return [
                'contact' => $contact,
                'entry' => $entry,
                'next_page_slug' => $this->nextPageSlug($journey, $page, $data),
            ];
        });
    }

    private function recordConsent(
        OfferJourneyContact $contact,
        OfferJourney $journey,
        string $purpose,
        string $status,
        string $text,
        Request $request,
        array $attribution,
        string $legalBasis
    ): void {
        OfferJourneyConsent::query()->create([
            'offer_journey_contact_id' => $contact->id,
            'offer_journey_id' => $journey->id,
            'purpose' => $purpose,
            'legal_basis' => $legalBasis,
            'status' => $status,
            'text_version' => (string) config('offer_journeys.legal.consent_text_version', 'draft-v1'),
            'text_snapshot' => $text,
            'source' => 'public_journey_form',
            'context_json' => array_filter([
                'campaign_id' => $attribution['campaign']?->id,
                'utm_source' => $attribution['utm_source'] ?? null,
                'utm_medium' => $attribution['utm_medium'] ?? null,
                'utm_campaign' => $attribution['utm_campaign'] ?? null,
            ], fn ($value): bool => filled($value)),
            'ip_hash' => $request->ip()
                ? hash_hmac('sha256', $request->ip(), (string) config('app.key'))
                : null,
            'user_agent_summary' => Str::limit((string) $request->userAgent(), 255, ''),
            'granted_at' => now(),
        ]);
    }

    private function recordAnalytics(
        OfferJourney $journey,
        OfferJourneyPageVersion $page,
        OfferJourneyContact $contact,
        OfferJourneyEntry $entry,
        Request $request
    ): void {
        if (! app(OfferJourneyAccess::class)->trackingAvailable()) {
            return;
        }

        try {
            $attribution = $this->campaignResolver->attribution($journey, $request);
            OfferJourneyEvent::query()->create([
                'offer_journey_id' => $journey->id,
                'offer_journey_version_id' => $page->offer_journey_version_id,
                'offer_journey_page_id' => $page->offer_journey_page_id,
                'offer_journey_contact_id' => $contact->id,
                'offer_journey_entry_id' => $entry->id,
                'offer_journey_campaign_link_id' => $attribution['campaign']?->id,
                'session_id' => $request->attributes->get('offer_journey_visitor_id') ?: $request->cookie('oj_visitor'),
                'event_name' => 'lead_captured',
                'url' => Str::limit($request->fullUrl(), 2000, ''),
                'referer' => Str::limit((string) $request->headers->get('referer'), 2000, ''),
                'utm_source' => $attribution['utm_source'],
                'utm_medium' => $attribution['utm_medium'],
                'utm_campaign' => $attribution['utm_campaign'],
                'occurred_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Offer journey lead analytics failed.', [
                'journey_id' => $journey->id,
                'exception' => $exception::class,
            ]);
        }
    }

    private function nextPageSlug(OfferJourney $journey, OfferJourneyPageVersion $page, array $context): ?string
    {
        $next = $this->transitionResolver->nextPageSlug($journey, $page, $context);
        if ($next) {
            return $next;
        }

        return $journey->publishedVersion?->pages->firstWhere('type', 'thank_you')?->slug;
    }

    private function normalizePhone(?string $phone): ?string
    {
        $normalized = preg_replace('/[^0-9+]/', '', (string) $phone);

        return $normalized !== '' ? $normalized : null;
    }
}
