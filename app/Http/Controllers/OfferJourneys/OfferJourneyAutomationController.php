<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyAutomation;
use App\Domain\OfferJourneys\Models\OfferJourneyAutomationVersion;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneySuppression;
use App\Domain\OfferJourneys\Services\OfferJourneyAutomationBuilder;
use App\Domain\OfferJourneys\Services\OfferJourneyAutomationSimulator;
use App\Domain\OfferJourneys\Services\OfferJourneyMessagePreview;
use App\Domain\OfferJourneys\Services\OfferJourneyMessageTemplateLibrary;
use App\Http\Controllers\Controller;
use App\Support\OfferJourneys\OfferJourneyAccess;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class OfferJourneyAutomationController extends Controller
{
    use AuthorizesRequests;

    public function show(
        OfferJourney $journey,
        OfferJourneyAutomationBuilder $builder,
        OfferJourneyMessagePreview $messagePreview,
        OfferJourneyMessageTemplateLibrary $messageTemplateLibrary
    ): View
    {
        $this->authorize('update', $journey);
        $automation = $journey->automations()->with(['versions.nodes', 'publishedVersion.nodes'])->first()
            ?? $builder->createV1Draft($journey, request()->user())->load(['versions.nodes', 'publishedVersion.nodes']);
        $version = $automation->versions->firstWhere('status', 'draft')
            ?? $automation->publishedVersion;

        $tags = \App\Domain\OfferJourneys\Models\OfferJourneyTag::query()->where('user_id', request()->user()->id)->orderBy('name')->get();
        $sentThisMonth = \App\Domain\OfferJourneys\Models\OfferJourneyMessageDelivery::query()
            ->where('user_id', request()->user()->id)->where('category', 'marketing')
            ->whereNotNull('sent_at')->where('sent_at', '>=', now()->startOfMonth())->count();
        $messageUsage = [
            'sent' => $sentThisMonth,
            'limit' => config('offer_journeys.limits.monthly_marketing_emails', 2000),
            'remaining' => max(0, config('offer_journeys.limits.monthly_marketing_emails', 2000) - $sentThisMonth),
        ];
        $recentDeliveries = \App\Domain\OfferJourneys\Models\OfferJourneyMessageDelivery::query()
            ->where('offer_journey_id', $journey->id)->latest()->limit(10)->get();

        $messageToolsEnabled = (bool) config('offer_journeys.message_tools_enabled', false);
        $messagePreviews = collect();
        $messageTemplates = collect();
        $recipientEstimate = null;

        if ($messageToolsEnabled) {
            $messagePreviews = $version->nodes->where('type', 'email')->mapWithKeys(function ($node) use ($journey, $messagePreview) {
                $config = $node->config_json ?? [];

                return [$node->id => $messagePreview->render(
                    $journey,
                    request()->user(),
                    (string) ($config['subject'] ?? ''),
                    (string) ($config['body'] ?? '')
                )];
            });
            $messageTemplates = $messageTemplateLibrary->all();

            $frequencySince = now()->subHours(max(1, (int) config('offer_journeys.contact_frequency_hours', 72)));
            $recipientEstimate = OfferJourneyContact::query()
                ->where('user_id', request()->user()->id)
                ->whereHas('entries', fn ($query) => $query->where('offer_journey_id', $journey->id))
                ->whereHas('consents', fn ($query) => $query
                    ->where('purpose', 'marketing_follow_up')
                    ->where('status', 'granted')
                    ->whereNull('withdrawn_at'))
                ->whereDoesntHave('suppressions')
                ->whereNotIn('email_normalized', OfferJourneySuppression::query()
                    ->where('user_id', request()->user()->id)
                    ->select('email_normalized'))
                ->whereDoesntHave('messageDeliveries', fn ($query) => $query
                    ->where('category', 'marketing')
                    ->where('is_test', false)
                    ->whereNotNull('sent_at')
                    ->where('sent_at', '>=', $frequencySince))
                ->count();
        }

        return view('offer-journeys.practitioner.automation', compact(
            'journey',
            'automation',
            'version',
            'tags',
            'messageUsage',
            'recentDeliveries',
            'messageToolsEnabled',
            'messagePreviews',
            'messageTemplates',
            'recipientEstimate'
        ));
    }

    public function update(
        Request $request,
        OfferJourney $journey,
        OfferJourneyAutomation $automation,
        OfferJourneyAutomationBuilder $builder
    ): RedirectResponse {
        $this->authorizeAutomation($journey, $automation);
        $validated = $request->validate([
            'messages' => ['required', 'array', 'max:3'],
            'messages.*.subject' => ['required', 'string', 'max:180'],
            'messages.*.body' => ['required', 'string', 'max:6000'],
            'messages.*.delay_days' => ['required', 'integer', 'min:0', 'max:60'],
            'messages.*.is_enabled' => ['nullable', 'boolean'],
        ]);

        $version = $builder->editableVersion($automation);
        foreach ($version->nodes as $node) {
            $input = $validated['messages'][$node->node_key] ?? null;
            if (! $input) {
                continue;
            }
            $config = $node->config_json;
            $config['subject'] = $input['subject'];
            $config['body'] = $input['body'];
            $config['delay_minutes'] = ((int) $input['delay_days']) * 1440;
            $config['is_enabled'] = (bool) ($input['is_enabled'] ?? false);
            $node->update(['config_json' => $config]);
        }

        return back()->with('success', 'Le brouillon des messages a été enregistré.');
    }

    public function activate(
        Request $request,
        OfferJourney $journey,
        OfferJourneyAutomation $automation,
        OfferJourneyAutomationVersion $version,
        OfferJourneyAutomationBuilder $builder,
        OfferJourneyAccess $access
    ): RedirectResponse {
        $this->authorizeAutomation($journey, $automation);
        abort_unless((int) $version->offer_journey_automation_id === (int) $automation->id, 404);

        if (! $access->automationAvailableFor($request->user())) {
            return back()->withErrors(['automation' => 'Les automatisations ne sont pas activées pour ce pilote.']);
        }

        $builder->publish($automation, $version, $request->user());

        return back()->with('success', 'La séquence de suivi est active.');
    }

    public function pause(OfferJourney $journey, OfferJourneyAutomation $automation): RedirectResponse
    {
        $this->authorizeAutomation($journey, $automation);
        $automation->update(['status' => 'paused', 'paused_at' => now()]);

        return back()->with('success', 'La séquence est en pause. Aucun nouveau message ne sera envoyé.');
    }

    public function createDraft(OfferJourney $journey, OfferJourneyAutomation $automation, OfferJourneyAutomationBuilder $builder): RedirectResponse
    {
        $this->authorizeAutomation($journey, $automation);
        $builder->editableVersion($automation);

        return back()->with('success', 'Une nouvelle version brouillon est prête.');
    }

    public function updateSettings(Request $request, OfferJourney $journey, OfferJourneyAutomation $automation): RedirectResponse
    {
        $this->authorizeAutomation($journey, $automation);
        $validated = $request->validate([
            'reentry_mode' => ['required', Rule::in(['once', 'after_delay'])],
            'reentry_delay_days' => ['nullable', 'required_if:reentry_mode,after_delay', 'integer', 'min:1', 'max:365'],
            'quiet_hours_start' => ['required', 'date_format:H:i'],
            'quiet_hours_end' => ['required', 'date_format:H:i'],
        ]);
        $automation->update($validated);

        return back()->with('success', 'Les règles d’entrée et les heures silencieuses ont été enregistrées.');
    }

    public function simulate(Request $request, OfferJourney $journey, OfferJourneyAutomation $automation, OfferJourneyAutomationVersion $version, OfferJourneyAutomationSimulator $simulator): RedirectResponse
    {
        $this->authorizeAutomation($journey, $automation);
        abort_unless((int) $version->offer_journey_automation_id === (int) $automation->id, 404);
        $validated = $request->validate([
            'marketing_consent' => ['nullable', 'boolean'],
            'converted' => ['nullable', 'boolean'],
            'inactive_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer'],
        ]);
        $version->load('nodes');

        return back()->with('simulation', $simulator->simulate($version, [
            ...$validated,
            'marketing_consent' => $request->boolean('marketing_consent'),
            'converted' => $request->boolean('converted'),
        ]));
    }

    private function authorizeAutomation(OfferJourney $journey, OfferJourneyAutomation $automation): void
    {
        $this->authorize('update', $journey);
        abort_unless((int) $automation->offer_journey_id === (int) $journey->id, 404);
    }
}
