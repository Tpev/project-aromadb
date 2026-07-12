<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneySlugRedirect;
use App\Domain\OfferJourneys\Models\OfferJourneyVersion;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyConversion;
use App\Domain\OfferJourneys\Services\OfferJourneyPipeline;
use App\Domain\OfferJourneys\Services\OfferJourneyPublisher;
use App\Domain\OfferJourneys\Services\OfferJourneyAutomationBuilder;
use App\Domain\OfferJourneys\Services\OfferJourneyResourceStorage;
use App\Domain\OfferJourneys\Services\OfferJourneyPublicationPreflight;
use App\Domain\OfferJourneys\Services\OfferJourneyTemplateLibrary;
use App\Http\Controllers\Controller;
use App\Models\DigitalTraining;
use App\Models\Event;
use App\Models\Product;
use App\Support\OfferJourneys\OfferJourneyAccess;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class OfferJourneyController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, OfferJourneyAccess $access): View
    {
        $this->authorize('viewAny', OfferJourney::class);

        $journeys = OfferJourney::query()
            ->ownedBy($request->user())
            ->withCount([
                'events as views_count' => fn ($query) => $query->where('event_name', 'page_viewed'),
                'events as leads_count' => fn ($query) => $query->where('event_name', 'lead_captured'),
                'conversions as conversions_count' => fn ($query) => $query->where('status', 'confirmed'),
                'campaignLinks as campaign_links_count',
            ])
            ->latest('updated_at')
            ->paginate(15);

        $firstJourney = $journeys->first();
        $newContacts = OfferJourneyContact::query()->ownedBy($request->user())->where('status', 'new')->count();

        return view('offer-journeys.practitioner.index', [
            'journeys' => $journeys,
            'canPublish' => $access->canPublish($request->user()),
            'summary' => [
                'published' => OfferJourney::query()->ownedBy($request->user())->where('status', 'published')->count(),
                'contacts' => OfferJourneyContact::query()->ownedBy($request->user())->where('created_at', '>=', now()->subDays(30))->count(),
                'conversions' => OfferJourneyConversion::query()->whereHas('journey', fn ($query) => $query->ownedBy($request->user()))->where('status', 'confirmed')->where('occurred_at', '>=', now()->subDays(30))->count(),
                'revenue_cents' => (int) OfferJourneyConversion::query()->whereHas('journey', fn ($query) => $query->ownedBy($request->user()))->where('status', 'confirmed')->where('occurred_at', '>=', now()->subDays(30))->sum('amount_cents'),
                'inactive' => OfferJourneyContact::query()->ownedBy($request->user())->where('status', '!=', 'converted')->where('last_activity_at', '<=', now()->subDays(30))->count(),
            ],
            'activation' => $this->activationState($firstJourney, $newContacts),
        ]);
    }

    public function create(Request $request, OfferJourneyTemplateLibrary $templates): View
    {
        $this->authorize('create', OfferJourney::class);

        $userId = $request->user()->id;

        return view('offer-journeys.practitioner.create', [
            'objectives' => $this->objectives(),
            'products' => Product::query()->where('user_id', $userId)->orderBy('name')->get(),
            'events' => Event::query()->where('user_id', $userId)->latest('start_date_time')->get(),
            'trainings' => DigitalTraining::query()->where('user_id', $userId)->orderBy('title')->get(),
            'templates' => (bool) config('offer_journeys.template_library_enabled', false) ? $templates->all() : collect(),
        ]);
    }

    private function activationState(?OfferJourney $journey, int $newContacts): array
    {
        if (! $journey) {
            return [
                'next' => ['title' => 'Créez votre première page', 'body' => 'Choisissez un objectif. Olithea préparera le formulaire, la confirmation et le suivi adaptés.', 'label' => 'Commencer', 'url' => route('offer-journeys.create')],
                'checks' => [['label' => 'Créer une page', 'done' => false], ['label' => 'Vérifier le parcours', 'done' => false], ['label' => 'Publier', 'done' => false], ['label' => 'Partager', 'done' => false], ['label' => 'Recevoir un premier contact', 'done' => false]],
            ];
        }

        $published = $journey->status === 'published';
        $shared = $journey->campaign_links_count > 0 || $journey->views_count > 0;
        $hasContact = $journey->leads_count > 0;
        $next = match (true) {
            ! $published => ['title' => 'Vérifiez puis publiez votre page', 'body' => 'Contrôlez les textes, le formulaire et la destination avant de la rendre visible.', 'label' => 'Vérifier le parcours', 'url' => route('offer-journeys.show', $journey)],
            $newContacts > 0 => ['title' => $newContacts.' nouvelle(s) personne(s) attendent votre suivi', 'body' => 'Consultez leur demande et choisissez une prochaine action.', 'label' => 'Voir les personnes', 'url' => route('offer-journeys.contacts.index', ['status' => 'new'])],
            ! $shared => ['title' => 'Partagez votre page', 'body' => 'Préparez un lien pour Instagram, votre newsletter, un email ou un support imprimé.', 'label' => 'Préparer le partage', 'url' => route('offer-journeys.share', $journey)],
            $journey->views_count >= 20 && $journey->leads_count === 0 => ['title' => 'Votre page est consultée sans générer de demande', 'body' => 'Regardez où les visiteurs s’arrêtent avant de modifier le titre, le formulaire ou le bouton.', 'label' => 'Comprendre les résultats', 'url' => route('offer-journeys.analytics', $journey)],
            default => ['title' => 'Consultez les résultats', 'body' => 'Suivez les visites, contacts et actions confirmées pour décider de la prochaine amélioration.', 'label' => 'Voir les résultats', 'url' => route('offer-journeys.analytics', $journey)],
        };

        return [
            'next' => $next,
            'checks' => [
                ['label' => 'Créer une page', 'done' => true],
                ['label' => 'Vérifier le parcours', 'done' => $published],
                ['label' => 'Publier', 'done' => $published],
                ['label' => 'Partager', 'done' => $shared],
                ['label' => 'Recevoir un premier contact', 'done' => $hasContact],
            ],
        ];
    }

    public function store(Request $request, OfferJourneyPipeline $pipeline, OfferJourneyAutomationBuilder $automationBuilder, OfferJourneyResourceStorage $resourceStorage, OfferJourneyTemplateLibrary $templates): RedirectResponse
    {
        $this->authorize('create', OfferJourney::class);

        [$sourceType, $sourceId] = array_pad(explode(':', (string) $request->input('source_ref'), 2), 2, null);
        if ($request->input('objective') === 'gift_voucher' && ! $sourceType) {
            $sourceType = 'gift_voucher';
        }
        $request->merge([
            'source_type' => $sourceType ?: null,
            'source_id' => ctype_digit((string) $sourceId) ? (int) $sourceId : null,
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'objective' => ['required', Rule::in(config('offer_journeys.allowed_objectives', []))],
            'source_type' => ['nullable', Rule::in(['product', 'event', 'digital_training', 'gift_voucher', 'custom'])],
            'source_id' => ['nullable', 'integer'],
            'source_ref' => ['nullable', 'string', 'max:100'],
            'public_title' => ['required', 'string', 'max:180'],
            'summary' => ['nullable', 'string', 'max:1200'],
            'cta_label' => ['required', 'string', 'max:80'],
            'resource_url' => ['nullable', 'url:http,https', 'max:2000'],
            'resource_file' => ['nullable', 'file', 'max:51200', 'mimetypes:application/pdf,application/zip,audio/mpeg,audio/mp4,audio/x-m4a,audio/wav,video/mp4,video/webm'],
            'template_key' => ['nullable', 'string', Rule::in($templates->all()->pluck('key')->all())],
        ]);

        $template = (bool) config('offer_journeys.template_library_enabled', false)
            ? $templates->get($validated['template_key'] ?? null)
            : null;
        if ($template && $template['objective'] !== $validated['objective']) {
            throw ValidationException::withMessages([
                'template_key' => 'Ce modele ne correspond pas a l objectif selectionne.',
            ]);
        }

        if ($validated['objective'] === 'lead_magnet'
            && blank($validated['resource_url'] ?? null)
            && ! $request->hasFile('resource_file')) {
            throw ValidationException::withMessages(['resource_file' => 'Ajoutez un fichier privé ou un lien HTTPS vers la ressource.']);
        }

        $user = $request->user();
        $activeCount = OfferJourney::query()
            ->ownedBy($user)
            ->whereIn('status', ['draft', 'published', 'paused'])
            ->count();

        abort_if($activeCount >= config('offer_journeys.limits.active_per_user', 10), 422, 'Limite de parcours actifs atteinte.');
        $this->validateOwnedSource(
            $user->id,
            $validated['objective'],
            $validated['source_type'] ?? null,
            $validated['source_id'] ?? null
        );

        $resourceFile = $request->hasFile('resource_file')
            ? $resourceStorage->store($request->file('resource_file'), $user)
            : null;

        try {
            $journey = DB::transaction(function () use ($validated, $user, $pipeline, $automationBuilder, $resourceFile, $template) {
            $slug = $this->uniqueSlug($user->id, $validated['name']);

            $journey = OfferJourney::query()->create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'slug' => $slug,
                'objective' => $validated['objective'],
                'status' => 'draft',
                'source_type' => $validated['source_type'] ?? null,
                'source_id' => $validated['source_id'] ?? null,
                'primary_conversion_type' => $validated['objective'],
                'timezone' => config('app.timezone', 'Europe/Paris'),
            ]);

            $mainPage = $journey->pages()->create([
                'name' => 'Page principale',
                'slug' => 'offre',
                'type' => match ($validated['objective']) {
                    'lead_magnet' => 'opt_in',
                    'contact_request' => 'qualification',
                    default => 'sales',
                },
                'position' => 0,
                'draft_content_json' => [
                    'title' => $validated['public_title'],
                    'summary' => filled($validated['summary'] ?? null) ? $validated['summary'] : ($template['summary'] ?? ''),
                    'cta_label' => $validated['cta_label'],
                    'audience' => $template['audience'] ?? '',
                    'outcomes' => $template['outcomes'] ?? [],
                    'steps' => $template['steps'] ?? [],
                    'practical_details' => $template['practical_details'] ?? '',
                    'faq' => $template['faq'] ?? [],
                    'template_key' => $template['key'] ?? null,
                    'resource_url' => $validated['resource_url'] ?? null,
                    'resource_file' => $resourceFile,
                ],
                'validation_state' => 'ready',
            ]);

            $actionPage = null;
            if ($validated['objective'] !== 'contact_request') {
                $actionPage = $journey->pages()->create([
                    'name' => $validated['objective'] === 'lead_magnet' ? 'Ressource' : 'Passage a l action',
                    'slug' => $validated['objective'] === 'lead_magnet' ? 'ressource' : 'action',
                    'type' => match ($validated['objective']) {
                        'lead_magnet' => 'content',
                        'appointment' => 'booking',
                        'event' => 'event_registration',
                        'training' => 'training_access',
                        'gift_voucher' => 'checkout',
                        default => 'content',
                    },
                    'position' => 1,
                    'draft_content_json' => [
                        'title' => match ($validated['objective']) {
                            'lead_magnet' => 'Votre ressource est prete',
                            'appointment' => 'Choisissez votre rendez-vous',
                            'event' => 'Inscrivez-vous a cet evenement',
                            'training' => 'Accedez a la formation',
                            'gift_voucher' => 'Finalisez votre bon cadeau',
                            default => 'Continuer',
                        },
                        'summary' => '',
                        'cta_label' => match ($validated['objective']) {
                            'lead_magnet' => 'Telecharger la ressource',
                            'appointment' => 'Voir les disponibilites',
                            'event' => "S'inscrire",
                            'training' => 'Decouvrir la formation',
                            'gift_voucher' => 'Choisir un bon cadeau',
                            default => 'Continuer',
                        },
                        'resource_url' => $validated['resource_url'] ?? null,
                        'resource_file' => $resourceFile,
                    ],
                    'validation_state' => 'ready',
                ]);
            }

            $thankYouPage = $journey->pages()->create([
                'name' => 'Confirmation',
                'slug' => 'merci',
                'type' => 'thank_you',
                'position' => $actionPage ? 2 : 1,
                'draft_content_json' => [
                    'title' => 'Merci, votre demande a bien été prise en compte.',
                    'summary' => $validated['objective'] === 'lead_magnet'
                        ? 'Votre ressource est disponible ci-dessous et vous sera également envoyée par email.'
                        : 'Vous allez recevoir les prochaines informations par email.',
                    'cta_label' => $validated['objective'] === 'lead_magnet' ? 'Accéder à la ressource' : 'Voir le profil du praticien',
                    'resource_url' => $validated['resource_url'] ?? null,
                    'resource_file' => $resourceFile,
                ],
                'validation_state' => 'ready',
            ]);

            $journey->transitions()->create([
                'from_page_id' => $mainPage->id,
                'to_page_id' => $actionPage?->id ?: $thankYouPage->id,
                'trigger' => 'primary_cta',
                'external_action' => null,
                'priority' => 0,
            ]);

            if ($actionPage) {
                $journey->transitions()->create([
                    'from_page_id' => $actionPage->id,
                    'to_page_id' => null,
                    'trigger' => 'primary_cta',
                    'external_action' => match ($validated['objective']) {
                        'appointment' => 'appointment_booking',
                        'event' => 'event_registration',
                        'training' => 'training_access',
                        'gift_voucher' => 'gift_voucher_checkout',
                        default => null,
                    },
                    'priority' => 0,
                ]);
            }

            if (in_array($validated['objective'], ['lead_magnet', 'contact_request'], true)) {
                $form = $mainPage->form()->create([
                    'offer_journey_id' => $journey->id,
                    'submit_label' => $validated['cta_label'],
                    'privacy_text' => 'Vos informations sont utilisées uniquement pour répondre à cette demande.',
                    'marketing_consent_mode' => 'optional',
                ]);

                $form->fields()->createMany([
                    ['name' => 'first_name', 'label' => 'Prénom', 'type' => 'text', 'is_required' => true, 'position' => 0, 'purpose' => 'identifier le contact'],
                    ['name' => 'email', 'label' => 'Adresse email', 'type' => 'email', 'is_required' => true, 'position' => 1, 'purpose' => 'répondre à la demande'],
                    ['name' => 'phone', 'label' => 'Téléphone', 'type' => 'tel', 'is_required' => false, 'position' => 2, 'purpose' => 'recontacter si demandé'],
                ]);
            }

            $pipeline->ensureDefaults($user);
            $automationBuilder->createV1Draft($journey, $user);

                return $journey;
            });
        } catch (\Throwable $exception) {
            $resourceStorage->delete($resourceFile);
            throw $exception;
        }

        return redirect()->route('offer-journeys.show', $journey)
            ->with('success', 'Le parcours a été créé en brouillon.');
    }

    public function show(Request $request, OfferJourney $journey, OfferJourneyPublicationPreflight $preflight): View
    {
        $this->authorize('view', $journey);
        $journey->load(['pages.form.fields', 'transitions', 'versions.publisher']);

        return view('offer-journeys.practitioner.show', [
            'journey' => $journey,
            'preflight' => (bool) config('offer_journeys.publication_assistance_enabled', false)
                ? $preflight->inspect($journey)
                : null,
        ]);
    }

    public function edit(OfferJourney $journey): View
    {
        $this->authorize('update', $journey);
        $journey->load(['pages.form.fields', 'transitions']);

        return view('offer-journeys.practitioner.edit', compact('journey'));
    }

    public function update(Request $request, OfferJourney $journey): RedirectResponse
    {
        $this->authorize('update', $journey);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => [
                'required',
                'alpha_dash',
                'max:160',
                Rule::unique('offer_journeys')->where('user_id', $request->user()->id)->ignore($journey->id),
            ],
            'show_on_profile' => ['nullable', 'boolean'],
        ]);

        $oldSlug = $journey->slug;
        $journey->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'show_on_profile' => $request->boolean('show_on_profile'),
        ]);

        if ($oldSlug !== $journey->slug) {
            OfferJourneySlugRedirect::query()->updateOrCreate(
                ['offer_journey_id' => $journey->id, 'scope_type' => 'journey', 'old_slug' => $oldSlug],
                ['offer_journey_page_id' => null, 'new_slug' => $journey->slug]
            );
        }

        return back()->with('success', 'Les paramètres ont été enregistrés.');
    }

    public function publish(OfferJourney $journey, OfferJourneyPublisher $publisher): RedirectResponse
    {
        $this->authorize('publish', $journey);
        $version = $publisher->publish($journey, request()->user());

        return back()->with('success', "Version {$version->version_number} publiée.");
    }

    public function pause(OfferJourney $journey): RedirectResponse
    {
        $this->authorize('update', $journey);
        $journey->update(['status' => 'paused', 'paused_at' => now()]);

        return back()->with('success', 'Le parcours est en pause.');
    }

    public function archive(OfferJourney $journey): RedirectResponse
    {
        $this->authorize('delete', $journey);
        $journey->update(['status' => 'archived', 'archived_at' => now(), 'show_on_profile' => false]);

        return redirect()->route('offer-journeys.index')->with('success', 'Le parcours a été archivé.');
    }

    public function duplicate(Request $request, OfferJourney $journey): RedirectResponse
    {
        $this->authorize('view', $journey);
        $this->authorize('create', OfferJourney::class);
        abort_if(
            OfferJourney::query()->ownedBy($request->user())->whereIn('status', ['draft', 'published', 'paused'])->count()
                >= config('offer_journeys.limits.active_per_user', 10),
            422,
            'Limite de parcours actifs atteinte.'
        );
        $journey->load(['pages.form.fields', 'transitions', 'automations.versions.nodes']);

        $copy = DB::transaction(function () use ($journey, $request) {
            $copy = $journey->replicate(['published_version_id', 'published_at', 'paused_at', 'archived_at']);
            $copy->name = $journey->name.' - copie';
            $copy->slug = $this->uniqueSlug($request->user()->id, $copy->name);
            $copy->status = 'draft';
            $copy->show_on_profile = false;
            $copy->save();

            $pageMap = [];
            foreach ($journey->pages as $page) {
                $newPage = $copy->pages()->create($page->only([
                    'name', 'slug', 'type', 'position', 'draft_content_json', 'theme_json',
                    'seo_title', 'seo_description', 'is_indexable', 'validation_state',
                ]));
                $pageMap[$page->id] = $newPage->id;
                if ($page->form) {
                    $newForm = $newPage->form()->create([
                        ...$page->form->only(['submit_label', 'success_message', 'privacy_text', 'marketing_consent_mode', 'is_active']),
                        'offer_journey_id' => $copy->id,
                    ]);
                    foreach ($page->form->fields as $field) {
                        $newForm->fields()->create($field->only(['name', 'label', 'type', 'is_required', 'options_json', 'position', 'purpose']));
                    }
                }
            }
            foreach ($journey->transitions as $transition) {
                $copy->transitions()->create([
                    ...$transition->only(['trigger', 'condition_json', 'external_action', 'priority', 'is_fallback', 'is_active']),
                    'from_page_id' => $pageMap[$transition->from_page_id],
                    'to_page_id' => $transition->to_page_id ? ($pageMap[$transition->to_page_id] ?? null) : null,
                ]);
            }
            foreach ($journey->automations as $automation) {
                $newAutomation = $copy->automations()->create([
                    ...$automation->only(['name', 'trigger_type', 'reentry_mode', 'reentry_delay_days', 'quiet_hours_start', 'quiet_hours_end']),
                    'user_id' => $request->user()->id,
                    'status' => 'draft',
                ]);
                $sourceVersion = $automation->versions->firstWhere('status', 'draft')
                    ?? $automation->versions->sortByDesc('version_number')->first();
                if ($sourceVersion) {
                    $newVersion = $newAutomation->versions()->create(['version_number' => 1, 'status' => 'draft', 'definition_json' => ['schema_version' => 1]]);
                    foreach ($sourceVersion->nodes as $node) {
                        $newVersion->nodes()->create($node->only(['node_key', 'type', 'name', 'config_json', 'next_node_key', 'yes_node_key', 'no_node_key', 'position_x', 'position_y']));
                    }
                }
            }

            return $copy;
        });

        return redirect()->route('offer-journeys.show', $copy)->with('success', 'Le parcours et ses messages ont été dupliqués en brouillon.');
    }

    public function restore(
        OfferJourney $journey,
        OfferJourneyVersion $version,
        OfferJourneyPublisher $publisher
    ): RedirectResponse {
        $this->authorize('publish', $journey);
        $publisher->restore($journey, $version);

        return back()->with('success', "La version {$version->version_number} est de nouveau en ligne.");
    }

    private function uniqueSlug(int $userId, string $name): string
    {
        $base = Str::slug($name) ?: 'parcours';
        $slug = $base;
        $counter = 2;

        while (OfferJourney::query()->where('user_id', $userId)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    private function validateOwnedSource(int $userId, string $objective, ?string $sourceType, ?int $sourceId): void
    {
        if (! $sourceType || ! $sourceId || $sourceType === 'custom') {
            return;
        }

        $expectedSource = match ($objective) {
            'appointment' => 'product',
            'event' => 'event',
            'training' => 'digital_training',
            'gift_voucher' => 'gift_voucher',
            default => null,
        };

        abort_if($expectedSource && $sourceType !== $expectedSource, 422, 'La ressource ne correspond pas à l’objectif choisi.');

        $model = match ($sourceType) {
            'product' => Product::class,
            'event' => Event::class,
            'digital_training' => DigitalTraining::class,
            'gift_voucher' => GiftVoucher::class,
            default => null,
        };

        abort_unless($model && $model::query()->whereKey($sourceId)->where('user_id', $userId)->exists(), 422, 'La ressource sélectionnée est indisponible.');
    }

    private function objectives(): array
    {
        return [
            'appointment' => ['label' => 'Obtenir des réservations', 'description' => 'Présenter une séance puis ouvrir votre agenda.'],
            'event' => ['label' => 'Remplir un atelier', 'description' => 'Présenter un événement et recueillir les inscriptions.'],
            'lead_magnet' => ['label' => 'Offrir une ressource', 'description' => 'Recueillir un contact puis délivrer un contenu gratuit.'],
            'training' => ['label' => 'Proposer une formation', 'description' => 'Présenter une formation et donner accès au programme.'],
            'gift_voucher' => ['label' => 'Vendre un bon cadeau', 'description' => 'Expliquer le cadeau puis ouvrir le paiement existant.'],
            'contact_request' => ['label' => 'Recevoir une demande qualifiée', 'description' => 'Recueillir une demande courte avant un premier échange.'],
        ];
    }
}
