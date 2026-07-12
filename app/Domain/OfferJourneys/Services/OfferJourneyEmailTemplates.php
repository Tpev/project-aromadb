<?php

namespace App\Domain\OfferJourneys\Services;

use Illuminate\Support\Str;

class OfferJourneyEmailTemplates
{
    public function all(): array
    {
        return [
            $this->template('discovery', 'Séance découverte', 'Présenter un premier échange sans engagement.', 'Faisons connaissance', 'Je vous propose un premier échange pour comprendre votre besoin et voir si mon accompagnement peut vous convenir.', 'Découvrir la séance'),
            $this->template('workshop', 'Inscription à un atelier', 'Présenter un atelier et ses informations pratiques.', 'Un atelier pour prendre le temps', 'Découvrez le prochain atelier, son déroulement et les informations utiles avant de vous inscrire.', 'Voir l’atelier', true),
            $this->template('resource', 'Ressource gratuite', 'Partager un guide, un audio ou une checklist.', 'Votre ressource est prête', 'Voici une ressource pratique que vous pourrez consulter à votre rythme.', 'Accéder à la ressource'),
            $this->template('mini_program', 'Mini-programme', 'Introduire une courte série de contenus.', 'Commençons simplement', 'Je vous accompagne avec quelques repères progressifs et faciles à mettre en pratique.', 'Découvrir le programme'),
            $this->template('gift', 'Bon cadeau', 'Présenter une attention à offrir.', 'Une attention qui a du sens', 'Vous pouvez offrir une prestation à une personne de votre choix, avec un bon cadeau simple à utiliser.', 'Choisir un bon cadeau'),
            $this->template('follow_up', 'Relance simple', 'Reprendre contact avec tact.', 'Avez-vous eu le temps de regarder ?', 'Je me permets de revenir vers vous au sujet de {{offre}}. Je reste disponible si vous avez une question.', 'Revoir les informations'),
            $this->template('news', 'Actualités du cabinet', 'Partager une information ou une nouveauté.', 'Les nouvelles du cabinet', 'Voici les informations utiles et les prochaines dates à retenir.', 'En savoir plus'),
        ];
    }

    private function template(string $key, string $name, string $description, string $title, string $paragraph, string $button, bool $details = false): array
    {
        $blocks = [
            $this->block('heading', ['text' => $title, 'level' => 'h1', 'align' => 'left']),
            $this->block('paragraph', ['text' => "Bonjour {{prenom}},\n\n$paragraph", 'align' => 'left']),
        ];
        if ($details) {
            $blocks[] = $this->block('details', ['title' => 'Informations pratiques', 'text' => "Date : à compléter\nLieu : à compléter"]);
        }
        $blocks[] = $this->block('button', ['label' => $button, 'url' => '{{lien_offre}}', 'variant' => 'filled', 'align' => 'left']);
        $blocks[] = $this->block('signature', ['text' => '{{nom_praticien}}', 'show_contact' => true]);

        return compact('key', 'name', 'description') + ['subject' => $title, 'preheader' => $description, 'content' => ['blocks' => $blocks]];
    }

    private function block(string $type, array $data): array
    {
        return ['id' => (string) Str::uuid(), 'type' => $type, 'data' => $data];
    }
}
