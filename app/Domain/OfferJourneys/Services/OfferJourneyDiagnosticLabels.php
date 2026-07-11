<?php

namespace App\Domain\OfferJourneys\Services;

class OfferJourneyDiagnosticLabels
{
    private const REASONS = [
        'completed' => 'Sequence terminee normalement',
        'converted' => 'Reservation, inscription ou achat confirme',
        'suppressed' => 'Adresse placee sur la liste de suppression',
        'unsubscribed' => 'Personne desinscrite',
        'missing_consent' => 'Consentement requis absent',
        'invalid_email' => 'Adresse email invalide',
        'source_unavailable' => 'Offre ou ressource associee indisponible',
        'contact_inactive' => 'Contact inactif ou anonymise',
        'temporarily_paused' => 'Parcours ou automatisation en pause',
        'temporarily_disabled' => 'Envoi desactive par la configuration',
        'sender_paused' => 'Tous les emails de ce praticien sont en pause',
        'marketing_paused' => 'Messages marketing de ce praticien en pause',
        'monthly_quota' => 'Limite mensuelle atteinte',
        'progressive_quota' => 'Limite progressive du compte atteinte',
        'bounce_rate' => 'Taux de rejet trop eleve',
        'complaint_rate' => 'Taux de plainte trop eleve',
        'archived' => 'Parcours archive',
        'invalid_node' => 'Etape introuvable dans la version publiee',
        'invalid_next_node' => 'Etape suivante introuvable',
        'unsupported_node' => 'Type d etape non pris en charge',
        'condition_without_target' => 'Condition sans destination',
        'invalid_condition_target' => 'Destination de condition introuvable',
    ];

    public function reason(?string $code): string
    {
        return self::REASONS[$code ?? ''] ?? ($code ? str_replace('_', ' ', $code) : 'Aucune raison renseignee');
    }

    public function recommendation(?string $code): string
    {
        return match ($code) {
            'missing_consent', 'unsubscribed', 'suppressed' => 'Ne pas relancer. Verifier la preuve de consentement avant toute nouvelle communication.',
            'invalid_email' => 'Demander une adresse correcte par un autre canal, sans modifier la preuve existante.',
            'source_unavailable' => 'Verifier que l offre associee existe encore et reste reservable ou achetable.',
            'temporarily_paused', 'temporarily_disabled', 'sender_paused', 'marketing_paused' => 'Retablir uniquement apres avoir confirme la cause de la pause.',
            'monthly_quota', 'progressive_quota' => 'Attendre le renouvellement de la limite ou faire valider une hausse progressive.',
            'bounce_rate', 'complaint_rate' => 'Laisser les envois en pause et analyser les destinataires ainsi que le consentement.',
            'invalid_node', 'invalid_next_node', 'unsupported_node', 'condition_without_target', 'invalid_condition_target' => 'Corriger puis republier une nouvelle version avant de relancer.',
            'converted', 'completed' => 'Aucune action necessaire.',
            default => 'Consulter l historique avant toute relance.',
        };
    }
}
