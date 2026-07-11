<?php

namespace App\Domain\OfferJourneys\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OfferJourneySnsVerifier
{
    public function verify(array $message): void
    {
        $required = ['Type', 'MessageId', 'TopicArn', 'Timestamp', 'SignatureVersion', 'Signature', 'SigningCertURL', 'Message'];
        foreach ($required as $field) {
            if (! isset($message[$field]) || ! is_string($message[$field]) || $message[$field] === '') {
                throw new RuntimeException('Message SNS incomplet.');
            }
        }

        $allowedTopics = array_values(array_filter(config('offer_journeys.deliverability.sns_topic_arns', [])));
        if ($allowedTopics === [] || ! in_array($message['TopicArn'], $allowedTopics, true)) {
            throw new RuntimeException('Topic SNS non autorise.');
        }

        try {
            $timestamp = Carbon::parse($message['Timestamp']);
        } catch (\Throwable) {
            throw new RuntimeException('Horodatage SNS invalide.');
        }

        $tolerance = max(60, (int) config('offer_journeys.deliverability.timestamp_tolerance_seconds', 3600));
        if (abs(now()->diffInSeconds($timestamp, false)) > $tolerance) {
            throw new RuntimeException('Message SNS expire.');
        }

        $version = $message['SignatureVersion'];
        $algorithm = match ($version) {
            '1' => OPENSSL_ALGO_SHA1,
            '2' => OPENSSL_ALGO_SHA256,
            default => throw new RuntimeException('Version de signature SNS non prise en charge.'),
        };

        $certificateUrl = $message['SigningCertURL'];
        $this->assertAmazonSnsUrl($certificateUrl, true);
        $certificate = Cache::remember(
            'offer-journeys:sns-cert:'.hash('sha256', $certificateUrl),
            now()->addHours(12),
            fn (): string => Http::timeout(5)->retry(2, 150)->get($certificateUrl)->throw()->body()
        );

        $signature = base64_decode($message['Signature'], true);
        if ($signature === false || openssl_verify($this->canonicalString($message), $signature, $certificate, $algorithm) !== 1) {
            throw new RuntimeException('Signature SNS invalide.');
        }
    }

    public function confirmSubscription(array $message): void
    {
        if (($message['Type'] ?? null) !== 'SubscriptionConfirmation'
            || ! (bool) config('offer_journeys.deliverability.auto_confirm_subscription', false)) {
            return;
        }

        $url = (string) ($message['SubscribeURL'] ?? '');
        $this->assertAmazonSnsUrl($url, false);
        Http::timeout(5)->retry(2, 150)->get($url)->throw();
    }

    private function canonicalString(array $message): string
    {
        $fields = match ($message['Type']) {
            'Notification' => ['Message', 'MessageId', 'Subject', 'Timestamp', 'TopicArn', 'Type'],
            'SubscriptionConfirmation', 'UnsubscribeConfirmation' => ['Message', 'MessageId', 'SubscribeURL', 'Timestamp', 'Token', 'TopicArn', 'Type'],
            default => throw new RuntimeException('Type de message SNS non pris en charge.'),
        };

        $canonical = '';
        foreach ($fields as $field) {
            if (array_key_exists($field, $message)) {
                if (! is_string($message[$field])) {
                    throw new RuntimeException('Champ SNS invalide.');
                }
                $canonical .= $field."\n".$message[$field]."\n";
            }
        }

        return $canonical;
    }

    private function assertAmazonSnsUrl(string $url, bool $certificate): void
    {
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = (string) ($parts['path'] ?? '');
        $validHost = preg_match('/^sns\.[a-z0-9-]+\.amazonaws\.com(\.cn)?$/', $host) === 1;

        if (($parts['scheme'] ?? null) !== 'https'
            || ! $validHost
            || isset($parts['user'], $parts['pass'])
            || isset($parts['port'])
            || ($certificate && (preg_match('#^/SimpleNotificationService-[A-Za-z0-9_-]+\.pem$#', $path) !== 1
                || isset($parts['query'], $parts['fragment'])))) {
            throw new RuntimeException('URL SNS non autorisee.');
        }
    }
}
