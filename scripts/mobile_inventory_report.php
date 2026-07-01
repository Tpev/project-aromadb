<?php

declare(strict_types=1);

$root = dirname(__DIR__);

function relative_path(string $path, string $root): string
{
    $path = str_replace('\\', '/', realpath($path) ?: $path);
    $root = str_replace('\\', '/', realpath($root) ?: $root);

    return ltrim(str_replace($root, '', $path), '/');
}

function list_files(string $dir, string $pattern): array
{
    if (! is_dir($dir)) {
        return [];
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
    );

    $files = [];
    foreach ($iterator as $file) {
        if ($file->isFile() && fnmatch($pattern, $file->getFilename())) {
            $files[] = $file->getPathname();
        }
    }

    sort($files);

    return $files;
}

function module_from_uri(string $uri, ?string $name): string
{
    $uri = trim($uri, '/');
    $first = $uri === '' ? 'home' : explode('/', $uri)[0];

    if ($name && str_starts_with($name, 'mobile.')) {
        return 'mobile';
    }

    return match ($first) {
        'admin' => 'admin',
        'api' => 'api',
        'client', 'client-login', 'client-password', 'client-forgot-password' => 'client_portal',
        'mobile' => 'mobile',
        'dashboard-pro', 'appointments', 'clients', 'client-profiles', 'invoices', 'products',
        'availabilities', 'practice-locations', 'questionnaires', 'metrics', 'events', 'audiences',
        'newsletters', 'communities', 'documents', 'emargement', 'session-notes', 'receipts',
        'inventory-items', 'pack-products', 'digital-trainings', 'gift-vouchers', 'pro' => 'pro_authenticated',
        default => 'public_or_marketing',
    };
}

function view_module(string $view): string
{
    $first = explode('/', $view)[0] ?? 'root';

    return match ($first) {
        'admin' => 'admin',
        'client' => 'client_portal',
        'mobile' => 'mobile',
        'auth' => 'auth',
        'emails', 'vendor' => 'system_templates',
        'layouts', 'components', 'partials' => 'shared_ui',
        'dashboard-pro', 'appointments', 'client_profiles', 'invoices', 'products', 'availabilities',
        'practice_locations', 'questionnaires', 'metrics', 'events', 'audiences', 'newsletters',
        'communities', 'documents', 'emargement', 'session_notes', 'receipts', 'inventory_items',
        'pack_products', 'digital-trainings', 'gift-vouchers', 'pro' => 'pro_authenticated',
        default => 'public_or_marketing',
    };
}

function extract_model_relationships(string $file): array
{
    $source = file_get_contents($file);
    if ($source === false) {
        return [];
    }

    preg_match_all(
        '/public\s+function\s+(\w+)\s*\([^)]*\)\s*(?::\s*[^{]+)?\{(?P<body>.*?)\n\s*\}/s',
        $source,
        $matches,
        PREG_SET_ORDER
    );

    $relations = [];
    foreach ($matches as $match) {
        $body = $match['body'] ?? '';
        if (preg_match('/->(hasOne|hasMany|belongsTo|belongsToMany|morphMany|morphOne|morphTo|morphedByMany|morphToMany|hasManyThrough|hasOneThrough)\s*\(/', $body, $relationMatch)) {
            $target = null;
            if (preg_match('/::class/', $body) && preg_match('/([A-Za-z_\\\\]+)::class/', $body, $targetMatch)) {
                $target = $targetMatch[1];
            }
            $relations[] = [
                'method' => $match[1],
                'type' => $relationMatch[1],
                'target' => $target,
            ];
        }
    }

    return $relations;
}

function route_prefix_matches(array $route, array $namePrefixes = [], array $uriPrefixes = []): bool
{
    $name = (string) ($route['name'] ?? '');
    $uri = trim((string) ($route['uri'] ?? ''), '/');

    foreach ($namePrefixes as $prefix) {
        $prefix = trim($prefix, '.');
        if ($prefix !== '' && ($name === $prefix || str_starts_with($name, $prefix . '.'))) {
            return true;
        }
    }

    foreach ($uriPrefixes as $prefix) {
        $prefix = trim($prefix, '/');
        if ($prefix === '') {
            continue;
        }

        if ($uri === $prefix || str_starts_with($uri, $prefix . '/')) {
            return true;
        }
    }

    return false;
}

