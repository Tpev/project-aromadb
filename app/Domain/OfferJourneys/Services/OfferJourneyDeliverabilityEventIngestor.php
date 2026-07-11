<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourneyDeliverabilityEvent;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageDelivery;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OfferJourneyDeliverabilityEventIngestor
{
    public function __construct(private readonly OfferJourneyEmailSuppressionService $suppressions)
    {
    }

    public function ingest(array $sns): int
    {
        $payload = json_decode((string) ($sns['Message'] ?? ''), true);
        if (! is_array($payload)) {
            return 0;
        }

        $type = Str::lower((string) ($payload['notificationType'] ?? $payload['eventType'] ?? ''));
        if (! in_array($type, ['delivery', 'bounce', 'complaint', 'reject'], true)) {
            return 0;
        }

        $providerMessageId = (string) Arr::get($payload, 'mail.messageId', '');
        $delivery = $this->findDelivery($payload, $providerMessageId);
        $recipients = $this->recipients($payload, $type);
        $count = 0;

        foreach ($recipients as $recipient) {
            $email = Str::lower(trim((string) ($recipient['email'] ?? '')));
            $subtype = $this->subtype($payload, $type, $recipient);
            $diagnostic = $this->diagnostic($payload, $type, $recipient);
            $eventKey = hash('sha256', implode('|', [
                (string) ($sns['TopicArn'] ?? ''),
                (string) ($sns['MessageId'] ?? ''),
                $type,
                $email,
            ]));

            DB::transaction(function () use ($eventKey, $sns, $providerMessageId, $delivery, $type, $subtype, $email, $diagnostic, $payload, &$count): void {
                $event = OfferJourneyDeliverabilityEvent::query()->firstOrCreate([
                    'event_key' => $eventKey,
                ], [
                    'sns_message_id' => $sns['MessageId'] ?? null,
                    'provider_message_id' => $providerMessageId ?: null,
                    'user_id' => $delivery?->user_id,
                    'offer_journey_message_delivery_id' => $delivery?->id,
                    'event_type' => $type,
                    'event_subtype' => $subtype,
                    'recipient_email' => filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null,
                    'diagnostic' => Str::limit($diagnostic, 1000),
                    'metadata' => $this->sanitizedMetadata($payload, $type),
                    'occurred_at' => $this->occurredAt($payload, $type),
                ]);

                if (! $event->wasRecentlyCreated) {
                    return;
                }

                $count++;
                $this->updateDelivery($delivery, $type, $diagnostic, $providerMessageId);

                $shouldSuppress = $type === 'complaint'
                    || $type === 'reject'
                    || ($type === 'bounce' && $subtype === 'permanent');
                if ($shouldSuppress && $delivery?->user && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->suppressions->suppress(
                        $delivery->user,
                        $email,
                        'ses_'.$type,
                        $diagnostic ?: 'Signal de delivrabilite Amazon SES',
                        'amazon_ses'
                    );
                }
            });
        }

        return $count;
    }

    private function findDelivery(array $payload, string $providerMessageId): ?OfferJourneyMessageDelivery
    {
        $tagId = Arr::get($payload, 'mail.tags.offer_delivery_id.0');
        if (ctype_digit((string) $tagId)) {
            return OfferJourneyMessageDelivery::query()->with('user')->find((int) $tagId);
        }

        if ($providerMessageId !== '') {
            $delivery = OfferJourneyMessageDelivery::query()->with('user')
                ->where('provider_message_id', $providerMessageId)->first();
            if ($delivery) {
                return $delivery;
            }
        }

        $email = Arr::first($this->recipients($payload, Str::lower((string) ($payload['notificationType'] ?? ''))))['email'] ?? null;

        return $email ? OfferJourneyMessageDelivery::query()->with('user')
            ->whereRaw('LOWER(recipient_email) = ?', [Str::lower(trim($email))])
            ->where('created_at', '>=', now()->subDays(14))
            ->latest('id')->first() : null;
    }

    private function recipients(array $payload, string $type): array
    {
        $rows = match ($type) {
            'bounce' => Arr::get($payload, 'bounce.bouncedRecipients', []),
            'complaint' => Arr::get($payload, 'complaint.complainedRecipients', []),
            'delivery' => array_map(fn (string $email): array => ['emailAddress' => $email], Arr::get($payload, 'delivery.recipients', [])),
            'reject' => array_map(fn (string $email): array => ['emailAddress' => $email], Arr::get($payload, 'mail.destination', [])),
            default => [],
        };

        return collect($rows)->map(fn (array $row): array => [
            'email' => $row['emailAddress'] ?? null,
            'status' => $row['status'] ?? null,
            'action' => $row['action'] ?? null,
            'diagnostic' => $row['diagnosticCode'] ?? null,
        ])->filter(fn (array $row): bool => filled($row['email']))->values()->all();
    }

    private function subtype(array $payload, string $type, array $recipient): ?string
    {
        return match ($type) {
            'bounce' => Str::lower((string) Arr::get($payload, 'bounce.bounceType', 'unknown')),
            'complaint' => Str::lower((string) Arr::get($payload, 'complaint.complaintFeedbackType', 'complaint')),
            'reject' => Str::lower((string) Arr::get($payload, 'reject.reason', 'rejected')),
            default => null,
        };
    }

    private function diagnostic(array $payload, string $type, array $recipient): string
    {
        return (string) ($recipient['diagnostic']
            ?? ($type === 'reject' ? Arr::get($payload, 'reject.reason') : null)
            ?? ($type === 'complaint' ? Arr::get($payload, 'complaint.complaintFeedbackType') : null)
            ?? $type);
    }

    private function occurredAt(array $payload, string $type): Carbon
    {
        $value = Arr::get($payload, $type.'.timestamp') ?? Arr::get($payload, 'mail.timestamp');

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return now();
        }
    }

    private function sanitizedMetadata(array $payload, string $type): array
    {
        return array_filter([
            'sending_account_id' => Arr::get($payload, 'mail.sendingAccountId'),
            'source' => Arr::get($payload, 'mail.source'),
            'bounce_type' => Arr::get($payload, 'bounce.bounceType'),
            'bounce_subtype' => Arr::get($payload, 'bounce.bounceSubType'),
            'complaint_feedback_type' => Arr::get($payload, 'complaint.complaintFeedbackType'),
            'reject_reason' => Arr::get($payload, 'reject.reason'),
            'event_type' => $type,
        ], fn ($value): bool => filled($value));
    }

    private function updateDelivery(?OfferJourneyMessageDelivery $delivery, string $type, string $diagnostic, string $providerMessageId): void
    {
        if (! $delivery) {
            return;
        }

        $attributes = ['provider_message_id' => $providerMessageId ?: $delivery->provider_message_id];
        if ($type === 'delivery') {
            $attributes += ['status' => 'delivered', 'delivered_at' => now()];
        } elseif ($type === 'bounce') {
            $attributes += ['status' => 'bounced', 'bounced_at' => now(), 'failure_reason' => Str::limit($diagnostic, 1000)];
        } elseif ($type === 'complaint') {
            $attributes += ['status' => 'complained', 'complained_at' => now(), 'failure_reason' => Str::limit($diagnostic, 1000)];
        } elseif ($type === 'reject') {
            $attributes += ['status' => 'rejected', 'rejected_at' => now(), 'failure_reason' => Str::limit($diagnostic, 1000)];
        }
        $delivery->update($attributes);
    }
}
