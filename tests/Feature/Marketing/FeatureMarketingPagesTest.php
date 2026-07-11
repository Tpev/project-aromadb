<?php

it('renders the offer journey marketing page publicly with its SEO contract', function () {
    $response = $this->get(route('features.offer-journeys'));

    $response->assertOk()
        ->assertSee('Transformez un simple lien en rendez-vous, inscription ou demande qualifiée.')
        ->assertSee('page de capture')
        ->assertSee('Un tunnel de vente, c’est simplement un chemin vers une action.')
        ->assertSee('Créez votre parcours en trois étapes.')
        ->assertSee('Une ouverture progressive')
        ->assertDontSee('Construisez une suite logique, pas une impasse.')
        ->assertDontSee('Pipeline et tâches de suivi.')
        ->assertSee(route('register-pro'), false)
        ->assertSee('<link rel="canonical" href="'.route('features.offer-journeys').'"', false)
        ->assertSee('application/ld+json', false)
        ->assertSee('FAQPage', false)
        ->assertSee('SoftwareApplication', false)
        ->assertSee('images/features/parcours-offre-creation.webp', false)
        ->assertSee('images/features/parcours-offre-capture-mobile.webp', false)
        ->assertSee('images/features/parcours-offre-resultats.webp', false);

    preg_match('/<main class="fm-page">(.*)<\/main>/s', $response->getContent(), $page);

    expect(substr_count($page[1] ?? '', '<section'))->toBe(7)
        ->and(substr_count($page[1] ?? '', '<h1>'))->toBe(1);
});

it('renders the electronic invoice marketing page publicly without overstating its scope', function () {
    $response = $this->get(route('features.e-invoicing'));

    $response->assertOk()
        ->assertSee('Factures électroniques : soyez prêt pour septembre 2026.')
        ->assertSee('1er septembre 2026')
        ->assertSee('https://www.economie.gouv.fr/tout-savoir-sur-la-facturation-electronique-pour-les-entreprises', false)
        ->assertSee('factures électroniques d’achat')
        ->assertSee('images/facturation-electronique-solution-compatible.png', false)
        ->assertDontSee('SUPER PDP')
        ->assertDontSee('Non présenté comme disponible')
        ->assertSee(route('register-pro'), false)
        ->assertSee('<link rel="canonical" href="'.route('features.e-invoicing').'"', false)
        ->assertSee('application/ld+json', false)
        ->assertSee('FAQPage', false)
        ->assertSee('SoftwareApplication', false)
        ->assertSee('images/features/facturation-electronique-inbox.webp', false);
});

it('links both new pages from the feature index and public navigation', function () {
    $this->get(route('features.index'))
        ->assertOk()
        ->assertSee(route('features.offer-journeys'), false)
        ->assertSee(route('features.e-invoicing'), false);

    $this->get(route('prolanding'))
        ->assertOk()
        ->assertSee(route('features.offer-journeys'), false)
        ->assertSee(route('features.e-invoicing'), false);
});

it('ships the real product visuals used by the feature pages', function () {
    foreach ([
        'parcours-offre-creation.webp',
        'parcours-offre-formulaire.webp',
        'parcours-offre-capture-mobile.webp',
        'parcours-offre-resultats.webp',
        'facturation-electronique-inbox.webp',
    ] as $image) {
        expect(public_path('images/features/'.$image))->toBeFile();
    }

    expect(public_path('images/facturation-electronique-solution-compatible.png'))->toBeFile();
});