function view_prefix_matches(array $views, array $prefixes): array
{
    $matches = [];

    foreach ($views as $view) {
        $normalized = str_starts_with($view, 'mobile/') ? substr($view, 7) : $view;

        foreach ($prefixes as $prefix) {
            $prefix = trim($prefix, '/');
            if ($prefix === '') {
                continue;
            }

            if (
                $normalized === $prefix ||
                $normalized === $prefix . '.blade.php' ||
                str_starts_with($normalized, $prefix . '/')
            ) {
                $matches[] = $view;
                break;
            }
        }
    }

    return array_values(array_unique($matches));
}

function mobile_controller_mode(array $mobileRoutes): string
{
    if ($mobileRoutes === []) {
        return 'Missing';
    }

    $usesDedicatedMobileController = false;
    $usesSharedController = false;
    $usesRouteOnlyAction = false;

    foreach ($mobileRoutes as $route) {
        $action = (string) ($route['action'] ?? '');

        if (str_contains($action, 'App\\Http\\Controllers\\Mobile\\')) {
            $usesDedicatedMobileController = true;
            continue;
        }

        if (str_contains($action, 'App\\Http\\Controllers\\')) {
            $usesSharedController = true;
            continue;
        }

        $usesRouteOnlyAction = true;
    }

    if ($usesDedicatedMobileController && $usesSharedController) {
        return 'Dedicated + shared';
    }

    if ($usesDedicatedMobileController) {
        return 'Dedicated mobile';
    }

    if ($usesSharedController) {
        return 'Shared controller';
    }

    return $usesRouteOnlyAction ? 'Route-only' : 'Other';
}

$routeJson = shell_exec('php artisan route:list --json 2>NUL');
$routes = json_decode($routeJson ?: '[]', true);
if (! is_array($routes)) {
    $routes = [];
}

$views = array_map(
    fn (string $path): string => relative_path($path, $root . '/resources/views'),
    list_files($root . '/resources/views', '*.blade.php')
);

$mobileViews = array_values(array_filter($views, fn (string $view): bool => str_starts_with($view, 'mobile/')));

$models = list_files($root . '/app/Models', '*.php');
$controllers = list_files($root . '/app/Http/Controllers', '*.php');
$migrations = list_files($root . '/database/migrations', '*.php');

$routesByModule = [];
foreach ($routes as $route) {
    $module = module_from_uri((string) ($route['uri'] ?? ''), $route['name'] ?? null);
    $routesByModule[$module][] = $route;
}
ksort($routesByModule);

$viewsByModule = [];
foreach ($views as $view) {
    $viewsByModule[view_module($view)][] = $view;
}
ksort($viewsByModule);

$modelRows = [];
foreach ($models as $model) {
    $relative = relative_path($model, $root);
    $modelName = basename($model, '.php');
    $relations = extract_model_relationships($model);
    $modelRows[] = [
        'name' => $modelName,
        'path' => $relative,
        'relations' => $relations,
    ];
}

