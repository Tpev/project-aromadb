<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourney;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OfferJourneyPublicationPreflight
{
    public function __construct(private readonly OfferJourneySendingPolicy $sendingPolicy)
    {
    }

    public function inspect(OfferJourney $journey): array
    {
        $journey->loadMissing(['user', 'pages.form.fields', 'transitions', 'automations']);
        $errors = [];
        $warnings = [];
        $pages = $journey->pages->sortBy('position')->values();
        $first = $pages->first();
        $firstContent = $first?->draft_content_json ?? [];

        if ($pages->isEmpty()) {
            $errors['pages'] = 'Ajoutez au moins une page.';
        }
        if (blank($firstContent['title'] ?? null)) {
            $errors['title'] = 'Ajoutez un titre public à la première page.';
        }
        if ($first?->type !== 'thank_you' && blank($firstContent['cta_label'] ?? null)) {
            $errors['cta_label'] = 'Indiquez clairement l’action proposée par le bouton principal.';
        }
        if (in_array($journey->objective, ['appointment', 'event', 'training', 'gift_voucher'], true)
            && ! app(OfferJourneySourceResolver::class)->sourceAvailable($journey, $journey->user)) {
            $errors['source'] = 'L’offre Olithea associée est absente ou indisponible.';
        }
        if ($journey->objective === 'lead_magnet' && ! $pages->contains(function ($page): bool {
            $content = $page->draft_content_json ?? [];

            return filled($content['resource_url'] ?? null)
                || app(OfferJourneyResourceStorage::class)->exists($content['resource_file'] ?? null);
        })) {
            $errors['resource'] = 'Ajoutez et testez la ressource promise.';
        }

        foreach ($pages as $page) {
            if ($page->form) {
                if (blank($page->form->privacy_text)) {
                    $errors['form_privacy_'.$page->id] = 'Expliquez la finalité du formulaire de la page « '.$page->name.' ».';
                }
                foreach ($page->form->fields as $field) {
                    if (blank($field->purpose)) {
                        $errors['field_purpose_'.$field->id] = 'Indiquez pourquoi le champ « '.$field->label.' » est demandé.';
                    }
                }
            }
            if ($page->type !== 'thank_you'
                && ! $journey->transitions->contains(fn ($transition): bool => (int) $transition->from_page_id === (int) $page->id && $transition->is_active)) {
                $errors['transition_'.$page->id] = 'Définissez la suite du parcours après la page « '.$page->name.' ».';
            }
        }

        if (blank($firstContent['summary'] ?? null)) {
            $warnings['summary'] = 'Ajoutez une phrase courte pour expliquer le bénéfice concret de l’offre.';
        }
        if (blank($firstContent['audience'] ?? null)) {
            $warnings['audience'] = 'Précisez à qui cette offre est destinée.';
        }
        if (empty($firstContent['outcomes'] ?? [])) {
            $warnings['outcomes'] = 'Ajoutez deux ou trois résultats concrets et réalistes.';
        }

        $copy = $pages->map(fn ($page): string => json_encode($page->draft_content_json, JSON_UNESCAPED_UNICODE) ?: '')->implode(' ');
        $normalizedCopy = Str::lower(Str::ascii($copy));
        $riskyTerms = ['guerit', 'soigne', 'diagnostic', 'traitement garanti', 'resultat garanti', 'sans aucun risque'];
        $found = collect($riskyTerms)->filter(fn (string $term): bool => Str::contains($normalizedCopy, $term))->values();
        if ($found->isNotEmpty()) {
            $warnings['medical_claims'] = 'Relisez les formulations potentiellement médicales ou absolues : '.$found->implode(', ').'.';
        }

        $cta = Str::lower(Str::ascii((string) ($firstContent['cta_label'] ?? '')));
        $expectedWords = match ($journey->objective) {
            'appointment' => ['reserver', 'rendez-vous', 'disponibilite'],
            'event' => ['inscrire', 'participer', 'place'],
            'lead_magnet' => ['recevoir', 'telecharger', 'acceder'],
            'training' => ['decouvrir', 'formation', 'acceder'],
            'gift_voucher' => ['offrir', 'choisir', 'cadeau'],
            default => ['envoyer', 'contacter', 'demande'],
        };
        if ($cta !== '' && ! Str::contains($cta, $expectedWords)) {
            $warnings['cta_consistency'] = 'Le texte du bouton pourrait mieux annoncer ce qui se passera ensuite.';
        }

        if ($journey->automations->isNotEmpty() && ! $journey->automations->contains('status', 'active')) {
            $warnings['automation'] = 'Les messages de suivi existent mais aucune version n’est active.';
        }
        if ($reason = $this->sendingPolicy->blockingReason($journey->user, 'marketing')) {
            $warnings['sender'] = 'Les messages marketing ne partiront pas actuellement : '.app(OfferJourneyDiagnosticLabels::class)->reason($reason).'.';
        }

        return [
            'ready' => $errors === [],
            'errors' => $errors,
            'warnings' => $warnings,
            'checks' => [
                'source' => ! isset($errors['source']),
                'content' => ! isset($errors['title']) && ! isset($errors['cta_label']),
                'form' => collect(array_keys($errors))->filter(fn (string $key): bool => Str::startsWith($key, ['form_', 'field_']))->isEmpty(),
                'navigation' => collect(array_keys($errors))->filter(fn (string $key): bool => Str::startsWith($key, 'transition_'))->isEmpty(),
                'resource' => ! isset($errors['resource']),
            ],
        ];
    }

    public function assertPublishable(OfferJourney $journey): void
    {
        $result = $this->inspect($journey);
        if ($result['errors'] !== []) {
            throw ValidationException::withMessages($result['errors']);
        }
    }
}
