<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Models\User;
use Illuminate\Support\Str;

class OfferJourneyMessagePreview
{
    public function render(OfferJourney $journey, User $user, string $subject, string $body): array
    {
        $values = [
            '{{prenom}}' => 'Camille',
            '{{offre}}' => $journey->name,
            '{{nom_praticien}}' => $user->company_name ?: $user->name,
            '{{lien_offre}}' => route('offer-journeys.public.show', [
                'therapist' => $user,
                'journeySlug' => $journey->slug,
            ]),
            '{{lien_ressource}}' => 'https://olithea.fr/exemple-ressource',
        ];
        $renderedSubject = strtr($subject, $values);
        $renderedBody = strtr($body, $values);
        $warnings = [];
        if (blank($subject)) {
            $warnings[] = 'Ajoutez un objet au message.';
        }
        if (blank($body)) {
            $warnings[] = 'Ajoutez le contenu du message.';
        }
        if (! Str::contains($body, ['{{lien_offre}}', '{{lien_ressource}}']) && Str::contains(Str::lower($body), ['clique', 'retrouver', 'decouvrir', 'réserver'])) {
            $warnings[] = 'Le texte semble annoncer un lien, mais aucune variable de lien n’est présente.';
        }
        preg_match_all('/{{[^}]+}}/', $subject.' '.$body, $matches);
        $unknown = collect($matches[0] ?? [])->diff(array_keys($values))->unique()->values();
        if ($unknown->isNotEmpty()) {
            $warnings[] = 'Variables inconnues : '.$unknown->implode(', ').'.';
        }

        return [
            'subject' => $renderedSubject,
            'body' => $renderedBody,
            'body_html' => nl2br(e($renderedBody)),
            'warnings' => $warnings,
            'variables' => $values,
        ];
    }
}
