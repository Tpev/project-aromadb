<?php

use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyDeliverabilityEvent;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageDelivery;
use App\Domain\OfferJourneys\Models\OfferJourneySenderControl;
use App\Domain\OfferJourneys\Models\OfferJourneySuppression;
use App\Domain\OfferJourneys\Services\OfferJourneyDeliverabilityEventIngestor;
use App\Domain\OfferJourneys\Services\OfferJourneySendingPolicy;
use App\Jobs\ProcessOfferJourneySesEvent;
use App\Models\NewsletterOptOut;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Carbon::setTestNow('2026-07-11 10:00:00');
    config()->set('offer_journeys.deliverability.enabled', true);
    config()->set('offer_journeys.deliverability.sns_topic_arns', [
        'arn:aws:sns:eu-north-1:123456789012:olithea-ses-events',
    ]);
    Cache::flush();
});

afterEach(fn () => Carbon::setTestNow());

function signedOfferJourneySnsMessage(array $overrides = []): array
{
    $certificateUrl = 'https://sns.eu-north-1.amazonaws.com/SimpleNotificationService-test.pem';
    $opensslOptions = ['private_key_bits' => 2048, 'digest_alg' => 'sha256'];
    $windowsConfig = dirname(PHP_BINARY).'/extras/ssl/openssl.cnf';
    if (is_file($windowsConfig)) {
        $opensslOptions['config'] = $windowsConfig;
    }
    $key = openssl_pkey_new($opensslOptions);
    $csr = openssl_csr_new([
        'countryName' => 'FR',
        'organizationName' => 'Olithea test',
        'commonName' => 'sns.eu-north-1.amazonaws.com',
    ], $key, $opensslOptions);
    $certificateResource = openssl_csr_sign($csr, null, $key, 1, $opensslOptions);
    openssl_x509_export($certificateResource, $certificate);

    $message = array_merge([
        'Type' => 'Notification',
        'MessageId' => 'sns-message-1',
        'TopicArn' => 'arn:aws:sns:eu-north-1:123456789012:olithea-ses-events',
        'Timestamp' => now()->toIso8601String(),
        'SignatureVersion' => '1',
        'SigningCertURL' => $certificateUrl,
        'Message' => json_encode(['notificationType' => 'Delivery']),
    ], $overrides);

    $canonical = '';
    foreach (['Message', 'MessageId', 'Subject', 'Timestamp', 'TopicArn', 'Type'] as $field) {
        if (array_key_exists($field, $message)) {
            $canonical .= $field."\n".$message[$field]."\n";
        }
    }
    openssl_sign($canonical, $signature, $key, OPENSSL_ALGO_SHA1);
    $message['Signature'] = base64_encode($signature);
    Http::fake([$certificateUrl => Http::response($certificate)]);

    return $message;
}

it('accepts only signed SNS notifications from an allowlisted topic', function () {
    Queue::fake();
    $message = signedOfferJourneySnsMessage();

    $this->postJson(route('api.offer-journeys.ses-webhook'), $message)
        ->assertAccepted()
        ->assertJson(['accepted' => true]);

    Queue::assertPushed(ProcessOfferJourneySesEvent::class, 1);
});

it('rejects a signed notification from another topic', function () {
    Queue::fake();
    $message = signedOfferJourneySnsMessage([
        'TopicArn' => 'arn:aws:sns:eu-north-1:123456789012:untrusted',
    ]);

    $this->postJson(route('api.offer-journeys.ses-webhook'), $message)->assertForbidden();
    Queue::assertNothingPushed();
});

it('keeps the SES webhook unavailable until explicitly enabled', function () {
    config()->set('offer_journeys.deliverability.enabled', false);

    $this->postJson(route('api.offer-journeys.ses-webhook'), [])->assertNotFound();
});

