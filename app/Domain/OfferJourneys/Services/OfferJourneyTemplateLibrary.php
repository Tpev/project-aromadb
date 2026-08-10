<?php

namespace App\Domain\OfferJourneys\Services;

use Illuminate\Support\Collection;

class OfferJourneyTemplateLibrary
{
    public function all(): Collection
    {
        return collect([
            'discovery_session' => $this->template('Séance découverte', 'appointment', 'Présenter un premier échange et orienter vers les disponibilités.', 'Réserver un échange', 'Un premier temps pour faire le point sur votre besoin et vérifier si cet accompagnement vous correspond.', 'Cette séance s’adresse aux personnes qui souhaitent poser leurs questions avant de commencer.', ['Présenter votre situation et vos attentes', 'Comprendre le cadre de l’accompagnement', 'Choisir la prochaine étape en toute clarté'], ['Choisissez un créneau disponible', 'Recevez la confirmation Olithea', 'Retrouvez les informations pratiques dans votre espace']),
            'initial_assessment' => $this->template('Bilan initial', 'contact_request', 'Recueillir une demande structurée avant de proposer la suite.', 'Demander un bilan', 'Décrivez votre besoin afin que le praticien puisse vous répondre avec les informations utiles.', 'Ce bilan s’adresse aux personnes qui souhaitent clarifier leur demande avant un rendez-vous.', ['Une demande mieux comprise', 'Une réponse adaptée au contexte transmis', 'Une orientation vers la prochaine étape appropriée'], ['Répondez aux quelques questions', 'Le praticien étudie votre demande', 'Vous recevez une réponse par le canal choisi']),
            'in_person_workshop' => $this->template('Atelier en présentiel', 'event', 'Présenter le programme, le lieu et les modalités d’inscription.', "S'inscrire à l'atelier", 'Retrouvez le programme, les informations pratiques et les places encore disponibles.', 'Cet atelier s’adresse aux personnes intéressées par le thème présenté, dans la limite des places disponibles.', ['Un temps guidé en petit groupe', 'Des exercices ou échanges autour du thème', 'Des repères à réutiliser après l’atelier'], ['Consultez le programme', 'Choisissez votre inscription', 'Recevez la confirmation et les informations pratiques']),
            'online_conference' => $this->template('Conférence en ligne', 'event', 'Présenter une rencontre à distance et son lien de participation.', 'Réserver ma place', 'Participez à une conférence en ligne consacrée à un thème précis, depuis chez vous.', 'Cette conférence est ouverte aux personnes qui souhaitent découvrir le sujet avant d’aller plus loin.', ['Une présentation structurée', 'Un temps réservé aux questions', 'Les modalités de participation envoyées après inscription'], ['Réservez votre place', 'Recevez votre confirmation', 'Rejoignez la conférence depuis le lien transmis']),
            'free_guide' => $this->template('Guide gratuit', 'lead_magnet', 'Proposer une ressource utile après un formulaire court.', 'Accéder au guide', 'Accédez à une ressource pratique pour mieux comprendre le sujet et préparer la suite.', 'Ce guide s’adresse aux personnes qui souhaitent avancer à leur rythme avec des repères simples.', ['Des explications accessibles', 'Une méthode ou une checklist concrète', 'Des pistes pour choisir la prochaine étape'], ['Indiquez votre adresse email', 'Accédez immédiatement à la ressource', 'Retrouvez le lien sur la page de confirmation']),
            'email_program' => $this->template('Mini-programme email', 'lead_magnet', 'Envoyer une courte série de contenus pédagogiques consentis.', 'Recevoir le programme', 'Recevez plusieurs messages courts pour progresser étape par étape sur le thème choisi.', 'Ce programme s’adresse aux personnes qui préfèrent avancer progressivement, sans engagement.', ['Un premier contenu immédiatement', 'Des messages courts et espacés', 'Une possibilité de se désinscrire à tout moment'], ['Inscrivez-vous au programme', 'Recevez le premier message', 'Poursuivez à votre rythme']),
            'digital_training' => $this->template('Formation digitale', 'training', 'Présenter une formation existante et guider vers son accès.', 'Découvrir la formation', 'Consultez le programme, le format et les modalités d’accès à cette formation en ligne.', 'Cette formation s’adresse au public indiqué dans le programme et ne remplace pas un suivi médical.', ['Un programme organisé en étapes', 'Un accès aux contenus indiqués', 'Des modalités clairement présentées avant l’inscription'], ['Consultez le programme', 'Vérifiez les modalités', 'Accédez à la page de la formation']),
            'seasonal_gift' => $this->template('Bon cadeau saisonnier', 'gift_voucher', 'Présenter une idée cadeau et guider vers le bon cadeau existant.', 'Choisir un bon cadeau', 'Offrez un bon cadeau utilisable selon les prestations et conditions indiquées par le praticien.', 'Une attention destinée à une personne qui pourra choisir parmi les prestations éligibles.', ['Un bon préparé depuis Olithea', 'Des conditions visibles avant l’achat', 'Une réception selon les modalités proposées'], ['Choisissez le bon cadeau', 'Renseignez les informations demandées', 'Finalisez le paiement sécurisé']),
            'callback_request' => $this->template('Demande de rappel', 'contact_request', 'Recueillir les coordonnées et le créneau préféré.', 'Demander à être rappelé', 'Laissez vos coordonnées et quelques précisions afin que le praticien puisse vous recontacter.', 'Cette demande convient lorsque vous préférez échanger avant de réserver.', ['Un contact par le canal choisi', 'Une réponse tenant compte des informations transmises', 'Aucun rendez-vous créé automatiquement'], ['Indiquez vos coordonnées utiles', 'Précisez brièvement votre demande', 'Le praticien vous recontacte selon ses disponibilités']),
        ])->map(fn (array $template, string $key): array => ['key' => $key, ...$template]);
    }

    public function get(?string $key): ?array
    {
        return $key ? $this->all()->firstWhere('key', $key) : null;
    }

    public function forObjective(string $objective): Collection
    {
        return $this->all()->where('objective', $objective)->values();
    }

    private function template(string $name, string $objective, string $description, string $cta, string $summary, string $audience, array $outcomes, array $steps): array
    {
        return compact('name', 'objective', 'description', 'cta', 'summary', 'audience', 'outcomes', 'steps') + [
            'theme_style' => match ($objective) {
                'event', 'gift_voucher' => 'clay',
                'training' => 'forest',
                'contact_request' => 'neutral',
                default => 'olive',
            },
            'practical_details' => '',
            'faq' => [
                ['question' => 'Que se passe-t-il après ma demande ?', 'answer' => 'La prochaine étape est indiquée avant de valider. Vous recevez également une confirmation lorsque cela est prévu.'],
                ['question' => 'Puis-je revenir sur mon choix ?', 'answer' => 'Vous pouvez contacter le praticien et utiliser les liens de désinscription présents dans les messages concernés.'],
            ],
        ];
    }
}