$practitionerFeatureCatalog = [
    [
        'label' => 'Dashboard PRO',
        'web_names' => ['dashboard-pro'],
        'web_uris' => ['dashboard-pro'],
        'mobile_names' => ['mobile.dashboard'],
        'mobile_views' => ['dashboard-pro'],
    ],
    [
        'label' => 'Rendez-vous',
        'web_names' => ['appointments'],
        'web_uris' => ['appointments'],
        'mobile_names' => ['mobile.appointments', 'mobile.rdv'],
        'mobile_views' => ['appointments'],
    ],
    [
        'label' => 'Clients',
        'web_names' => ['clients', 'client-profiles'],
        'web_uris' => ['clients', 'client-profiles'],
        'mobile_names' => ['mobile.clients'],
        'mobile_views' => ['clients'],
    ],
    [
        'label' => 'Factures et devis',
        'web_names' => ['invoices', 'quotes'],
        'web_uris' => ['invoices', 'devis'],
        'mobile_names' => ['mobile.invoices', 'mobile.quotes'],
        'mobile_views' => ['invoices'],
    ],
    [
        'label' => 'Prestations',
        'web_names' => ['products'],
        'web_uris' => ['products'],
        'mobile_names' => ['mobile.products'],
        'mobile_views' => ['products'],
    ],
    [
        'label' => 'Disponibilites',
        'web_names' => ['availabilities', 'special-availabilities'],
        'web_uris' => ['availabilities', 'special-availabilities'],
        'mobile_names' => ['mobile.availabilities'],
        'mobile_views' => ['availabilities'],
    ],
    [
        'label' => 'Lieux de pratique',
        'web_names' => ['practice-locations'],
        'web_uris' => ['practice-locations'],
        'mobile_names' => ['mobile.practice-locations'],
        'mobile_views' => ['practice-locations'],
    ],
    [
        'label' => 'Questionnaires',
        'web_names' => ['questionnaires'],
        'web_uris' => ['questionnaires'],
        'mobile_names' => ['mobile.questionnaires'],
        'mobile_views' => ['questionnaires'],
    ],
    [
        'label' => 'Evenements',
        'web_names' => ['events'],
        'web_uris' => ['events'],
        'mobile_names' => ['mobile.events'],
        'mobile_views' => ['events'],
    ],
    [
        'label' => 'Documents et signatures',
        'web_names' => ['documents'],
        'web_uris' => ['documents'],
        'mobile_names' => ['mobile.documents'],
        'mobile_views' => ['documents'],
    ],
    [
        'label' => 'Emargements',
        'web_names' => ['emargement'],
        'web_uris' => ['emargement'],
        'mobile_names' => ['mobile.emargements'],
        'mobile_views' => ['emargements'],
    ],
    [
        'label' => 'Recettes',
        'web_names' => ['receipts'],
        'web_uris' => ['receipts'],
        'mobile_names' => ['mobile.receipts'],
        'mobile_views' => ['receipts'],
    ],
    [
        'label' => 'Stock',
        'web_names' => ['inventory-items'],
        'web_uris' => ['inventory-items'],
        'mobile_names' => ['mobile.inventory'],
        'mobile_views' => ['inventory'],
    ],
    [
        'label' => 'Entreprises',
        'web_names' => ['corporate-clients'],
        'web_uris' => ['corporate-clients'],
        'mobile_names' => ['mobile.corporate-clients'],
        'mobile_views' => ['corporate-clients'],
    ],
    [
        'label' => 'Packs',
        'web_names' => ['pack-products', 'pack-purchases'],
        'web_uris' => ['pack-products', 'pack-purchases'],
        'mobile_names' => ['mobile.packs'],
        'mobile_views' => ['packs'],
    ],
    [
        'label' => 'Bons cadeaux',
        'web_names' => ['pro.gift-vouchers', 'gift-vouchers'],
        'web_uris' => ['dashboard-pro/bons-cadeaux'],
        'mobile_names' => ['mobile.gift-vouchers'],
        'mobile_views' => ['gift-vouchers'],
    ],
    [
        'label' => 'Factures recues',
        'web_names' => ['received-invoices'],
        'web_uris' => ['received-invoices'],
        'mobile_names' => ['mobile.received-invoices'],
        'mobile_views' => ['received-invoices'],
    ],
    [
        'label' => 'Formations digitales',
        'web_names' => ['digital-trainings'],
        'web_uris' => ['digital-trainings'],
        'mobile_names' => ['mobile.digital-trainings'],
        'mobile_views' => ['digital-trainings'],
    ],
    [
        'label' => 'Communautes',
        'web_names' => ['communities'],
        'web_uris' => ['communautes'],
        'mobile_names' => ['mobile.communities'],
        'mobile_views' => ['communities'],
    ],
    [
        'label' => 'Audiences',
        'web_names' => ['audiences'],
        'web_uris' => ['audiences'],
        'mobile_names' => ['mobile.audiences'],
        'mobile_views' => ['audiences'],
    ],
    [
        'label' => 'Newsletters',
        'web_names' => ['newsletters'],
        'web_uris' => ['newsletters'],
        'mobile_names' => ['mobile.newsletters'],
        'mobile_views' => ['newsletters'],
    ],
    [
        'label' => 'Avis Google',
        'web_names' => ['pro.google-reviews'],
        'web_uris' => ['dashboard-pro/avis-google'],
        'mobile_names' => ['mobile.google-reviews'],
        'mobile_views' => ['google-reviews'],
    ],
    [
        'label' => 'Parrainage',
        'web_names' => ['pro.referrals'],
        'web_uris' => ['pro/referrals'],
        'mobile_names' => ['mobile.referrals'],
        'mobile_views' => ['referrals'],
    ],
    [
        'label' => 'Profil praticien',
        'web_names' => ['profile'],
        'web_uris' => ['profile'],
        'mobile_names' => ['mobile.profile'],
        'mobile_views' => ['profile'],
    ],
    [
        'label' => 'Abonnement',
        'web_names' => ['subscription'],
        'web_uris' => ['subscription', 'abonnement'],
        'mobile_names' => ['mobile.subscription'],
        'mobile_views' => ['subscription'],
    ],
    [
        'label' => 'Statistiques',
        'web_names' => ['client_profiles.metrics'],
        'web_uris' => [],
        'mobile_names' => ['mobile.metrics'],
        'mobile_views' => ['metrics'],
    ],
    [
        'label' => 'Notes de seance',
        'web_names' => ['session-notes', 'session-note-templates'],
        'web_uris' => ['session-notes', 'session-note-templates'],
        'mobile_names' => ['mobile.session-notes'],
        'mobile_views' => ['session-notes'],
    ],
];

