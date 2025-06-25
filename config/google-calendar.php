<?php

return [

    // Utiliser uniquement OAuth
    'default_auth_profile' => env('GOOGLE_CALENDAR_AUTH_PROFILE', 'oauth'),

    'auth_profiles' => [

        // ---------- Profil OAuth (token injecté en mémoire) ----------
        'oauth' => [
            'credentials_json' => null,   // on ne lit pas de fichier
            'token_json'       => null,   // on ne stocke pas de fichier
        ],
    ],

    // Agenda par défaut
    'calendar_id'        => env('GOOGLE_CALENDAR_ID', 'primary'),

    // Impersonation (laissez vide)
    'user_to_impersonate'=> env('GOOGLE_CALENDAR_IMPERSONATE'),
];
