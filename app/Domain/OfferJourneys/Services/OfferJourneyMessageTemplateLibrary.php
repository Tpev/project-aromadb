<?php

namespace App\Domain\OfferJourneys\Services;

use Illuminate\Support\Collection;

class OfferJourneyMessageTemplateLibrary
{
    public function all(): Collection
    {
        return collect([
            ['key' => 'request_confirmation', 'name' => 'Confirmation de demande', 'category' => 'transactional', 'subject' => 'Votre demande concernant {{offre}}', 'body' => "Bonjour {{prenom}},\n\nVotre demande a bien été reçue. Je reviendrai vers vous dès que possible.\n\n{{nom_praticien}}"],
            ['key' => 'resource_delivery', 'name' => 'Envoi d’une ressource', 'category' => 'transactional', 'subject' => 'Votre ressource : {{offre}}', 'body' => "Bonjour {{prenom}},\n\nVoici la ressource demandée : {{lien_ressource}}\n\nBonne découverte,\n{{nom_praticien}}"],
            ['key' => 'helpful_follow_up', 'name' => 'Conseil complémentaire', 'category' => 'marketing', 'subject' => 'Un repère pour aller plus loin', 'body' => "Bonjour {{prenom}},\n\nJ’espère que le premier contenu vous a été utile. Vous pouvez retrouver l’offre ici : {{lien_offre}}\n\n{{nom_praticien}}"],
            ['key' => 'event_reminder', 'name' => 'Rappel d’un événement', 'category' => 'transactional', 'subject' => 'Rappel concernant {{offre}}', 'body' => "Bonjour {{prenom}},\n\nVoici un rappel concernant {{offre}}. Retrouvez les informations utiles depuis votre confirmation Olithea.\n\n{{nom_praticien}}"],
            ['key' => 'next_step', 'name' => 'Prochaine étape facultative', 'category' => 'marketing', 'subject' => 'Souhaitez-vous poursuivre ?', 'body' => "Bonjour {{prenom}},\n\nSi vous souhaitez poursuivre, la prochaine étape est présentée ici : {{lien_offre}}\n\n{{nom_praticien}}"],
        ]);
    }
}