$featureCoverageRows = [];
foreach ($practitionerFeatureCatalog as $feature) {
    $webRoutes = array_values(array_filter(
        $routes,
        fn (array $route): bool => ! str_starts_with((string) ($route['name'] ?? ''), 'mobile.')
            && ! str_starts_with(trim((string) ($route['uri'] ?? ''), '/'), 'mobile/')
            && route_prefix_matches($route, $feature['web_names'], $feature['web_uris'])
    ));
    $featureMobileRoutes = array_values(array_filter(
        $routes,
        fn (array $route): bool => route_prefix_matches($route, $feature['mobile_names'], ['mobile'])
            && route_prefix_matches($route, $feature['mobile_names'], [])
    ));
    $featureMobileViews = view_prefix_matches($mobileViews, $feature['mobile_views']);
    $mode = mobile_controller_mode($featureMobileRoutes);
    $status = match (true) {
        count($featureMobileRoutes) > 0 && count($featureMobileViews) > 0 => 'Covered',
        count($featureMobileRoutes) > 0 => 'Route-only',
        count($webRoutes) > 0 => 'Gap',
        default => 'No web route',
    };

    $featureCoverageRows[] = [
        'label' => $feature['label'],
        'web_count' => count($webRoutes),
        'mobile_count' => count($featureMobileRoutes),
        'view_count' => count($featureMobileViews),
        'mode' => $mode,
        'status' => $status,
    ];
}

$clientPortalFeatureCatalog = [
    [
        'label' => 'Connexion et accueil client',
        'web_names' => ['client.login', 'client.home'],
        'web_uris' => ['client/login', 'client/home'],
        'mobile_names' => ['mobile.client.login', 'mobile.client.home'],
        'mobile_views' => ['client/auth', 'client/home.blade.php'],
    ],
    [
        'label' => 'Messagerie client',
        'web_names' => ['client.messages'],
        'web_uris' => ['client/messages'],
        'mobile_names' => ['mobile.client.messages'],
        'mobile_views' => ['client/messages'],
    ],
    [
        'label' => 'Documents client',
        'web_names' => ['client.documents', 'client.files', 'client_files'],
        'web_uris' => ['client/documents', 'client/files'],
        'mobile_names' => ['mobile.client.files'],
        'mobile_views' => ['client/home.blade.php'],
    ],
    [
        'label' => 'Factures client',
        'web_names' => ['client.invoices'],
        'web_uris' => ['client/invoices'],
        'mobile_names' => ['mobile.client.invoices'],
        'mobile_views' => ['client/home.blade.php'],
    ],
    [
        'label' => 'Communautes client',
        'web_names' => ['client.communities'],
        'web_uris' => ['client/communautes'],
        'mobile_names' => ['mobile.client.communities'],
        'mobile_views' => ['client/communities'],
    ],
];

