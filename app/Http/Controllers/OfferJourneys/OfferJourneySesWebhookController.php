<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Services\OfferJourneySnsVerifier;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessOfferJourneySesEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OfferJourneySesWebhookController extends Controller
{
    public function __invoke(Request $request, OfferJourneySnsVerifier $verifier): JsonResponse
    {
        abort_unless((bool) config('offer_journeys.deliverability.enabled', false), 404);

        $raw = $request->getContent();
        if ($raw === '' || strlen($raw) > 262144) {
            return response()->json(['message' => 'Requete invalide.'], 400);
        }

        $message = json_decode($raw, true);
        if (! is_array($message)) {
            return response()->json(['message' => 'JSON invalide.'], 400);
        }

        try {
            $verifier->verify($message);
            if (($message['Type'] ?? null) === 'SubscriptionConfirmation') {
                $verifier->confirmSubscription($message);

                return response()->json(['accepted' => true]);
            }

            abort_unless(($message['Type'] ?? null) === 'Notification', 400);
            ProcessOfferJourneySesEvent::dispatch($message);

            return response()->json(['accepted' => true], 202);
        } catch (RuntimeException $exception) {
            Log::warning('Offer journey SNS webhook rejected.', [
                'message_id' => $message['MessageId'] ?? null,
                'reason' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Signature ou origine invalide.'], 403);
        }
    }
}
