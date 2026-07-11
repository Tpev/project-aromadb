<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourneyAutomationRun;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyConversion;
use App\Domain\OfferJourneys\Models\OfferJourneyEntry;
use App\Domain\OfferJourneys\Models\OfferJourneyPipelineStage;
use App\Models\Appointment;
use App\Models\DigitalTrainingEnrollment;
use App\Models\GiftVoucherOrder;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class OfferJourneyConversionAttributor
{
    public function __construct(
        private readonly OfferJourneyAttributionContext $attributionContext,
        private readonly OfferJourneyPipeline $pipeline,
        private readonly OfferJourneyAbandonmentTracker $abandonmentTracker
    ) {
    }

    public function appointment(Appointment $appointment): void
    {
        $appointment->loadMissing(['clientProfile', 'product']);
        $email = $appointment->clientProfile?->email;
        $rawStatus = (string) $appointment->status;
        $this->attribute(
            $appointment,
            (int) $appointment->user_id,
            $email,
            'product',
            $appointment->product_id ? (int) $appointment->product_id : null,
            $this->status($rawStatus, ['confirmed', 'paid', 'scheduled']),
            $appointment->product ? (int) round(((float) $appointment->product->price) * 100) : null,
            'appointment'
        );
        $this->trackAbandonment($appointment, (int) $appointment->user_id, $email, 'product', $appointment->product_id ? (int) $appointment->product_id : null, $rawStatus);
    }

    public function reservation(Reservation $reservation): void
    {
        $reservation->loadMissing('event');
        $userId = (int) $reservation->event?->user_id;
        $rawStatus = (string) $reservation->status;
        $this->attribute(
            $reservation,
            $userId,
            $reservation->email,
            'event',
            $reservation->event_id ? (int) $reservation->event_id : null,
            $this->status($rawStatus, ['confirmed', 'paid']),
            $reservation->amount_ttc !== null ? (int) round(((float) $reservation->amount_ttc) * 100) : null,
            'event_registration'
        );
        $this->trackAbandonment($reservation, $userId, $reservation->email, 'event', $reservation->event_id ? (int) $reservation->event_id : null, $rawStatus);
    }

    public function training(DigitalTrainingEnrollment $enrollment): void
    {
        $enrollment->loadMissing('training');
        $this->attribute(
            $enrollment,
            (int) $enrollment->training?->user_id,
            $enrollment->participant_email ?: $enrollment->clientProfile?->email,
            'digital_training',
            $enrollment->digital_training_id ? (int) $enrollment->digital_training_id : null,
            'confirmed',
            $enrollment->training?->price_cents,
            'training_enrollment'
        );
    }

    public function giftVoucher(GiftVoucherOrder $order): void
    {
        $rawStatus = (string) $order->status;
        $this->attribute(
            $order,
            (int) $order->user_id,
            $order->buyer_email,
            'gift_voucher',
            null,
            $this->status($rawStatus, ['paid']),
            $order->amount_cents,
            'gift_voucher_purchase'
        );
        $this->trackAbandonment($order, (int) $order->user_id, $order->buyer_email, 'gift_voucher', null, $rawStatus);
    }

    private function attribute(
        Model $model,
        int $userId,
        ?string $email,
        string $sourceType,
        ?int $sourceId,
        ?string $status,
        ?int $amountCents,
        string $conversionType
    ): void {
        if (! config('offer_journeys.enabled') || $userId <= 0 || ! $model->getKey()) {
            return;
        }

        try {
            $key = 'oj:'.Str::snake(class_basename($model)).':'.$model->getKey();
            $existing = OfferJourneyConversion::query()->where('idempotency_key', $key)->first();

            if ($existing) {
                if ($status && $existing->status !== $status) {
                    $existing->update([
                        'status' => $status,
                        'cancelled_at' => $status === 'cancelled' ? now() : $existing->cancelled_at,
                        'refunded_at' => $status === 'refunded' ? now() : $existing->refunded_at,
                    ]);
                }
                return;
            }

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return;
            }

            $normalizedEmail = Str::lower(trim($email));
            $contact = OfferJourneyContact::query()
                ->where('user_id', $userId)
                ->where('email_normalized', $normalizedEmail)
                ->first();

            $entry = $contact ? $this->matchingEntry($contact, $sourceType, $sourceId) : null;
            if (! $entry) {
                $context = $this->attributionContext->resolve($userId, $sourceType, $sourceId);
                if ($context) {
                    $journey = $context['journey'];
                    $payload = $context['payload'];
                    $user = $journey->user;
                    $this->pipeline->ensureDefaults($user);
                    $contact ??= OfferJourneyContact::query()->firstOrCreate([
                        'user_id' => $userId,
                        'email_normalized' => $normalizedEmail,
                    ], [
                        'email' => $normalizedEmail,
                        'pipeline_stage_id' => \App\Domain\OfferJourneys\Models\OfferJourneyPipelineStage::query()
                            ->where('user_id', $userId)->where('system_key', 'new')->value('id'),
                        'status' => 'new',
                        'last_activity_at' => now(),
                    ]);
                    $entry = OfferJourneyEntry::query()->firstOrCreate([
                        'offer_journey_id' => $journey->id,
                        'offer_journey_contact_id' => $contact->id,
                    ], [
                        'status' => 'active',
                        'first_utm_source' => $payload['utm_source'] ?? null,
                        'first_utm_medium' => $payload['utm_medium'] ?? null,
                        'first_utm_campaign' => $payload['utm_campaign'] ?? null,
                        'last_utm_source' => $payload['utm_source'] ?? null,
                        'last_utm_medium' => $payload['utm_medium'] ?? null,
                        'last_utm_campaign' => $payload['utm_campaign'] ?? null,
                        'entered_at' => now(),
                        'last_activity_at' => now(),
                    ]);
                    $entry->setRelation('journey', $journey);
                }
            }
            if (! $entry) {
                return;
            }

            if ($status !== 'confirmed') {
                return;
            }

            DB::transaction(function () use ($entry, $contact, $model, $key, $amountCents, $conversionType) {
                OfferJourneyConversion::query()->create([
                    'offer_journey_id' => $entry->offer_journey_id,
                    'offer_journey_version_id' => $entry->journey->published_version_id,
                    'offer_journey_contact_id' => $contact->id,
                    'offer_journey_entry_id' => $entry->id,
                    'conversion_type' => $conversionType,
                    'convertible_type' => $model::class,
                    'convertible_id' => $model->getKey(),
                    'status' => 'confirmed',
                    'amount_cents' => $amountCents,
                    'currency' => 'EUR',
                    'attribution_model' => 'last_touch',
                    'attribution_json' => [
                        'utm_source' => $entry->last_utm_source ?: $entry->first_utm_source,
                        'utm_medium' => $entry->last_utm_medium ?: $entry->first_utm_medium,
                        'utm_campaign' => $entry->last_utm_campaign ?: $entry->first_utm_campaign,
                    ],
                    'idempotency_key' => $key,
                    'occurred_at' => now(),
                    'confirmed_at' => now(),
                ]);

                $entry->update(['status' => 'converted', 'converted_at' => now(), 'last_activity_at' => now()]);
                $convertedStage = OfferJourneyPipelineStage::query()
                    ->where('user_id', $contact->user_id)
                    ->where('system_key', 'converted')
                    ->value('id');
                $contact->update([
                    'status' => 'converted',
                    'pipeline_stage_id' => $convertedStage ?: $contact->pipeline_stage_id,
                    'converted_at' => now(),
                    'last_activity_at' => now(),
                ]);
                $contact->activities()->create([
                    'offer_journey_id' => $entry->offer_journey_id,
                    'type' => 'converted',
                    'title' => 'Conversion confirmée dans Olithea',
                    'metadata' => ['type' => $conversionType, 'model' => $model::class, 'id' => $model->getKey()],
                    'occurred_at' => now(),
                ]);
                OfferJourneyAutomationRun::query()
                    ->where('offer_journey_contact_id', $contact->id)
                    ->where('status', 'running')
                    ->whereHas('automation', fn ($query) => $query->where('offer_journey_id', $entry->offer_journey_id))
                    ->update(['status' => 'completed', 'exit_reason' => 'converted', 'next_action_at' => null, 'exited_at' => now()]);
            });
        } catch (Throwable $exception) {
            Log::warning('Offer journey conversion attribution failed without blocking the business action.', [
                'model' => $model::class,
                'model_id' => $model->getKey(),
                'exception' => $exception::class,
            ]);
        }
    }

    private function matchingEntry(OfferJourneyContact $contact, string $sourceType, ?int $sourceId): ?OfferJourneyEntry
    {
        return OfferJourneyEntry::query()
            ->where('offer_journey_contact_id', $contact->id)
            ->where('entered_at', '>=', now()->subDays(config('offer_journeys.attribution_days', 30)))
            ->whereHas('journey', function ($query) use ($sourceType, $sourceId) {
                $query->where('source_type', $sourceType);
                if ($sourceId !== null) {
                    $query->where('source_id', $sourceId);
                }
            })
            ->with('journey')
            ->latest('last_activity_at')
            ->first();
    }

    private function trackAbandonment(
        Model $model,
        int $userId,
        ?string $email,
        string $sourceType,
        ?int $sourceId,
        string $status
    ): void {
        try {
            $this->abandonmentTracker->sync($model, $userId, $email, $sourceType, $sourceId, $status);
        } catch (Throwable $exception) {
            Log::warning('Offer journey abandonment tracking failed without blocking the business action.', [
                'model' => $model::class,
                'model_id' => $model->getKey(),
                'exception' => $exception::class,
            ]);
        }
    }

    private function status(string $status, array $confirmed): ?string
    {
        if (in_array($status, $confirmed, true)) {
            return 'confirmed';
        }
        if (in_array($status, ['cancelled', 'canceled', 'Annulée', 'Annulee'], true)) {
            return 'cancelled';
        }
        if (in_array($status, ['refunded', 'remboursé', 'remboursee'], true)) {
            return 'refunded';
        }

        return null;
    }
}
