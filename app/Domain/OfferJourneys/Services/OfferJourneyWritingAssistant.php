<?php

namespace App\Domain\OfferJourneys\Services;

use Illuminate\Support\Str;

class OfferJourneyWritingAssistant
{
    public function review(string $title, string $summary, string $cta, string $objective): array
    {
        $cleanTitle = trim(preg_replace('/\s+/', ' ', $title));
        $cleanSummary = trim(preg_replace('/\s+/', ' ', $summary));
        $warnings = [];

        if (mb_strlen($cleanTitle) < 12) {
            $warnings[] = 'Le titre est très court. Précisez le résultat ou le format proposé.';
        } elseif (mb_strlen($cleanTitle) > 75) {
            $warnings[] = 'Le titre sera plus lisible en dessous de 75 caractères.';
        }
        if (str_word_count(Str::ascii($cleanSummary)) < 8) {
            $warnings[] = 'La présentation gagnerait à expliquer concrètement ce que la personne va trouver.';
        }

        $normalized = Str::lower(Str::ascii($cleanTitle.' '.$cleanSummary));
        $risky = collect(['guerit', 'soigne', 'diagnostic', 'resultat garanti', 'sans aucun risque'])
            ->filter(fn (string $term): bool => Str::contains($normalized, $term))->values();
        if ($risky->isNotEmpty()) {
            $warnings[] = 'Certaines formulations peuvent être comprises comme une promesse médicale ou absolue : '.$risky->implode(', ').'.';
        }
        if (Str::contains($normalized, ['boostez', 'transformez votre vie', 'solution revolutionnaire', 'bien-etre optimal'])) {
            $warnings[] = 'Remplacez les formulations générales par un résultat concret, observable et réaliste.';
        }
        if (mb_strlen(trim($cta)) < 4 || in_array(Str::lower(trim($cta)), ['cliquez ici', 'en savoir plus', 'continuer'], true)) {
            $warnings[] = 'Le bouton devrait annoncer précisément l’action suivante.';
        }

        $base = $cleanTitle !== '' ? $cleanTitle : 'cette offre';
        $titles = match ($objective) {
            'appointment' => ["Découvrir {$base} avant de réserver", "Faire le point avec {$base}", "Réserver un premier échange autour de {$base}"],
            'event' => ["Participer à {$base}", "Découvrir le programme de {$base}", "Réserver votre place pour {$base}"],
            'lead_magnet' => ["Recevoir {$base}", "Le guide pratique : {$base}", "Des repères concrets pour {$base}"],
            'training' => ["Découvrir la formation {$base}", "Le programme de {$base}", "Avancer étape par étape avec {$base}"],
            'gift_voucher' => ["Offrir {$base}", "Un bon cadeau pour découvrir {$base}", "Choisir un bon cadeau {$base}"],
            default => ["Parler de votre besoin : {$base}", "Demander des informations sur {$base}", "Être recontacté au sujet de {$base}"],
        };

        return [
            'title_suggestions' => collect($titles)->map(fn (string $value): string => Str::limit($value, 90, ''))->unique()->values()->all(),
            'warnings' => $warnings,
            'readability' => [
                'title_characters' => mb_strlen($cleanTitle),
                'summary_words' => str_word_count(Str::ascii($cleanSummary)),
                'cta_characters' => mb_strlen(trim($cta)),
            ],
        ];
    }
}
