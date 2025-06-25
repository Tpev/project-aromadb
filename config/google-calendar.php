<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Profil d’authentification par défaut
    |--------------------------------------------------------------------------
    | Nous utilisons uniquement le flux OAuth (chaque thérapeute possède son
    | access-token enregistré en base de données).  Aucune lecture de fichier
    | JSON n’est nécessaire.
    */
    'default_auth_profile' => env('GOOGLE_CALENDAR_AUTH_PROFILE', 'oauth'),

    /*
    |--------------------------------------------------------------------------
    | Profils disponibles
    |--------------------------------------------------------------------------
    */
    'auth_profiles' => [

        /* -- Compte de service (laissez tel quel, non utilisé) --------------- */
        'service_account' => [
            'credentials_json' => storage_path('app/google-calendar/service-account-credentials.json'),
        ],

        /* -- Flux OAuth utilisateur ----------------------------------------- */
        'oauth' => [

            /*
             * Spatie accepte désormais qu’on fournisse UNIQUEMENT le token
             * via   config(['google-calendar.oauth_token' => [...]])
             * sans fichier.  On met donc les chemins à null.
             */
            'credentials_json' => null,
            'token_json'       => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Calendrier Google à utiliser par défaut
    |--------------------------------------------------------------------------
    */
    'calendar_id' => env('GOOGLE_CALENDAR_ID', 'primary'),

    /*
    |--------------------------------------------------------------------------
    | Délégation (compte de service) – facultatif
    |--------------------------------------------------------------------------
    */
    'user_to_impersonate' => env('GOOGLE_CALENDAR_IMPERSONATE'),
];
