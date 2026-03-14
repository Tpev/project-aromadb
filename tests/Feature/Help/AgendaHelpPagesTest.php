<?php

it('agenda help pages are publicly reachable', function (string $routeName, string $expectedHeading) {
    $response = $this->get(route($routeName));

    $response->assertOk();
    $response->assertSee($expectedHeading);
})->with([
    ['aide.agenda.creer-rendez-vous', 'Créer un rendez-vous en ligne depuis le Portail Pro'],
    ['aide.agenda.configurer-disponibilites', 'Configurer ses disponibilités et horaires types'],
    ['aide.agenda.gerer-indisponibilites', 'Gérer ses indisponibilités (congés, absences, fermetures)'],
    ['aide.agenda.duree-prestation-temps-de-pause', 'Définir la durée d’une prestation et le temps de pause'],
    ['aide.agenda.creer-atelier-evenement', 'Créer un atelier ou un événement dans l’agenda'],
    ['aide.agenda.synchroniser-calendrier', 'Synchroniser Google Calendar, Apple iCloud ou Outlook'],
]);

it('agenda feature page exposes links to all agenda help pages', function () {
    $response = $this->get(route('features.agenda'));
    $response->assertOk();

    $response->assertSee(url('/aide/agenda/creer-un-rendez-vous-en-ligne'));
    $response->assertSee(url('/aide/agenda/configurer-disponibilites'));
    $response->assertSee(url('/aide/agenda/gerer-indisponibilites'));
    $response->assertSee(url('/aide/agenda/duree-prestation-temps-de-pause'));
    $response->assertSee(url('/aide/agenda/creer-un-atelier-ou-evenement'));
    $response->assertSee(url('/aide/agenda/synchroniser-calendrier'));
});
