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
        ->assertSee('images/features/parcours-offre-resultats.webp', false)
        ->assertSee(route('features.capture-page'), false)
        ->assertSee(route('guides.sales-funnel-practitioner'), false)
        ->assertSee(route('guides.lead-magnet-practitioner'), false);

    preg_match('/<main class="fm-page">(.*)<\/main>/s', $response->getContent(), $page);

    expect(substr_count($page[1] ?? '', '<section'))->toBe(7)
        ->and(substr_count($page[1] ?? '', '<h1>'))->toBe(1);
});

it('renders the capture page as a distinct commercial page', function () {
    $response = $this->get(route('features.capture-page'));

    $response->assertOk()
        ->assertSee('Créez une page de capture reliée à votre activité.')
        ->assertSee('La demande et le marketing restent deux choix séparés.')
        ->assertSee('Chaque demande arrive là où elle doit continuer.')
        ->assertSee('images/features/parcours-offre-capture-mobile.webp', false)
        ->assertSee('images/features/parcours-offre-formulaire.webp', false)
        ->assertSee(route('features.offer-journeys'), false)
        ->assertSee(route('guides.sales-funnel-practitioner'), false)
        ->assertSee(route('guides.lead-magnet-practitioner'), false)
        ->assertSee('<link rel="canonical" href="'.route('features.capture-page').'"', false)
        ->assertSee('SoftwareApplication', false)
        ->assertSee('FAQPage', false)
        ->assertDontSee('Jusqu’à trois questions personnalisées')
        ->assertDontSee('clients garantis');

    preg_match('/<main class="fm-page">(.*)<\/main>/s', $response->getContent(), $page);

    expect(substr_count($page[1] ?? '', '<section'))->toBe(7)
        ->and(substr_count($page[1] ?? '', '<h1>'))->toBe(1);
});

it('renders the ethical sales funnel guide as substantial editorial content', function () {
    $response = $this->get(route('guides.sales-funnel-practitioner'));

    $response->assertOk()
        ->assertSee('Tunnel de vente pour praticien : définition, étapes et exemples simples')
        ->assertSee('Construire un tunnel compatible avec une pratique éthique')
        ->assertSee('Ne collectez pas de données de santé dans un formulaire marketing')
        ->assertSee('il ne garantit ni trafic, ni rendez-vous, ni chiffre d’affaires')
        ->assertSee('images/features/parcours-offre-creation.webp', false)
        ->assertSee(route('features.offer-journeys'), false)
        ->assertSee(route('features.capture-page'), false)
        ->assertSee(route('guides.lead-magnet-practitioner'), false)
        ->assertSee('<link rel="canonical" href="'.route('guides.sales-funnel-practitioner').'"', false)
        ->assertSee('"@type":"Article"', false)
        ->assertSee('FAQPage', false)
        ->assertDontSee('guérison garantie');

    preg_match('/<article class="mg-article">(.*)<\/article>/s', $response->getContent(), $article);

    expect(substr_count($response->getContent(), '<h1>'))->toBe(1)
        ->and(count(preg_split('/\s+/u', trim(strip_tags($article[1] ?? '')))))->toBeGreaterThan(900);
});

it('renders the lead magnet guide with practical formats and safe claims', function () {
    $response = $this->get(route('guides.lead-magnet-practitioner'));

    $response->assertOk()
        ->assertSee('Lead magnet pour praticien : idées et exemples pour attirer les bonnes demandes')
        ->assertSee('Le guide PDF court')
        ->assertSee('L’audio court')
        ->assertSee('La checklist')
        ->assertSee('Le mini-programme')
        ->assertSee('Ne qualifiez pas un prospect avec des données de santé')
        ->assertSee('les campagnes et envois automatiques dépendent de l’activation du compte')
        ->assertSee('images/features/parcours-offre-creation.webp', false)
        ->assertSee('images/features/parcours-offre-capture-mobile.webp', false)
        ->assertSee(route('features.offer-journeys'), false)
        ->assertSee(route('features.capture-page'), false)
        ->assertSee(route('guides.sales-funnel-practitioner'), false)
        ->assertSee('<link rel="canonical" href="'.route('guides.lead-magnet-practitioner').'"', false)
        ->assertSee('"@type":"Article"', false)
        ->assertSee('FAQPage', false)
        ->assertDontSee('revenu garanti');

    preg_match('/<article class="mg-article">(.*)<\/article>/s', $response->getContent(), $article);

    expect(substr_count($response->getContent(), '<h1>'))->toBe(1)
        ->and(count(preg_split('/\s+/u', trim(strip_tags($article[1] ?? '')))))->toBeGreaterThan(1000);
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

it('links the marketing architecture from the feature index and public navigation', function () {
    $this->get(route('features.index'))
        ->assertOk()
        ->assertSee(route('features.offer-journeys'), false)
        ->assertSee(route('features.capture-page'), false)
        ->assertSee(route('guides.sales-funnel-practitioner'), false)
        ->assertSee(route('features.e-invoicing'), false);

    $this->get(route('prolanding'))
        ->assertOk()
        ->assertSee(route('features.offer-journeys'), false)
        ->assertSee(route('guides.sales-funnel-practitioner'), false)
        ->assertSee(route('guides.lead-magnet-practitioner'), false)
        ->assertSee(route('features.e-invoicing'), false);
});

it('includes the marketing architecture in the generated sitemap source', function () {
    $source = file_get_contents(app_path('Console/Commands/GenerateSitemap.php'));

    foreach ([
        '/fonctionnalites/parcours-offre',
        '/fonctionnalites/page-de-capture',
        '/guides/tunnel-de-vente-praticien',
        '/guides/lead-magnet-praticien',
    ] as $path) {
        expect($source)->toContain($path);
    }
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
