<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageCampaign;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageDelivery;
use App\Domain\OfferJourneys\Models\OfferJourneySegment;
use App\Domain\OfferJourneys\Services\OfferJourneyCampaignAudience;
use App\Domain\OfferJourneys\Services\OfferJourneyEmailQuality;
use App\Http\Controllers\Controller;
use App\Mail\OfferJourneyMessageMail;
use App\Support\OfferJourneys\OfferJourneyAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class OfferJourneyMessageCampaignController extends Controller
{
    public function index(Request $request, OfferJourneyCampaignAudience $audience): View
    {
        $this->ensureEnabled();
        $campaigns = OfferJourneyMessageCampaign::query()
            ->where('user_id', $request->user()->id)
            ->with(['journeys:id,name', 'segment:id,name'])
            ->withCount([
                'deliveries as failed_deliveries_count' => fn ($query) => $query->where('status', 'failed'),
                'deliveries as bounced_deliveries_count' => fn ($query) => $query->whereIn('status', ['bounced', 'rejected']),
                'deliveries as complained_deliveries_count' => fn ($query) => $query->where('status', 'complained'),
            ])
            ->orderByDesc('created_at')
            ->paginate(20);
        $journeys = OfferJourney::query()->ownedBy($request->user())->published()->orderBy('name')->get(['id', 'name']);
        $week = OfferJourneyMessageCampaign::query()->where('user_id', $request->user()->id)
            ->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()])->orderBy('scheduled_at')->get();
        $segments = collect();
        $editCampaign = null;

        if ($request->filled('edit')) {
            $editCampaign = OfferJourneyMessageCampaign::query()
                ->where('user_id', $request->user()->id)
                ->where('status', 'draft')
                ->with(['journeys:id,name', 'segment:id,name'])
                ->findOrFail((int) $request->input('edit'));
        }

        if (config('offer_journeys.segment_campaigns_enabled', false)) {
            $frequencySince = now()->subHours(max(1, (int) config('offer_journeys.contact_frequency_hours', 72)));
            $segments = OfferJourneySegment::query()
                ->where('user_id', $request->user()->id)
                ->where('is_active', true)
                ->with('rules')
                ->orderBy('name')
                ->get();
            $segments->each(function (OfferJourneySegment $segment) use ($request, $audience, $frequencySince) {
                $segment->audience_summary = $audience->resolve(
                    $audience->queryForSegment((int) $request->user()->id, $segment),
                    (int) $request->user()->id,
                    $frequencySince
                )['summary'];
            });
        }

        return view('offer-journeys.practitioner.message-campaigns.index', compact('campaigns', 'journeys', 'segments', 'week', 'editCampaign'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureEnabled();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'subject' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:6000'],
            'action' => ['required', Rule::in(['draft', 'schedule', 'send_now'])],
            'audience_type' => ['required', Rule::in(['journeys', 'segment'])],
            'scheduled_at' => ['nullable', 'date', 'after:now', 'before:'.now()->addYear()->toDateTimeString()],
            'journey_ids' => ['nullable', 'array', 'max:20'],
            'journey_ids.*' => [Rule::exists('offer_journeys', 'id')->where('user_id', $request->user()->id)->where('status', 'published')],
            'segment_id' => [
                'nullable',
                Rule::exists('offer_journey_segments', 'id')->where('user_id', $request->user()->id)->where('is_active', true),
            ],
        ]);

        if ($validated['audience_type'] === 'segment') {
            abort_unless(config('offer_journeys.segment_campaigns_enabled', false), 404);
            if (empty($validated['segment_id'])) {
                return back()->withErrors(['segment_id' => 'Choisissez un segment.'])->withInput();
            }
            if (count($validated['journey_ids'] ?? []) > 1) {
                return back()->withErrors(['journey_ids' => 'Choisissez au maximum une page à promouvoir pour cette campagne.'])->withInput();
            }
        } elseif (empty($validated['journey_ids'])) {
            return back()->withErrors(['journey_ids' => 'Choisissez au moins un parcours.'])->withInput();
        }

        if ($validated['action'] === 'schedule' && empty($validated['scheduled_at'])) {
            return back()->withErrors(['scheduled_at' => 'Choisissez une date et une heure.'])->withInput();
        }

        $status = $validated['action'] === 'draft' ? 'draft' : 'scheduled';
        $scheduledAt = match ($validated['action']) {
            'draft' => null,
            'send_now' => now(),
            default => $validated['scheduled_at'],
        };
        $campaign = OfferJourneyMessageCampaign::query()->create([
            'user_id' => $request->user()->id,
            'created_by_user_id' => $request->user()->id,
            'audience_type' => $validated['audience_type'],
            'offer_journey_segment_id' => $validated['audience_type'] === 'segment' ? $validated['segment_id'] : null,
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'status' => $status,
            'scheduled_at' => $scheduledAt,
        ]);
        $campaign->journeys()->sync($validated['journey_ids'] ?? []);

        return back()->with('success', $status === 'draft'
            ? 'Le brouillon a été enregistré. Aucun message ne sera envoyé.'
            : 'La campagne est prête. Les consentements et exclusions seront revérifiés au moment de l’envoi.');
    }

    public function estimate(Request $request, OfferJourneyCampaignAudience $audience): JsonResponse
    {
        $this->ensureEnabled();
        $validated = $request->validate([
            'audience_type' => ['required', Rule::in(['journeys', 'segment'])],
            'journey_ids' => ['nullable', 'array', 'max:20'],
            'journey_ids.*' => [Rule::exists('offer_journeys', 'id')->where('user_id', $request->user()->id)->where('status', 'published')],
            'segment_id' => ['nullable', Rule::exists('offer_journey_segments', 'id')->where('user_id', $request->user()->id)->where('is_active', true)],
        ]);

        if ($validated['audience_type'] === 'segment') {
            abort_unless(config('offer_journeys.segment_campaigns_enabled', false), 404);
            $segment = OfferJourneySegment::query()->where('user_id', $request->user()->id)->with('rules')->findOrFail((int) ($validated['segment_id'] ?? 0));
            $query = $audience->queryForSegment((int) $request->user()->id, $segment);
        } else {
            $query = $audience->queryForJourneys((int) $request->user()->id, $validated['journey_ids'] ?? []);
        }

        $frequencySince = now()->subHours(max(1, (int) config('offer_journeys.contact_frequency_hours', 72)));

        return response()->json($audience->resolve($query, (int) $request->user()->id, $frequencySince)['summary']);
    }

    public function schedule(Request $request, OfferJourneyMessageCampaign $campaign, OfferJourneyEmailQuality $quality): RedirectResponse
    {
        $this->ensureEnabled();
        $this->ownedCampaign($request, $campaign);
        abort_unless($campaign->status === 'draft', 422);
        $validated = $request->validate([
            'scheduled_at' => ['required', 'date', 'after:now', 'before:'.now()->addYear()->toDateTimeString()],
        ]);
        $this->assertReady($campaign, $quality);
        $campaign->update(['status' => 'scheduled', 'scheduled_at' => $validated['scheduled_at']]);

        return back()->with('success', 'La campagne est programmée.');
    }

    public function update(Request $request, OfferJourneyMessageCampaign $campaign): RedirectResponse
    {
        $this->ensureEnabled();
        $this->ownedCampaign($request, $campaign);
        abort_unless($campaign->status === 'draft', 422);
        abort_if($campaign->content_json !== null, 422, 'Utilisez l’éditeur visuel pour modifier cette campagne.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'subject' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:6000'],
            'audience_type' => ['required', Rule::in(['journeys', 'segment'])],
            'journey_ids' => ['nullable', 'array', 'max:20'],
            'journey_ids.*' => [Rule::exists('offer_journeys', 'id')->where('user_id', $request->user()->id)->where('status', 'published')],
            'segment_id' => ['nullable', Rule::exists('offer_journey_segments', 'id')->where('user_id', $request->user()->id)->where('is_active', true)],
        ]);
        if ($validated['audience_type'] === 'segment') {
            abort_unless(config('offer_journeys.segment_campaigns_enabled', false), 404);
            if (empty($validated['segment_id'])) {
                return back()->withErrors(['segment_id' => 'Choisissez un segment.'])->withInput();
            }
            if (count($validated['journey_ids'] ?? []) > 1) {
                return back()->withErrors(['journey_ids' => 'Choisissez au maximum une page à promouvoir.'])->withInput();
            }
        } elseif (empty($validated['journey_ids'])) {
            return back()->withErrors(['journey_ids' => 'Choisissez au moins un parcours.'])->withInput();
        }

        $campaign->update([
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'audience_type' => $validated['audience_type'],
            'offer_journey_segment_id' => $validated['audience_type'] === 'segment' ? $validated['segment_id'] : null,
        ]);
        $campaign->journeys()->sync($validated['journey_ids'] ?? []);

        return redirect()->route('offer-journeys.message-campaigns.index')->with('success', 'Le brouillon a été mis à jour.');
    }

    public function sendTest(Request $request, OfferJourneyMessageCampaign $campaign): RedirectResponse
    {
        $this->ensureEnabled();
        $this->ownedCampaign($request, $campaign);
        abort_unless(in_array($campaign->status, ['draft', 'scheduled'], true), 422);
        abort_if($campaign->content_json && ! config('offer_journeys.email_editor_enabled', false), 404);

        $journey = $campaign->journeys()->first();
        $variables = $this->sampleVariables($request, $campaign, $journey);
        $subject = $this->sample($campaign->subject, $request, $campaign, $journey);
        $body = $this->sample($campaign->body, $request, $campaign, $journey);
        $delivery = OfferJourneyMessageDelivery::query()->create([
            'user_id' => $request->user()->id,
            'offer_journey_id' => $journey?->id,
            'offer_journey_message_campaign_id' => $campaign->id,
            'node_key' => 'campaign_test_'.$campaign->id,
            'category' => 'marketing',
            'status' => 'sending',
            'recipient_email' => $request->user()->email,
            'subject' => $subject,
            'idempotency_key' => 'oj:campaign-test:'.$campaign->id.':'.Str::uuid(),
            'is_test' => true,
            'metadata' => ['campaign_id' => $campaign->id],
        ]);

        try {
            Mail::to($request->user()->email)->send(new OfferJourneyMessageMail(
                $request->user(),
                '[TEST] '.$subject,
                $body,
                route('offer-journeys.message-campaigns.index'),
                'marketing',
                $delivery->id,
                $campaign,
                $variables
            ));
            $delivery->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (Throwable $exception) {
            $delivery->update(['status' => 'failed', 'failed_at' => now(), 'failure_reason' => Str::limit($exception->getMessage(), 255)]);

            return back()->withErrors(['test_email' => 'Le message test n’a pas pu être envoyé. Vérifiez la configuration email puis réessayez.']);
        }

        return back()->with('success', 'Le message test a été envoyé uniquement à votre adresse.');
    }

    public function cancel(Request $request, OfferJourneyMessageCampaign $campaign): RedirectResponse
    {
        $this->ensureEnabled();
        $this->ownedCampaign($request, $campaign);
        abort_unless(in_array($campaign->status, ['draft', 'scheduled'], true), 422);
        $campaign->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        return back()->with('success', 'La campagne a été annulée.');
    }

    public function sendNow(Request $request, OfferJourneyMessageCampaign $campaign, OfferJourneyEmailQuality $quality): RedirectResponse
    {
        $this->ensureEnabled();
        $this->ownedCampaign($request, $campaign);
        abort_unless($campaign->status === 'draft', 422);
        $this->assertReady($campaign, $quality);
        $campaign->update(['status' => 'scheduled', 'scheduled_at' => now()]);

        return back()->with('success', 'La campagne sera envoyée dès que possible. Les destinataires seront revérifiés avant l’envoi.');
    }

    public function returnToDraft(Request $request, OfferJourneyMessageCampaign $campaign): RedirectResponse
    {
        $this->ensureEnabled();
        $this->ownedCampaign($request, $campaign);
        abort_unless($campaign->status === 'scheduled', 422);
        $campaign->update(['status' => 'draft', 'scheduled_at' => null, 'processing_started_at' => null]);

        $route = $campaign->content_json
            ? route('offer-journeys.email-editor.edit', $campaign)
            : route('offer-journeys.message-campaigns.index', ['edit' => $campaign->id]);

        return redirect($route)->with('success', 'La campagne est repassée en brouillon et peut être modifiée.');
    }

    private function sample(string $text, Request $request, OfferJourneyMessageCampaign $campaign, ?OfferJourney $journey): string
    {
        return strtr($text, collect($this->sampleVariables($request, $campaign, $journey))
            ->mapWithKeys(fn ($value, $key) => ['{{'.$key.'}}' => $value])->all());
    }

    private function sampleVariables(Request $request, OfferJourneyMessageCampaign $campaign, ?OfferJourney $journey): array
    {
        return [
            'prenom' => 'Camille',
            'offre' => $journey?->name ?: $campaign->name,
            'nom_praticien' => $request->user()->company_name ?: $request->user()->name,
            'lien_offre' => $journey
                ? route('offer-journeys.public.show', ['therapist' => $request->user(), 'journeySlug' => $journey->slug])
                : '',
        ];
    }

    private function assertReady(OfferJourneyMessageCampaign $campaign, OfferJourneyEmailQuality $quality): void
    {
        $campaign->loadMissing('journeys');
        $errors = [];
        if (trim((string) $campaign->subject) === '') {
            $errors[] = 'Ajoutez un objet avant l’envoi.';
        }
        if ($campaign->audience_type === 'segment' && ! $campaign->offer_journey_segment_id) {
            $errors[] = 'Choisissez un segment.';
        }
        if ($campaign->audience_type !== 'segment' && $campaign->journeys->isEmpty()) {
            $errors[] = 'Choisissez au moins une page pour définir les destinataires.';
        }
        if ($campaign->content_json) {
            if (! config('offer_journeys.email_editor_enabled', false)) {
                $errors[] = 'L’éditeur visuel est actuellement désactivé.';
            }
            $errors = [...$errors, ...$quality->inspect(
                $campaign,
                $campaign->content_json,
                $campaign->style_json ?? [],
                $campaign->preheader
            )['errors']];
        } elseif (trim((string) $campaign->body) === '') {
            $errors[] = 'Rédigez le message avant l’envoi.';
        }
        if ($errors) {
            throw ValidationException::withMessages(['campaign' => array_values(array_unique($errors))]);
        }
    }

    private function ownedCampaign(Request $request, OfferJourneyMessageCampaign $campaign): void
    {
        abort_unless((int) $campaign->user_id === (int) $request->user()->id, 404);
    }

    private function ensureEnabled(): void
    {
        abort_unless(
            config('offer_journeys.campaigns_enabled', false)
                && app(OfferJourneyAccess::class)->canPublish(request()->user()),
            404
        );
    }
}
