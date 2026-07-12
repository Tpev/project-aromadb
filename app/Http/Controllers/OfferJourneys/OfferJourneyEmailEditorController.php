<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyEmailAsset;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageCampaign;
use App\Domain\OfferJourneys\Models\OfferJourneySegment;
use App\Domain\OfferJourneys\Services\OfferJourneyCampaignAudience;
use App\Domain\OfferJourneys\Services\OfferJourneyEmailAssetStorage;
use App\Domain\OfferJourneys\Services\OfferJourneyEmailContent;
use App\Domain\OfferJourneys\Services\OfferJourneyEmailQuality;
use App\Domain\OfferJourneys\Services\OfferJourneyEmailRenderer;
use App\Domain\OfferJourneys\Services\OfferJourneyEmailTemplates;
use App\Http\Controllers\Controller;
use App\Support\OfferJourneys\OfferJourneyAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OfferJourneyEmailEditorController extends Controller
{
    public function start(Request $request, OfferJourneyEmailContent $content): RedirectResponse
    {
        $this->ensureEnabled($request);
        $campaign = OfferJourneyMessageCampaign::query()->create([
            'user_id' => $request->user()->id,
            'created_by_user_id' => $request->user()->id,
            'audience_type' => 'journeys',
            'name' => 'Nouvelle campagne',
            'subject' => '',
            'preheader' => '',
            'body' => '',
            'content_json' => $content->defaultContent(),
            'style_json' => $content->styleFor($request->user()),
            'editor_version' => OfferJourneyEmailContent::VERSION,
            'status' => 'draft',
        ]);

        return redirect()->route('offer-journeys.email-editor.edit', $campaign);
    }

    public function edit(
        Request $request,
        OfferJourneyMessageCampaign $campaign,
        OfferJourneyEmailTemplates $templates,
        OfferJourneyCampaignAudience $audience
    ): View {
        $this->ensureEnabled($request);
        $this->owned($request, $campaign);
        abort_unless(in_array($campaign->status, ['draft', 'scheduled'], true), 422);

        $campaign->load(['journeys:id,name', 'emailAssets']);
        $journeys = OfferJourney::query()->ownedBy($request->user())->published()->orderBy('name')->get(['id', 'name', 'slug']);
        $segments = collect();
        if (config('offer_journeys.segment_campaigns_enabled', false)) {
            $frequencySince = now()->subHours(max(1, (int) config('offer_journeys.contact_frequency_hours', 72)));
            $segments = OfferJourneySegment::query()->where('user_id', $request->user()->id)->where('is_active', true)->with('rules')->orderBy('name')->get();
            $segments->each(function (OfferJourneySegment $segment) use ($request, $audience, $frequencySince) {
                $segment->audience_summary = $audience->resolve(
                    $audience->queryForSegment((int) $request->user()->id, $segment),
                    (int) $request->user()->id,
                    $frequencySince
                )['summary'];
            });
        }

        return view('offer-journeys.practitioner.message-campaigns.editor', [
            'campaign' => $campaign,
            'journeys' => $journeys,
            'segments' => $segments,
            'templates' => $templates->all(),
            'locked' => $campaign->status !== 'draft',
        ]);
    }

    public function autosave(
        Request $request,
        OfferJourneyMessageCampaign $campaign,
        OfferJourneyEmailContent $contentService,
        OfferJourneyEmailRenderer $renderer,
        OfferJourneyEmailQuality $quality
    ): JsonResponse {
        $this->ensureEnabled($request);
        $this->ownedDraft($request, $campaign);
        $validated = $this->validateEditor($request);
        $validated['subject'] = $contentService->validateHeader($validated['subject'] ?? '', 'subject', 180);
        $validated['preheader'] = $contentService->validateHeader($validated['preheader'] ?? '', 'preheader', 255);
        $normalized = $contentService->validate($validated['content'], $validated['style'] ?? [], $request->user(), $campaign);

        DB::transaction(function () use ($campaign, $validated, $normalized, $renderer) {
            $campaign->fill([
                'name' => $validated['name'],
                'subject' => $validated['subject'] ?? '',
                'preheader' => $validated['preheader'] ?? null,
                'content_json' => $normalized['content'],
                'style_json' => $normalized['style'],
                'editor_version' => OfferJourneyEmailContent::VERSION,
                'audience_type' => $validated['audience_type'],
                'offer_journey_segment_id' => $validated['audience_type'] === 'segment' ? ($validated['segment_id'] ?? null) : null,
            ]);
            $campaign->body = $renderer->plainBody($campaign, $normalized['content']);
            $campaign->save();
            $campaign->journeys()->sync($validated['journey_ids'] ?? []);
        });

        $campaign->refresh();

        return response()->json([
            'saved_at' => now()->format('H:i:s'),
            'quality' => $quality->inspect($campaign, $normalized['content'], $normalized['style'], $campaign->preheader),
        ]);
    }

    public function preview(
        Request $request,
        OfferJourneyMessageCampaign $campaign,
        OfferJourneyEmailContent $contentService,
        OfferJourneyEmailRenderer $renderer
    ) {
        $this->ensureEnabled($request);
        $this->owned($request, $campaign);
        $validated = $request->validate([
            'subject' => ['nullable', 'string', 'max:180'],
            'preheader' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'array'],
            'style' => ['required', 'array'],
        ]);
        $validated['subject'] = $contentService->validateHeader($validated['subject'] ?? '', 'subject', 180);
        $validated['preheader'] = $contentService->validateHeader($validated['preheader'] ?? '', 'preheader', 255);
        $normalized = $contentService->validate($validated['content'], $validated['style'], $request->user(), $campaign);
        $draft = $campaign->replicate();
        $draft->id = $campaign->id;
        $draft->exists = true;
        $draft->setRelation('user', $request->user());
        $draft->subject = $validated['subject'] ?? '';
        $draft->preheader = $validated['preheader'] ?? null;

        $journey = $campaign->journeys()->first();
        $rendered = $renderer->render($draft, [
            'prenom' => 'Camille',
            'offre' => $journey?->name ?: 'votre offre',
            'nom_praticien' => $request->user()->company_name ?: $request->user()->name,
            'lien_offre' => $journey ? route('offer-journeys.public.show', ['therapist' => $request->user(), 'journeySlug' => $journey->slug]) : 'https://olithea.fr/votre-page',
        ], '#desinscription-apercu', 'marketing', $normalized['content'], $normalized['style'], $draft->preheader);

        return response($rendered['html'])->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function convert(Request $request, OfferJourneyMessageCampaign $campaign, OfferJourneyEmailContent $content): RedirectResponse
    {
        $this->ensureEnabled($request);
        $this->ownedDraft($request, $campaign);
        abort_unless($campaign->content_json === null, 422);
        $campaign->update([
            'content_json' => ['blocks' => [
                ['id' => (string) Str::uuid(), 'type' => 'paragraph', 'data' => ['text' => $campaign->body, 'align' => 'left']],
                ['id' => (string) Str::uuid(), 'type' => 'signature', 'data' => ['text' => '{{nom_praticien}}', 'show_contact' => true]],
            ]],
            'style_json' => $content->styleFor($request->user()),
            'editor_version' => OfferJourneyEmailContent::VERSION,
        ]);

        return redirect()->route('offer-journeys.email-editor.edit', $campaign)->with('success', 'Le texte original a été conservé et placé dans le nouvel éditeur.');
    }

    public function upload(Request $request, OfferJourneyMessageCampaign $campaign, OfferJourneyEmailAssetStorage $storage): JsonResponse
    {
        $this->ensureEnabled($request);
        $this->ownedDraft($request, $campaign);
        $validated = $request->validate(['image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000']]);
        $asset = $storage->store($validated['image'], $request->user(), $campaign);

        return response()->json($this->assetPayload($asset), 201);
    }

    public function destroyAsset(Request $request, OfferJourneyMessageCampaign $campaign, OfferJourneyEmailAsset $asset, OfferJourneyEmailAssetStorage $storage): JsonResponse
    {
        $this->ensureEnabled($request);
        $this->ownedDraft($request, $campaign);
        abort_unless((int) $asset->user_id === (int) $request->user()->id && (int) $asset->offer_journey_message_campaign_id === (int) $campaign->id, 404);
        $used = collect(data_get($campaign->content_json, 'blocks', []))->contains(fn ($block) => (int) data_get($block, 'data.asset_id') === (int) $asset->id);
        abort_if($used, 422, 'Retirez cette image du message avant de la supprimer.');
        $storage->delete($asset);

        return response()->json(['deleted' => true]);
    }

    private function validateEditor(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'subject' => ['nullable', 'string', 'max:180'],
            'preheader' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'array'],
            'style' => ['required', 'array'],
            'audience_type' => ['required', Rule::in(['journeys', 'segment'])],
            'journey_ids' => ['nullable', 'array', 'max:20'],
            'journey_ids.*' => [Rule::exists('offer_journeys', 'id')->where('user_id', $request->user()->id)->where('status', 'published')],
            'segment_id' => ['nullable', Rule::exists('offer_journey_segments', 'id')->where('user_id', $request->user()->id)->where('is_active', true)],
        ]);
        if ($validated['audience_type'] === 'segment') {
            abort_unless(config('offer_journeys.segment_campaigns_enabled', false), 404);
            if (empty($validated['segment_id'])) {
                throw \Illuminate\Validation\ValidationException::withMessages(['segment_id' => 'Choisissez un segment.']);
            }
            if (count($validated['journey_ids'] ?? []) > 1) {
                throw \Illuminate\Validation\ValidationException::withMessages(['journey_ids' => 'Choisissez au maximum une page à promouvoir.']);
            }
        }

        return $validated;
    }

    private function assetPayload(OfferJourneyEmailAsset $asset): array
    {
        return [
            'id' => $asset->id,
            'url' => Storage::disk('public')->url($asset->path),
            'name' => $asset->original_name,
            'width' => $asset->width,
            'height' => $asset->height,
        ];
    }

    private function owned(Request $request, OfferJourneyMessageCampaign $campaign): void
    {
        abort_unless((int) $campaign->user_id === (int) $request->user()->id, 404);
    }

    private function ownedDraft(Request $request, OfferJourneyMessageCampaign $campaign): void
    {
        $this->owned($request, $campaign);
        abort_unless($campaign->status === 'draft', 422);
    }

    private function ensureEnabled(Request $request): void
    {
        abort_unless(
            config('offer_journeys.email_editor_enabled', false)
                && config('offer_journeys.campaigns_enabled', false)
                && app(OfferJourneyAccess::class)->canPublish($request->user()),
            404
        );
    }
}