$clientPortalCoverageRows = [];
foreach ($clientPortalFeatureCatalog as $feature) {
    $webRoutes = array_values(array_filter(
        $routes,
        fn (array $route): bool => ! str_starts_with((string) ($route['name'] ?? ''), 'mobile.')
            && ! str_starts_with(trim((string) ($route['uri'] ?? ''), '/'), 'mobile/')
            && route_prefix_matches($route, $feature['web_names'], $feature['web_uris'])
    ));
    $featureMobileRoutes = array_values(array_filter(
        $routes,
        fn (array $route): bool => route_prefix_matches($route, $feature['mobile_names'], ['mobile/client'])
            && route_prefix_matches($route, $feature['mobile_names'], [])
    ));
    $featureMobileViews = view_prefix_matches($mobileViews, $feature['mobile_views']);
    $mode = mobile_controller_mode($featureMobileRoutes);
    $status = match (true) {
        count($featureMobileRoutes) > 0 && count($featureMobileViews) > 0 => 'Covered',
        count($featureMobileRoutes) > 0 => 'Route-only',
        count($webRoutes) > 0 => 'Gap',
        default => 'No web route',
    };

    $clientPortalCoverageRows[] = [
        'label' => $feature['label'],
        'web_count' => count($webRoutes),
        'mobile_count' => count($featureMobileRoutes),
        'view_count' => count($featureMobileViews),
        'mode' => $mode,
        'status' => $status,
    ];
}

$sharedMobileRoutes = array_values(array_filter(
    $routesByModule['mobile'] ?? [],
    function (array $route): bool {
        $action = (string) ($route['action'] ?? '');

        return str_contains($action, 'App\\Http\\Controllers\\')
            && ! str_contains($action, 'App\\Http\\Controllers\\Mobile\\');
    }
));

$mobileControllerRows = [];
foreach ($controllers as $controller) {
    $relative = relative_path($controller, $root);
    if (! str_starts_with(str_replace('\\', '/', $relative), 'app/Http/Controllers/Mobile/')) {
        continue;
    }

    $controllerName = basename($controller, '.php');
    $controllerClass = 'App\\Http\\Controllers\\Mobile\\' . $controllerName;
    $routeCount = count(array_filter(
        $routes,
        fn (array $route): bool => str_starts_with((string) ($route['action'] ?? ''), $controllerClass . '@')
    ));

    $mobileControllerRows[] = [
        'name' => $controllerName,
        'routes' => $routeCount,
        'status' => $routeCount > 0 ? 'Used by routes' : 'No route usage detected',
        'path' => $relative,
    ];
}

