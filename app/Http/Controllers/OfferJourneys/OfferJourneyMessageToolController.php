<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyAutomation;
use App\Domain\OfferJourneys\Models\OfferJourneyAutomationNode;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageDelivery;
use App\Domain\OfferJourneys\Services\OfferJourneyMessagePreview;
use App\Http\Controllers\Controller;
use App\Mail\OfferJourneyMessageMail;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class OfferJourneyMessageToolController extends Controller
{
    use AuthorizesRequests;

    public function preview(Request $request, OfferJourney $journey, OfferJourneyAutomation $automation, OfferJourneyAutomationNode $node, OfferJourneyMessagePreview $preview): JsonResponse
    {
        $this->authorizeNode($request, $journey, $automation, $node);
        $data = $request->validate(['subject' => ['nullable', 'string', 'max:180'], 'body' => ['nullable', 'string', 'max:6000']]);

        return response()->json($preview->render($journey, $request->user(), (string) ($data['subject'] ?? ''), (string) ($data['body'] ?? '')));
    }

    public function sendTest(Request $request, OfferJourney $journey, OfferJourneyAutomation $automation, OfferJourneyAutomationNode $node, OfferJourneyMessagePreview $preview): JsonResponse
    {
        $this->authorizeNode($request, $journey, $automation, $node);
        $data = $request->validate(['subject' => ['required', 'string', 'max:180'], 'body' => ['required', 'string', 'max:6000']]);
        $rendered = $preview->render($journey, $request->user(), $data['subject'], $data['body']);
        if ($rendered['warnings'] !== []) {
            return response()->json(['message' => 'Corrigez les avertissements avant l’envoi du test.', 'warnings' => $rendered['warnings']], 422);
        }

        $delivery = OfferJourneyMessageDelivery::query()->create([
            'user_id' => $request->user()->id,
            'offer_journey_id' => $journey->id,
            'node_key' => $node->node_key,
            'category' => 'test',
            'status' => 'sending',
            'recipient_email' => $request->user()->email,
            'subject' => '[TEST] '.$rendered['subject'],
            'idempotency_key' => 'oj:test:'.$request->user()->id.':'.Str::uuid(),
            'is_test' => true,
            'metadata' => ['source' => 'practitioner_message_preview'],
        ]);

        try {
            Mail::to($request->user()->email)->send(new OfferJourneyMessageMail(
                $request->user(),
                '[TEST] '.$rendered['subject'],
                "MESSAGE TEST - aucune personne de votre liste n’a été contactée.\n\n".$rendered['body'],
                route('offer-journeys.automation', $journey),
                'test',
                $delivery->id
            ));
            $delivery->update(['status' => 'sent', 'sent_at' => now()]);

            return response()->json(['message' => 'Message test envoyé à '.$request->user()->email.'.']);
        } catch (Throwable $exception) {
            $delivery->update(['status' => 'failed', 'failed_at' => now(), 'failure_reason' => $exception->getMessage()]);
            throw $exception;
        }
    }

    private function authorizeNode(Request $request, OfferJourney $journey, OfferJourneyAutomation $automation, OfferJourneyAutomationNode $node): void
    {
        abort_unless((bool) config('offer_journeys.message_tools_enabled', false), 404);
        $this->authorize('update', $journey);
        abort_unless((int) $automation->offer_journey_id === (int) $journey->id, 404);
        abort_unless((int) $node->version?->offer_journey_automation_id === (int) $automation->id, 404);
        abort_unless($node->type === 'email', 404);
    }
}
