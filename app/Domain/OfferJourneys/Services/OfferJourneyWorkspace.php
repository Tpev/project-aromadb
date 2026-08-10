<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyEntry;
use App\Domain\OfferJourneys\Models\OfferJourneyEvent;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageDelivery;
use App\Domain\OfferJourneys\Models\OfferJourneyTask;
use Illuminate\Support\Carbon;

class OfferJourneyWorkspace
{
    public function __construct(
        private readonly OfferJourneyPublicationPreflight $preflight,
        private readonly OfferJourneySourceResolver $sourceResolver,
        private readonly OfferJourneyResourceStorage $resourceStorage,
    ) {
    }

    /**
     * Build a read-only product view from existing journey data.
     * Nothing returned here is stored as a competing source of truth.
     */
    public function for(OfferJourney $journey, int $days = 30): array
    {
        $journey->loadMissing([
            'user',
            'pages.form.fields',
            'publishedVersion.pages',
            'versions',
            'transitions',
            'automations.versions.nodes',
            'automations.publishedVersion.nodes',
            'campaignLinks',
        ]);

        $pages = $journey->pages->sortBy('position')->values();
        $preflight = $this->preflight->inspect($journey);
        $firstPage = $pages->first();
        $formPage = $pages->first(fn ($page) => $page->form !== null);
        $confirmationPage = $pages->firstWhere('type', 'thank_you');
        $automation = $journey->automations->first();
        $automationVersion = $automation?->versions
            ?->sortByDesc('version_number')
            ->first(fn ($version) => $version->status === 'draft')
            ?? $automation?->publishedVersion;
        $messageNodes = $automationVersion?->nodes
            ?->where('type', 'email')
            ->sortBy('position_y')
            ->values()
            ?? collect();

        $content = $firstPage?->draft_content_json ?? [];
        $pageReady = $firstPage !== null
            && filled($content['title'] ?? null)
            && filled($content['cta_label'] ?? null)
            && $preflight['checks']['content']
            && $preflight['checks']['navigation'];
        $formRequired = in_array($journey->objective, ['lead_magnet', 'contact_request'], true);
        $formReady = ! $formRequired || ($formPage?->form?->is_active && $formPage->form->fields->contains('name', 'email'));
        $enabledMessages = $messageNodes->filter(function ($node) {
            $config = $node->config_json ?? [];

            return (bool) ($config['is_enabled'] ?? false)
                && filled($config['subject'] ?? null)
                && (filled($config['body'] ?? null) || ! empty(data_get($config, 'email_content.blocks')));
        });
        $messagesDraftReady = $enabledMessages->isNotEmpty();
        $activeMessages = $automation?->publishedVersion?->nodes
            ?->where('type', 'email')
            ->filter(function ($node) {
                $config = $node->config_json ?? [];

                return (bool) ($config['is_enabled'] ?? false)
                    && filled($config['subject'] ?? null)
                    && (filled($config['body'] ?? null) || ! empty(data_get($config, 'email_content.blocks')));
            }) ?? collect();
        $messagesActive = $automation?->status === 'active' && $activeMessages->isNotEmpty();
        $resourceReady = $pages->contains(function ($page): bool {
            $pageContent = $page->draft_content_json ?? [];

            return filled($pageContent['resource_url'] ?? null)
                || $this->resourceStorage->exists($pageContent['resource_file'] ?? null);
        });
        $sourceReady = $this->sourceResolver->sourceAvailable($journey, $journey->user);
        $destinationReady = match ($journey->objective) {
            'lead_magnet' => $resourceReady,
            'contact_request' => true,
            default => $sourceReady,
        };
        $published = $journey->status === 'published' && $journey->published_version_id !== null;
        $hasUnpublishedChanges = $published && $this->hasUnpublishedChanges($journey, $pages);
        $tested = $published || OfferJourneyMessageDelivery::query()
            ->where('offer_journey_id', $journey->id)
            ->where('is_test', true)
            ->exists();
        $shared = $journey->campaignLinks->contains('is_active', true)
            || OfferJourneyEvent::query()
                ->where('offer_journey_id', $journey->id)
                ->where('event_name', 'page_viewed')
                ->where('is_test', false)
                ->where('is_bot', false)
                ->exists();

        $from = Carbon::now()->subDays(in_array($days, [7, 30, 90, 365], true) ? $days : 30)->startOfDay();
        $events = OfferJourneyEvent::query()
            ->where('offer_journey_id', $journey->id)
            ->where('occurred_at', '>=', $from)
            ->where('is_test', false)
            ->where('is_bot', false);
        $entries = OfferJourneyEntry::query()
            ->where('offer_journey_id', $journey->id)
            ->where('entered_at', '>=', $from);
        $confirmedConversions = $journey->conversions()
            ->where('status', 'confirmed')
            ->where('occurred_at', '>=', $from);

        $metrics = [
            'visitors' => (clone $events)->where('event_name', 'page_viewed')->whereNotNull('session_id')->distinct()->count('session_id'),
            'views' => (clone $events)->where('event_name', 'page_viewed')->count(),
            'submissions' => (clone $events)->where('event_name', 'lead_captured')->count(),
            'unique_contacts' => (clone $entries)->distinct()->count('offer_journey_contact_id'),
            'conversions' => (clone $confirmedConversions)->count(),
            'revenue_cents' => (int) (clone $confirmedConversions)->sum('amount_cents'),
        ];

        $pendingContacts = OfferJourneyContact::query()
            ->where('user_id', $journey->user_id)
            ->whereHas('entries', fn ($query) => $query->where('offer_journey_id', $journey->id))
            ->whereIn('status', ['new', 'qualifying'])
            ->count();
        $dueTasks = OfferJourneyTask::query()
            ->where('user_id', $journey->user_id)
            ->where('offer_journey_id', $journey->id)
            ->where('status', 'open')
            ->where(fn ($query) => $query->whereNull('due_at')->orWhere('due_at', '<=', now()))
            ->count();

        $pageUrl = $firstPage
            ? route('offer-journeys.pages.edit', [$journey, $firstPage])
            : route('offer-journeys.show', $journey);
        $formUrl = $formPage
            ? route('offer-journeys.pages.edit', [$journey, $formPage]).'?section=form'
            : $pageUrl.'?section=form';

        $progress = collect([
            $this->progressItem('page', 'Page prête', $pageReady, false, $pageUrl),
            $this->progressItem('form', $formRequired ? 'Formulaire prêt' : 'Formulaire non requis', $formReady, ! $formRequired, $formUrl),
            $this->progressItem('destination', $this->destinationProgressLabel($journey->objective), $destinationReady, false, $this->destinationUrl($journey, $pages, $pageUrl)),
            $this->progressItem('messages', $messagesActive ? 'Messages actifs' : ($messagesDraftReady ? 'Messages à activer' : 'Messages à préparer'), $messagesActive, false, route('offer-journeys.automation', $journey)),
            $this->progressItem('tested', 'Testé', $tested, false, route('offer-journeys.preview', $journey)),
            $this->progressItem('published', 'Version en ligne', $published, false, route('offer-journeys.show', $journey)),
            $this->progressItem('shared', 'Partagé', $shared, false, route('offer-journeys.share', $journey)),
        ]);

        $nextAction = $this->nextAction(
            $journey,
            compact('pageReady', 'formRequired', 'formReady', 'messagesDraftReady', 'messagesActive', 'tested', 'published', 'shared', 'pendingContacts', 'dueTasks'),
            $pageUrl,
            $formUrl,
            $preflight,
            $pages
        );

        return [
            'first_page' => $firstPage,
            'form_page' => $formPage,
            'confirmation_page' => $confirmationPage,
            'automation' => $automation,
            'automation_version' => $automationVersion,
            'message_nodes' => $messageNodes,
            'progress' => $progress,
            'progress_percent' => (int) round(100 * $progress->where('status', 'ready')->count() / max(1, $progress->where('status', '!=', 'disabled')->count())),
            'next_action' => $nextAction,
            'draft_has_blockers' => ! $preflight['ready'],
            'public_version_is_live' => $published,
            'has_unpublished_changes' => $hasUnpublishedChanges,
            'metrics' => $metrics,
            'pending_contacts' => $pendingContacts,
            'due_tasks' => $dueTasks,
            'journey_map' => $this->journeyMap($journey, $pages, $formPage, $confirmationPage, $messageNodes, $progress, $pageUrl, $formUrl, $preflight, $resourceReady, $sourceReady),
        ];
    }