$scopeDecisionRows = [
    [
        'module' => 'pro_authenticated',
        'decision' => 'In Android app scope',
        'treatment' => 'Dedicated `/mobile` routes and mobile Blade screens for practitioner workflows.',
        'rationale' => 'This is the daily practitioner product: agenda, clients, billing, products, documents, marketing, and settings.',
    ],
    [
        'module' => 'mobile',
        'decision' => 'Canonical Android surface',
        'treatment' => 'Packaged by Capacitor and verified at phone viewport.',
        'rationale' => 'This route family is the mobile app shell and should stay isolated from desktop routes.',
    ],
    [
        'module' => 'client_portal',
        'decision' => 'In Android app scope as isolated client role',
        'treatment' => 'Dedicated `/mobile/client` routes, client-guard auth, and mobile Blade screens for client account workflows.',
        'rationale' => 'Client login, messages, documents, invoices, and communities are useful in the app, but must stay separate from practitioner navigation and web routes.',
    ],
    [
        'module' => 'public_or_marketing',
        'decision' => 'Selective app scope',
        'treatment' => 'Mobile app covers practitioner search, therapist profile, booking, and appointment confirmation. Marketing/blog/legal pages remain responsive web.',
        'rationale' => 'Only transactional public journeys belong inside the app; SEO and content pages should stay web-first.',
    ],
    [
        'module' => 'admin',
        'decision' => 'Out of Android app scope for now',
        'treatment' => 'Keep admin web-first unless a specific mobile admin use case is requested.',
        'rationale' => 'Admin CRM, finance, imports, design templates, and impersonation are operational desktop workflows with higher risk.',
    ],
    [
        'module' => 'api',
        'decision' => 'Support surface',
        'treatment' => 'No Blade mobile screen required; verify only when mobile UI depends on the endpoint.',
        'rationale' => 'API routes are consumed by screens/integrations rather than rendered as standalone mobile pages.',
    ],
];

$bladeInventoryRows = [];
foreach ($views as $view) {
    $bladeInventoryRows[] = [
        'family' => view_module($view),
        'view' => $view,
    ];
}

$mobileCoverage = [];
foreach ($viewsByModule as $module => $moduleViews) {
    if ($module === 'system_templates' || $module === 'shared_ui') {
        continue;
    }

    $desktopCount = count(array_filter($moduleViews, fn (string $view): bool => ! str_starts_with($view, 'mobile/')));
    $mobileCount = match ($module) {
        'mobile' => count($moduleViews),
        'client_portal' => count(array_filter($mobileViews, fn (string $view): bool => str_starts_with($view, 'mobile/client/'))),
        default => count(array_filter($mobileViews, fn (string $view): bool => str_contains($view, "/{$module}/"))),
    };
    $mobileCoverage[] = [$module, $desktopCount, $mobileCount];
}

$now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s T');