it('ingests a permanent bounce idempotently and suppresses future messages', function () {
    $user = User::factory()->create(['is_therapist' => true]);
    $contact = OfferJourneyContact::query()->create([
        'user_id' => $user->id,
        'email' => 'nadine@example.test',
        'email_normalized' => 'nadine@example.test',
    ]);
    $delivery = OfferJourneyMessageDelivery::query()->create([
        'user_id' => $user->id,
        'offer_journey_contact_id' => $contact->id,
        'category' => 'marketing',
        'status' => 'sent',
        'recipient_email' => 'nadine@example.test',
        'subject' => 'Votre guide',
        'idempotency_key' => 'delivery-bounce-test',
        'sent_at' => now()->subMinute(),
    ]);
    $payload = [
        'notificationType' => 'Bounce',
        'mail' => [
            'messageId' => 'ses-message-1',
            'timestamp' => now()->toIso8601String(),
            'destination' => ['nadine@example.test'],
            'tags' => ['offer_delivery_id' => [(string) $delivery->id]],
        ],
        'bounce' => [
            'bounceType' => 'Permanent',
            'bounceSubType' => 'General',
            'timestamp' => now()->toIso8601String(),
            'bouncedRecipients' => [[
                'emailAddress' => 'nadine@example.test',
                'diagnosticCode' => 'smtp; 550 mailbox unavailable',
            ]],
        ],
    ];
    $sns = [
        'TopicArn' => config('offer_journeys.deliverability.sns_topic_arns.0'),
        'MessageId' => 'sns-bounce-1',
        'Message' => json_encode($payload),
    ];

    expect(app(OfferJourneyDeliverabilityEventIngestor::class)->ingest($sns))->toBe(1)
        ->and(app(OfferJourneyDeliverabilityEventIngestor::class)->ingest($sns))->toBe(0)
        ->and(OfferJourneyDeliverabilityEvent::query()->count())->toBe(1)
        ->and($delivery->fresh()->status)->toBe('bounced')
        ->and(OfferJourneySuppression::query()->where('email_normalized', 'nadine@example.test')->exists())->toBeTrue()
        ->and(NewsletterOptOut::query()->where('email', 'nadine@example.test')->exists())->toBeTrue();
});

it('records a transient bounce without permanently suppressing the recipient', function () {
    $user = User::factory()->create();
    $delivery = OfferJourneyMessageDelivery::query()->create([
        'user_id' => $user->id,
        'category' => 'transactional',
        'status' => 'sent',
        'recipient_email' => 'temp@example.test',
        'subject' => 'Votre demande',
        'idempotency_key' => 'delivery-transient-test',
        'sent_at' => now()->subMinute(),
    ]);
    $sns = [
        'TopicArn' => config('offer_journeys.deliverability.sns_topic_arns.0'),
        'MessageId' => 'sns-transient-1',
        'Message' => json_encode([
            'notificationType' => 'Bounce',
            'mail' => ['messageId' => 'ses-temp', 'tags' => ['offer_delivery_id' => [(string) $delivery->id]]],
            'bounce' => [
                'bounceType' => 'Transient',
                'timestamp' => now()->toIso8601String(),
                'bouncedRecipients' => [['emailAddress' => 'temp@example.test']],
            ],
        ]),
    ];

    app(OfferJourneyDeliverabilityEventIngestor::class)->ingest($sns);

    expect(OfferJourneyDeliverabilityEvent::query()->first()->event_subtype)->toBe('transient')
        ->and(OfferJourneySuppression::query()->count())->toBe(0);
});

it('enforces per-practitioner pauses and progressive account limits', function () {
    $user = User::factory()->create(['created_at' => now()->subDays(3)]);
    config()->set('offer_journeys.deliverability.progressive_monthly_limits', [
        ['minimum_account_age_days' => 0, 'limit' => 5],
        ['minimum_account_age_days' => 60, 'limit' => 100],
    ]);

    expect(app(OfferJourneySendingPolicy::class)->monthlyLimit($user))->toBe(5);

    OfferJourneySenderControl::query()->create([
        'user_id' => $user->id,
        'marketing_paused' => true,
        'pause_reason' => 'Verification support',
        'paused_at' => now(),
    ]);

    expect(app(OfferJourneySendingPolicy::class)->blockingReason($user, 'marketing'))->toBe('marketing_paused')
        ->and(app(OfferJourneySendingPolicy::class)->blockingReason($user, 'transactional'))->toBeNull();
});
