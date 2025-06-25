<?php

return [

    // Utiliser uniquement OAuth
    'default_auth_profile' => env('GOOGLE_CALENDAR_AUTH_PROFILE', 'oauth'),

'auth_profiles' => [
    'oauth' => [
        // ← pointez sur le fichier que vous venez de copier
        'credentials_json' => storage_path('app/google-calendar/oauth-credentials.json'),
        'token_json'       => null,   // on n’a toujours pas besoin de fichier token
    ],
],


    // Agenda par défaut
    'calendar_id'        => env('GOOGLE_CALENDAR_ID', 'primary'),

    // Impersonation (laissez vide)
    'user_to_impersonate'=> env('GOOGLE_CALENDAR_IMPERSONATE'),
];
