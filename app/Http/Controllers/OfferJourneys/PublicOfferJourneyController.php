<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyPage;
use App\Domain\OfferJourneys\Models\OfferJourneySlugRedirect;
use App\Domain\OfferJourneys\Services\OfferJourneySourceResolver;
use App\Domain\OfferJourneys\Services\OfferJourneyTracker;
use App\Domain\OfferJourneys\Services\OfferJourneyContactCapture;
use App\Domain\OfferJourneys\Services\OfferJourneyTransitionResolver;
use App\Domain\OfferJourneys\Services\OfferJourneyAttributionContext;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PublicOfferJourneyController extends Controller
{
    public function show(
        Request $request,
        User $therapist,
        string $journeySlug,
        OfferJourneySourceResolver $sourceResolver,
        OfferJourneyTracker $tracker,
        OfferJourneyTransitionResolver $transitionResolver,
        ?string $pageSlug = null
    ): View|RedirectResponse {
        $journey = OfferJourney::query()
            ->where('user_id', $therapist->id)
            ->where('slug', $journeySlug)
            ->published()
            ->with(['publishedVersion.pages.page', 'pages'])
            ->first();

        if (! $journey) {
            $redirect = OfferJourneySlugRedirect::query()
                ->whereHas('journey', fn ($query) => $query->where('user_id', $therapist->id)->published())
                ->where('scope_type', 'journey')
                ->where('old_slug', $journeySlug)
                ->latest()
                ->first();

            if ($redirect) {
                return redirect()->route('offer-journeys.public.show', [
                    'therapist' => $therapist,
                    'journeySlug' => $redirect->new_slug,
                    'pageSlug' => $pageSlug,
                ], 301);
            }

            abort(404);
        }

        $publishedPages = $journey->publishedVersion?->pages;
        abort_unless($publishedPages?->isNotEmpty(), 404);

        $pageVersion = $pageSlug
            ? $publishedPages->firstWhere('slug', $pageSlug)
            : $publishedPages->sortBy('position')->first();
        if (! $pageVersion && $pageSlug) {
            $redirect = OfferJourneySlugRedirect::query()
                ->where('offer_journey_id', $journey->id)
                ->where('scope_type', 'page')
                ->where('old_slug', $pageSlug)
                ->latest()
                ->first();
            if ($redirect && $publishedPages->contains('slug', $redirect->new_slug)) {
                return redirect()->route('offer-journeys.public.show', [
                    'therapist' => $therapist,
                    'journeySlug' => $journey->slug,
                    'pageSlug' => $redirect->new_slug,
                    ...$request->only(['oj_campaign', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content']),
                ], 301);
            }
        }
        abort_unless($pageVersion, 404);

        /** @var OfferJourneyPage $draftPage */
        $draftPage = $pageVersion->page;
        $tracker->recordPageView($journey, $journey->publishedVersion, $draftPage, $request);

        $transition = $transitionResolver->resolve($journey, $pageVersion);
        $hasPublicAction = ! empty($transition['to_page_id'])
            || ! empty($transition['external_action'])
            || filled(($pageVersion->content_json ?? [])['resource_url'] ?? null)
            || ! empty(($pageVersion->content_json ?? [])['resource_file'])
            || ($sourceResolver->sourceAvailable($journey, $therapist, true) && $sourceResolver->publicActionUrl($journey, $therapist, true));

        return view('offer-journeys.public.show', [
            'therapist' => $therapist,
            'journey' => $journey,
            'journeyDisplayName' => $journey->publishedVersion?->snapshot_json['name'] ?? $journey->name,
            'page' => $pageVersion,
            'content' => $pageVersion->content_json ?? [],
            'actionUrl' => $sourceResolver->publicActionUrl($journey, $therapist, true),
            'sourceAvailable' => $sourceResolver->sourceAvailable($journey, $therapist, true),
            'hasPublicAction' => $hasPublicAction,
            'isPreview' => false,
            'primaryActionUrl' => null,
        ]);
    }

    public function capture(
        Request $request,
        User $therapist,
        string $journeySlug,
        string $pageSlug,
        OfferJourneyContactCapture $capture
    ): RedirectResponse {
        $journey = OfferJourney::query()
            ->where('user_id', $therapist->id)
            ->where('slug', $journeySlug)
            ->published()
            ->with(['publishedVersion.pages.page'])
            ->firstOrFail();

        $page = $journey->publishedVersion?->pages->firstWhere('slug', $pageSlug);
        abort_unless($page && in_array($page->type, ['opt_in', 'qualification'], true), 404);

        $form = $page->content_json['_form'] ?? [];
        $allowedFields = collect($form['fields'] ?? [])->keyBy('name');
        $rules = [
            'website' => ['nullable', 'string', 'max:0'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'privacy_ack' => ['accepted'],
            'marketing_consent' => ['nullable', 'boolean'],
        ];
        foreach ([
            'first_name' => ['string', 'max:100'], 'last_name' => ['string', 'max:100'],
            'phone' => ['string', 'max:30'], 'contact_preference' => ['in:email,phone'],
            'city' => ['string', 'max:120'], 'postal_code' => ['string', 'max:20'],
        ] as $field => $constraints) {
            if ($allowedFields->has($field)) {
                $rules[$field] = [($allowedFields[$field]['is_required'] ?? false) ? 'required' : 'nullable', ...$constraints];
            }
        }
        foreach ($allowedFields as $fieldName => $field) {
            if (preg_match('/^custom_[1-3]_[a-z0-9_]+$/', (string) $fieldName) !== 1) {
                continue;
            }
            $optionsConfig = $field['options_json'] ?? [];
            $choices = collect($optionsConfig['options'] ?? [])->filter()->values()->all();
            $condition = $optionsConfig['show_if'] ?? null;
            $conditionMatches = ! is_array($condition)
                || (string) $request->input($condition['field'] ?? '') === (string) ($condition['value'] ?? '');
            $presence = ! empty($field['is_required']) && $conditionMatches ? 'required' : 'nullable';

            if (($field['type'] ?? null) === 'select') {
                $rules[$fieldName] = [$presence, 'string', Rule::in($choices), 'max:255'];
            } elseif (($field['type'] ?? null) === 'multiselect') {
                $rules[$fieldName] = [$presence, 'array', 'max:5'];
                $rules[$fieldName.'.*'] = ['string', Rule::in($choices)];
            } else {
                $rules[$fieldName] = [$presence, 'string', 'max:255'];
            }
        }

        $attributes = collect($allowedFields)
            ->mapWithKeys(fn (array $field, string $name) => [$name => mb_strtolower((string) ($field['label'] ?? $name))])
            ->merge([
                'email' => 'adresse email',
                'privacy_ack' => 'confirmation de confidentialité',
                'marketing_consent' => 'consentement aux suivis',
            ])
            ->all();

        $validated = $request->validate($rules, [
            'required' => 'Le champ :attribute est obligatoire.',
            'email' => 'Indiquez une adresse email valide.',
            'accepted' => 'Veuillez confirmer avoir pris connaissance de l’utilisation de vos informations.',
            'in' => 'La réponse choisie pour :attribute n’est pas valide.',
            'max.string' => 'Le champ :attribute est trop long.',
            'max.array' => 'Vous avez sélectionné trop de réponses pour :attribute.',
        ], $attributes);

        $result = $capture->capture($therapist, $journey, $page, $validated, $request);
        $nextPageSlug = $result['next_page_slug'];

        if (! $nextPageSlug) {
            return redirect()->route('therapist.show', $therapist->slug)
                ->with('success', 'Votre demande a bien été prise en compte.');
        }

        return redirect()->route('offer-journeys.public.show', [
            'therapist' => $therapist,
            'journeySlug' => $journey->slug,
            'pageSlug' => $nextPageSlug,
            ...$request->only(['oj_campaign', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content']),
        ]);
    }

    public function follow(
        Request $request,
        User $therapist,
        string $journeySlug,
        string $pageSlug,
        OfferJourneySourceResolver $sourceResolver,
        OfferJourneyTracker $tracker,
        OfferJourneyTransitionResolver $transitionResolver,
        OfferJourneyAttributionContext $attributionContext
    ): RedirectResponse {
        $journey = OfferJourney::query()
            ->where('user_id', $therapist->id)
            ->where('slug', $journeySlug)
            ->published()
            ->with(['publishedVersion.pages.page'])
            ->firstOrFail();
        $pageVersion = $journey->publishedVersion?->pages->firstWhere('slug', $pageSlug);
        abort_unless($pageVersion?->page, 404);

        $tracker->recordPrimaryAction($journey, $journey->publishedVersion, $pageVersion->page, $request);

        $nextPageSlug = $transitionResolver->nextPageSlug($journey, $pageVersion);
        if ($nextPageSlug) {
            return redirect()->route('offer-journeys.public.show', [
                'therapist' => $therapist,
                'journeySlug' => $journey->slug,
                'pageSlug' => $nextPageSlug,
                ...$request->only(['oj_campaign', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content']),
            ]);
        }

        $resourceUrl = $pageVersion->content_json['resource_url'] ?? null;
        if (is_string($resourceUrl) && filter_var($resourceUrl, FILTER_VALIDATE_URL)) {
            return redirect()->away($resourceUrl);
        }

        if (! empty($pageVersion->content_json['resource_file'])) {
            return redirect()->away(URL::temporarySignedRoute(
                'offer-journeys.resources.download',
                now()->addMinutes((int) config('offer_journeys.resource_link_minutes', 30)),
                ['pageVersion' => $pageVersion]
            ));
        }

        $actionUrl = $sourceResolver->publicActionUrl($journey, $therapist, true);
        abort_unless($actionUrl && $sourceResolver->sourceAvailable($journey, $therapist, true), 404);

        return redirect()->away($actionUrl)->withCookie($attributionContext->cookie($journey, $request));
    }
}
