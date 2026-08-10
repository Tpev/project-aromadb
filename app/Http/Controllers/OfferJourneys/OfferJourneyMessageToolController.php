<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyAutomation;
use App\Domain\OfferJourneys\Models\OfferJourneyAutomationNode;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageDelivery;
use App\Domain\OfferJourneys\Services\OfferJourneyMessagePreview;
use App\Domain\OfferJourneys\Services\OfferJourneyAutomationEmailComposer;
use App\Domain\OfferJourneys\Services\OfferJourneyEmailContent;
use App\Domain\OfferJourneys\Services\OfferJourneyEmailRenderer;
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

    public function preview(
        Request $request,
        OfferJourney $journey,
        OfferJourneyAutomation $automation,
        OfferJourneyAutomationNode $node,
        OfferJourneyMessagePreview $preview,
        OfferJourneyAutomationEmailComposer $composer,
        OfferJourneyEmailRenderer $renderer
    ): JsonResponse
    {
        $this->authorizeNode($request, $journey, $automation, $node);
        $data = $request->validate($this->rules(false));
        $result = $preview->render($journey, $request->user(), (string) ($data['subject'] ?? ''), (string) ($data['body'] ?? ''));

        if (($data['editor_version'] ?? null) === OfferJourneyEmailContent::VERSION && filled($data['body'] ?? null)) {
            $portable = $composer->compose($data, $node->config_json ?? [], $request->user());
            $rendered = $renderer->renderPortable(
                $request->user(),
                $result['subject'],
                $journey->name,
                $portable['content'],
                $portable['style'],
                $this->previewVariables($journey, $request),
                route('offer-journeys.automation', $journey),
                'test',
                $portable['preheader']
            );
            $result['email_html'] = $rendered['html'];
            $result['email_text'] = $rendered['text'];
        }

        return response()->json($result);
    }

    public function sendTest(
        Request $request,
        OfferJourney $journey,
        OfferJourneyAutomation $automation,
        OfferJourneyAutomationNode $node,
        OfferJourneyMessagePreview $preview,
        OfferJourneyAutomationEmailComposer $composer
    ): JsonResponse
    {
        $this->authorizeNode($request, $journey, $automation, $node);
        $data = $request->validate($this->rules(true));
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
            $portable = ($data['editor_version'] ?? null) === OfferJourneyEmailContent::VERSION
                ? $composer->compose($data, $node->config_json ?? [], $request->user())
                : ['content' => [], 'style' => [], 'preheader' => null];
            Mail::to($request->user()->email)->send(new OfferJourneyMessageMail(
                therapist: $request->user(),
                messageSubject: '[TEST] '.$rendered['subject'],
                messageBody: "MESSAGE TEST - aucune personne de votre liste n’a été contactée.\n\n".$rendered['body'],
                unsubscribeUrl: route('offer-journeys.automation', $journey),
                category: 'test',
                deliveryId: $delivery->id,
                renderVariables: $this->previewVariables($journey, $request),
                portableContent: $portable['content'],
                portableStyle: $portable['style'],
                preheader: $portable['preheader'],
                offerName: $journey->name
            ));
            $delivery->update(['status' => 'sent', 'sent_at' => now()]);

            return response()->json(['message' => 'Message test envoyé à '.$request->user()->email.'.']);
        } catch (Throwable $exception) {
            $delivery->update(['status' => 'failed', 'failed_at' => now(), 'failure_reason' => $exception->getMessage()]);
            throw $exception;
        }
    }

    private function rules(bool $required): array
    {
        $presence = $required ? 'required' : 'nullable';

        return [
            'subject' => [$presence, 'string', 'max:180'],
            'body' => [$presence, 'string', 'max:6000'],
            'editor_version' => ['nullable', 'in:'.OfferJourneyEmailContent::VERSION],
            'preheader' => ['nullable', 'string', 'max:180'],
            'heading' => ['nullable', 'string', 'max:180'],
            'image_url' => ['nullable', 'url:http,https', 'max:2000'],
            'image_alt' => ['nullable', 'string', 'max:180', 'required_with:image_url'],
            'button_label' => ['nullable', 'string', 'max:80', 'required_with:button_url'],
            'button_url' => ['nullable', 'string', 'max:2000', 'required_with:button_label'],
            'details_title' => ['nullable', 'string', 'max:120'],
            'details_text' => ['nullable', 'string', 'max:1500'],
            'signature' => ['nullable', 'string', 'max:500'],
            'primary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ];
    }

    private function previewVariables(OfferJourney $journey, Request $request): array
    {
        return [
            'prenom' => 'Camille',
            'offre' => $journey->name,
            'nom_praticien' => $request->user()->company_name ?: $request->user()->name,
            'lien_offre' => route('offer-journeys.public.show', ['therapist' => $request->user(), 'journeySlug' => $journey->slug]),
            'lien_ressource' => 'https://olithea.fr/exemple-ressource',
        ];
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