$lines = [];
$lines[] = '# AromaMade / Olithea Mobile App Inventory';
$lines[] = '';
$lines[] = "Generated from the current Laravel codebase on {$now}.";
$lines[] = '';
$lines[] = '## Current Surface Counts';
$lines[] = '';
$lines[] = '| Surface | Count | Source |';
$lines[] = '| --- | ---: | --- |';
$lines[] = '| Routes | ' . count($routes) . ' | `php artisan route:list --json` |';
$lines[] = '| Controllers | ' . count($controllers) . ' | `app/Http/Controllers` |';
$lines[] = '| Models | ' . count($models) . ' | `app/Models` |';
$lines[] = '| Blade screens/templates | ' . count($views) . ' | `resources/views` |';
$lines[] = '| Mobile Blade screens | ' . count($mobileViews) . ' | `resources/views/mobile` |';
$lines[] = '| Migrations | ' . count($migrations) . ' | `database/migrations` |';
$lines[] = '';
$lines[] = '## Route Families';
$lines[] = '';
$lines[] = '| Family | Routes | Primary purpose |';
$lines[] = '| --- | ---: | --- |';
$purpose = [
    'admin' => 'Internal admin, CRM, finance, therapists, marketing, licenses.',
    'api' => 'JSON/API endpoints used by frontend tools and integrations.',
    'client_portal' => 'End-client authentication, account, messages, communities, files.',
    'mobile' => 'Mobile-specific app shell, PRO dashboard, client portal, therapist search, booking, appointments, clients, invoices.',
    'pro_authenticated' => 'Practitioner workspace: dashboard, agenda, clients, billing, products, availability, documents, trainings, communities, marketing.',
    'public_or_marketing' => 'Public website, SEO pages, practitioner directory, checkout/booking, blog, legal/help pages.',
];
foreach ($routesByModule as $module => $moduleRoutes) {
    $lines[] = '| `' . $module . '` | ' . count($moduleRoutes) . ' | ' . ($purpose[$module] ?? '') . ' |';
}
$lines[] = '';
$lines[] = '## Mobile Scope Decisions';
$lines[] = '';
$lines[] = '| Module | Routes | Blade screens/templates | Decision | Treatment | Rationale |';
$lines[] = '| --- | ---: | ---: | --- | --- | --- |';
foreach ($scopeDecisionRows as $row) {
    $module = $row['module'];
    $lines[] = '| `' . $module . '` | '
        . count($routesByModule[$module] ?? []) . ' | '
        . count($viewsByModule[$module] ?? []) . ' | '
        . $row['decision'] . ' | '
        . $row['treatment'] . ' | '
        . $row['rationale'] . ' |';
}
$lines[] = '';
$lines[] = '## Screen Families';
$lines[] = '';
$lines[] = 'These counts are based on Blade source folders. Use the feature coverage tables below for the more important product question: which practitioner and client-portal web features have `/mobile` equivalents.';
$lines[] = '';
$lines[] = '| Family | Screens/templates | Mobile-specific screens | Notes |';
$lines[] = '| --- | ---: | ---: | --- |';
foreach ($mobileCoverage as [$module, $desktopCount, $mobileCount]) {
    $notes = match ($module) {
        'admin' => 'Admin surfaces should stay web-first unless a specific mobile admin need is approved.',
        'auth' => 'Shared auth screens; mobile has its own practitioner login.',
        'client_portal' => 'Client portal now has isolated `/mobile/client` screens for auth, dashboard, messages, documents, invoices, and communities.',
        'mobile' => 'Current mobile foundation already exists.',
        'pro_authenticated' => 'Highest-priority product area for the Android app.',
        'public_or_marketing' => 'Public pages can stay responsive web; only booking/search flows need app-grade treatment.',
        default => '',
    };
    $lines[] = '| `' . $module . '` | ' . $desktopCount . ' | ' . $mobileCount . ' | ' . $notes . ' |';
}
$lines[] = '';
$lines[] = '## Practitioner Mobile Coverage';
$lines[] = '';
$lines[] = '| Feature | Web routes | Mobile routes | Mobile views | Controller mode | Status |';
$lines[] = '| --- | ---: | ---: | ---: | --- | --- |';
foreach ($featureCoverageRows as $row) {
    $lines[] = '| ' . $row['label'] . ' | ' . $row['web_count'] . ' | ' . $row['mobile_count'] . ' | ' . $row['view_count'] . ' | ' . $row['mode'] . ' | ' . $row['status'] . ' |';
}
$lines[] = '';
$lines[] = '## Client Portal Mobile Coverage';
$lines[] = '';
$lines[] = '| Feature | Web routes | Mobile routes | Mobile views | Controller mode | Status |';
$lines[] = '| --- | ---: | ---: | ---: | --- | --- |';
foreach ($clientPortalCoverageRows as $row) {
    $lines[] = '| ' . $row['label'] . ' | ' . $row['web_count'] . ' | ' . $row['mobile_count'] . ' | ' . $row['view_count'] . ' | ' . $row['mode'] . ' | ' . $row['status'] . ' |';
}
$lines[] = '';
$lines[] = '## Actionable Mobile Gaps';
$lines[] = '';
$gaps = array_values(array_filter(
    array_merge($featureCoverageRows, $clientPortalCoverageRows),
    fn (array $row): bool => $row['status'] === 'Gap' || $row['status'] === 'Route-only'
));
if ($gaps === []) {
    $lines[] = '- No catalogued practitioner or client-portal feature is missing both mobile routes and mobile views.';
} else {
    foreach ($gaps as $gap) {
        $reason = $gap['status'] === 'Route-only'
            ? 'has mobile routes but no dedicated mobile Blade screen detected'
            : 'has web routes but no mobile route detected';
        $lines[] = '- ' . $gap['label'] . ': ' . $reason . '.';
    }
}
$lines[] = '';
$lines[] = '## Shared Mobile Controller Routes';
$lines[] = '';
$lines[] = 'These `/mobile` routes intentionally reuse non-mobile controllers. They need controller-level mobile view branching tests whenever touched.';
$lines[] = '';
if ($sharedMobileRoutes === []) {
    $lines[] = '- None detected.';
} else {
    $lines[] = '| Method | URI | Name | Action |';
    $lines[] = '| --- | --- | --- | --- |';
    foreach ($sharedMobileRoutes as $route) {
        $lines[] = '| `' . ($route['method'] ?? '') . '` | `' . ($route['uri'] ?? '') . '` | `' . ($route['name'] ?? '') . '` | `' . str_replace('|', '\\|', (string) ($route['action'] ?? '')) . '` |';
    }
}
$lines[] = '';
$lines[] = '## Mobile Controller Usage';
$lines[] = '';
$lines[] = '| Controller | Mobile route count | Status | File |';
$lines[] = '| --- | ---: | --- | --- |';
foreach ($mobileControllerRows as $row) {
    $lines[] = '| `' . $row['name'] . '` | ' . $row['routes'] . ' | ' . $row['status'] . ' | `' . $row['path'] . '` |';
}
$lines[] = '';
$lines[] = '## Existing Mobile Screens';
$lines[] = '';
foreach ($mobileViews as $view) {
    $lines[] = '- `' . $view . '`';
}
$lines[] = '';
$lines[] = '## Blade Screen Inventory';
$lines[] = '';
$lines[] = '| Family | Blade screen/template |';
$lines[] = '| --- | --- |';
foreach ($bladeInventoryRows as $row) {
    $lines[] = '| `' . $row['family'] . '` | `' . $row['view'] . '` |';
}
$lines[] = '';
$lines[] = '## Route Inventory';
$lines[] = '';
foreach ($routesByModule as $module => $moduleRoutes) {
    $lines[] = '### ' . $module;
    $lines[] = '';
    $lines[] = '| Method | URI | Name | Action | Middleware |';
    $lines[] = '| --- | --- | --- | --- | --- |';
    foreach ($moduleRoutes as $route) {
        $middleware = $route['middleware'] ?? [];
        if (is_array($middleware)) {
            $middleware = implode(', ', $middleware);
        }
        $lines[] = '| `' . ($route['method'] ?? '') . '` | `' . ($route['uri'] ?? '') . '` | `' . ($route['name'] ?? '') . '` | `' . str_replace('|', '\\|', (string) ($route['action'] ?? '')) . '` | `' . str_replace('|', '\\|', (string) $middleware) . '` |';
    }
    $lines[] = '';
}
$lines[] = '## Model Relationships';
$lines[] = '';
$lines[] = '| Model | Relationships detected | File |';
$lines[] = '| --- | --- | --- |';
foreach ($modelRows as $row) {
    $relations = array_map(
        fn (array $relation): string => '`' . $relation['method'] . '` ' . $relation['type'] . ($relation['target'] ? ' `' . $relation['target'] . '`' : ''),
        $row['relations']
    );
    $lines[] = '| `' . $row['name'] . '` | ' . ($relations ? implode('<br>', $relations) : 'None detected') . ' | `' . $row['path'] . '` |';
}
$lines[] = '';
$lines[] = '## Mobile Build Implications';
$lines[] = '';
$lines[] = '- The `/mobile` product is a Laravel server-rendered mobile app surface packaged into Android with Capacitor.';
$lines[] = '- The safest Android product scope remains the practitioner workspace plus transactional public search/booking flows.';
$lines[] = '- Web behavior must remain authoritative. Mobile routes should either branch to mobile views from existing controllers or live under the isolated `mobile.*` route namespace.';
$lines[] = '- Public/marketing, admin, and system email/PDF templates should not be forced into mobile app scope unless they are part of a user journey inside the Android app.';
$lines[] = '- Every added mobile route should be verified with `php artisan route:list --path=mobile`, `php artisan view:cache`, `npm run build`, and browser checks at phone viewport.';
$lines[] = '';

$targetDir = $root . '/docs';
if (! is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

file_put_contents($targetDir . '/mobile-app-inventory.md', implode(PHP_EOL, $lines) . PHP_EOL);

echo 'Wrote docs/mobile-app-inventory.md' . PHP_EOL;