    private function progressItem(string $key, string $label, bool $ready, bool $disabled, string $url): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'status' => $disabled ? 'disabled' : ($ready ? 'ready' : 'attention'),
            'url' => $url,
        ];
    }

    private function nextAction(OfferJourney $journey, array $state, string $pageUrl, string $formUrl, array $preflight, $pages): array
    {
        if ($preflight['errors'] !== []) {
            return $this->blockingAction($journey, $preflight['errors'], $pages, $pageUrl, $formUrl, $state['published']);
        }

        return match (true) {
            ! $state['pageReady'] => $this->action('Complétez votre page', 'Ajoutez un titre clair et un bouton qui décrit précisément la prochaine étape.', 'Modifier la page', $pageUrl),
            $state['formRequired'] && ! $state['formReady'] => $this->action('Préparez le formulaire', 'Demandez uniquement les informations nécessaires pour répondre à la demande.', 'Configurer le formulaire', $formUrl),
            ! $state['messagesDraftReady'] => $this->action('Préparez le premier message', 'Rédigez la confirmation que la personne recevra après sa demande.', 'Préparer les messages', route('offer-journeys.automation', $journey)),
            ! $state['messagesActive'] => $this->action('Activez les messages préparés', 'Relisez le brouillon puis activez-le. Rien ne sera envoyé aux anciens contacts.', 'Vérifier et activer', route('offer-journeys.automation', $journey)),
            ! $state['tested'] => $this->action('Testez le parcours', 'Parcourez la page comme un visiteur avant de la rendre publique.', 'Ouvrir l’aperçu', route('offer-journeys.preview', $journey)),
            ! $state['published'] => $this->action('Publiez votre page', 'La page et son suivi sont prêts. La publication reste réversible.', 'Vérifier et publier', route('offer-journeys.show', $journey)),
            ! $state['shared'] => $this->action('Partagez votre page', 'Créez un lien distinct pour le premier canal afin d’en mesurer les résultats.', 'Préparer le partage', route('offer-journeys.share', $journey)),
            $state['pendingContacts'] > 0 => $this->action(
                $state['pendingContacts'] === 1 ? '1 demande attend votre réponse' : $state['pendingContacts'].' demandes attendent votre réponse',
                'Consultez les demandes récentes et définissez la prochaine action.',
                'Voir les contacts',
                route('offer-journeys.contacts.index', ['journey_id' => $journey->id]),
            ),
            $state['dueTasks'] > 0 => $this->action(
                $state['dueTasks'] === 1 ? '1 action est à traiter' : $state['dueTasks'].' actions sont à traiter',
                'Terminez les actions prévues avant de modifier la page.',
                'Voir le suivi',
                route('offer-journeys.contacts.pipeline'),
            ),
            default => $this->action('Consultez les résultats', 'Repérez le canal et la page qui produisent les demandes les plus utiles.', 'Voir les résultats', route('offer-journeys.analytics', $journey)),
        };
    }

    private function blockingAction(OfferJourney $journey, array $errors, $pages, string $pageUrl, string $formUrl, bool $published): array
    {
        $key = (string) array_key_first($errors);
        $message = (string) reset($errors);
        $body = $message.($published ? ' La version actuellement en ligne reste inchangée.' : '');

        if ($key === 'source') {
            return $this->action('Associez l’offre proposée', $body, 'Choisir l’offre', route('offer-journeys.edit', $journey));
        }
        if ($key === 'resource') {
            $resourcePage = $pages->first(fn ($page) => in_array($page->type, ['content', 'opt_in'], true));

            return $this->action('Ajoutez la ressource promise', $body, 'Ajouter la ressource', $resourcePage ? route('offer-journeys.pages.edit', [$journey, $resourcePage]) : $pageUrl);
        }
        if (str_starts_with($key, 'transition_')) {
            $page = $pages->firstWhere('id', (int) str_replace('transition_', '', $key));

            return $this->action('Indiquez ce qui se passe ensuite', $body, 'Configurer la suite', $page ? route('offer-journeys.pages.edit', [$journey, $page]).'?section=after' : $pageUrl);
        }
        if (str_starts_with($key, 'form_') || str_starts_with($key, 'field_')) {
            return $this->action('Terminez le formulaire', $body, 'Configurer le formulaire', $formUrl);
        }

        return $this->action('Terminez le brouillon', $body, 'Corriger la page', $pageUrl);
    }

    private function action(string $title, string $body, string $label, string $url): array
    {
        return compact('title', 'body', 'label', 'url');
    }

    private function journeyMap(OfferJourney $journey, $pages, $formPage, $confirmationPage, $messageNodes, $progress, string $pageUrl, string $formUrl, array $preflight, bool $resourceReady, bool $sourceReady): array
    {
        $formProgress = $progress->firstWhere('key', 'form');
        $map = [];
        foreach ($pages->where('type', '!=', 'thank_you') as $page) {
            $pageContent = $page->draft_content_json ?? [];
            $hasTransitionError = isset($preflight['errors']['transition_'.$page->id]);
            $map[] = [
                'label' => $this->pageLabel($page->type, $journey->objective),
                'detail' => $page->name,
                'status' => $hasTransitionError
                    ? 'error'
                    : (filled($pageContent['title'] ?? null) && filled($pageContent['cta_label'] ?? null) ? 'ready' : 'attention'),
                'url' => route('offer-journeys.pages.edit', [$journey, $page]),
            ];
            if ($page->form) {
                $map[] = [
                    'label' => 'Formulaire de demande',
                    'detail' => $page->form->fields->count().' '.($page->form->fields->count() === 1 ? 'champ' : 'champs'),
                    'status' => $page->is($formPage) ? $formProgress['status'] : ($page->form->is_active ? 'ready' : 'attention'),
                    'url' => route('offer-journeys.pages.edit', [$journey, $page]).'?section=form',
                ];
            }
        }

        if ($map === []) {
            $map[] = ['label' => 'Page de présentation', 'detail' => 'À préparer', 'status' => 'attention', 'url' => $pageUrl];
        }

        $map[] = [
            'label' => 'Confirmation',
            'detail' => $confirmationPage ? 'Message affiché après la demande' : 'À préparer',
            'status' => $confirmationPage ? 'ready' : 'attention',
            'url' => $confirmationPage
                ? route('offer-journeys.pages.edit', [$journey, $confirmationPage]).'?section=after'
                : $pageUrl.'?section=after',
        ];

        foreach ($messageNodes as $node) {
            $config = $node->config_json ?? [];
            $map[] = [
                'label' => $node->name,
                'detail' => $this->delayLabel((int) ($config['delay_minutes'] ?? 0)),
                'status' => ($config['is_enabled'] ?? false) && filled($config['subject'] ?? null) && (filled($config['body'] ?? null) || ! empty(data_get($config, 'email_content.blocks')))
                    ? 'ready'
                    : (($config['is_enabled'] ?? false) ? 'attention' : 'disabled'),
                'url' => route('offer-journeys.automation', $journey).'#message-'.$node->id,
            ];
        }

        $map[] = [
            'label' => $this->destinationLabel($journey->objective),
            'detail' => $this->destinationDetail($journey, $resourceReady, $sourceReady),
            'status' => match ($journey->objective) {
                'lead_magnet' => $resourceReady ? 'ready' : 'error',
                'contact_request' => 'ready',
                default => $sourceReady ? 'ready' : 'error',
            },
            'url' => $this->destinationUrl($journey, $pages, $pageUrl),
        ];

        return $map;
    }

    private function hasUnpublishedChanges(OfferJourney $journey, $pages): bool
    {
        $version = $journey->publishedVersion;
        if (! $version) {
            return true;
        }

        $snapshot = $version->snapshot_json ?? [];
        foreach (['name', 'slug', 'objective', 'source_type', 'source_id', 'primary_conversion_type', 'timezone'] as $field) {
            if (($snapshot[$field] ?? null) != $journey->{$field}) {
                return true;
            }
        }

        if ($pages->count() !== $version->pages->count()) {
            return true;
        }
        foreach ($pages as $page) {
            $publishedPage = $version->pages->firstWhere('offer_journey_page_id', $page->id);
            if (! $publishedPage) {
                return true;
            }

            $content = $page->draft_content_json ?? [];
            if ($page->form) {
                $content['_form'] = [
                    'submit_label' => $page->form->submit_label,
                    'success_message' => $page->form->success_message,
                    'privacy_text' => $page->form->privacy_text,
                    'marketing_consent_mode' => $page->form->marketing_consent_mode,
                    'fields' => $page->form->fields->map->only([
                        'name', 'label', 'type', 'is_required', 'options_json', 'position', 'purpose',
                    ])->values()->all(),
                ];
            }

            if (hash('sha256', json_encode($content, JSON_UNESCAPED_UNICODE)) !== $publishedPage->content_hash
                || $page->slug !== $publishedPage->slug
                || $page->type !== $publishedPage->type
                || (int) $page->position !== (int) $publishedPage->position
                || $page->theme_json != $publishedPage->theme_json
                || $page->seo_title !== $publishedPage->seo_title
                || $page->seo_description !== $publishedPage->seo_description
                || (bool) $page->is_indexable !== (bool) $publishedPage->is_indexable) {
                return true;
            }
        }

        $transitionFields = ['from_page_id', 'to_page_id', 'trigger', 'condition_json', 'external_action', 'priority', 'is_fallback', 'is_active'];
        $currentTransitions = $journey->transitions
            ->sortBy(fn ($transition) => sprintf('%010d-%010d-%s', $transition->from_page_id, $transition->priority, $transition->trigger))
            ->map->only($transitionFields)
            ->values()
            ->all();
        $publishedTransitions = collect($snapshot['transitions'] ?? [])
            ->sortBy(fn (array $transition) => sprintf('%010d-%010d-%s', $transition['from_page_id'] ?? 0, $transition['priority'] ?? 0, $transition['trigger'] ?? ''))
            ->map(fn (array $transition): array => collect($transition)->only($transitionFields)->all())
            ->values()
            ->all();

        return $currentTransitions != $publishedTransitions;
    }

    private function delayLabel(int $minutes): string
    {
        $count = match (true) {
            $minutes % 1440 === 0 => (int) ($minutes / 1440),
            $minutes % 60 === 0 => (int) ($minutes / 60),
            default => $minutes,
        };

        return match (true) {
            $minutes <= 0 => 'Immédiatement',
            $minutes % 1440 === 0 => 'Après '.$count.' '.($count === 1 ? 'jour' : 'jours'),
            $minutes % 60 === 0 => 'Après '.$count.' '.($count === 1 ? 'heure' : 'heures'),
            default => 'Après '.$count.' '.($count === 1 ? 'minute' : 'minutes'),
        };
    }

    private function destinationLabel(string $objective): string
    {
        return match ($objective) {
            'appointment' => 'Prise de rendez-vous',
            'event' => 'Inscription à l’événement',
            'lead_magnet' => 'Accès à la ressource',
            'training' => 'Accès à la formation',
            'gift_voucher' => 'Choix du bon cadeau',
            default => 'Réponse du praticien',
        };
    }

    private function destinationProgressLabel(string $objective): string
    {
        return match ($objective) {
            'appointment' => 'Prestation associée',
            'event' => 'Événement associé',
            'lead_magnet' => 'Ressource prête',
            'training' => 'Formation associée',
            'gift_voucher' => 'Bons cadeaux reliés',
            default => 'Réponse prévue',
        };
    }

    private function destinationUrl(OfferJourney $journey, $pages, string $pageUrl): string
    {
        if ($journey->objective === 'lead_magnet') {
            $resourcePage = $pages->first(fn ($page) => in_array($page->type, ['content', 'opt_in'], true));

            return $resourcePage ? route('offer-journeys.pages.edit', [$journey, $resourcePage]) : $pageUrl;
        }

        return in_array($journey->objective, ['appointment', 'event', 'training', 'gift_voucher'], true)
            ? route('offer-journeys.edit', $journey)
            : $pageUrl;
    }

    private function destinationDetail(OfferJourney $journey, bool $resourceReady, bool $sourceReady): string
    {
        if ($journey->objective === 'lead_magnet') {
            return $resourceReady ? 'Le fichier ou le lien promis est prêt' : 'Ajoutez le fichier ou le lien promis';
        }
        if ($journey->objective === 'contact_request') {
            return 'La demande sera transmise au praticien';
        }

        $label = $sourceReady ? $this->sourceResolver->sourceLabel($journey, $journey->user) : null;

        return $label ? 'Relié à : '.$label : 'Choisissez la destination proposée au visiteur';
    }

    private function pageLabel(string $type, string $objective): string
    {
        return match ($type) {
            'opt_in', 'qualification' => 'Page de capture',
            'booking' => 'Page de réservation',
            'event_registration' => 'Page d’inscription',
            'training_access' => 'Présentation de la formation',
            'checkout' => 'Page de paiement',
            'content' => $objective === 'lead_magnet' ? 'Ressource' : match ($objective) {
                'appointment' => 'Page de la séance',
                'event' => 'Page de l’atelier',
                'training' => 'Page de la formation',
                'gift_voucher' => 'Page des bons cadeaux',
                default => 'Page de présentation',
            },
            default => match ($objective) {
                'appointment' => 'Page de la séance',
                'event' => 'Page de l’atelier',
                'training' => 'Page de la formation',
                'gift_voucher' => 'Page des bons cadeaux',
                default => 'Page de présentation',
            },
        };
    }
}
