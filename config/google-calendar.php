<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Profil d’authentification par défaut
    |--------------------------------------------------------------------------
    | L’application utilise le flux OAuth (compte Google du thérapeute),
    | pas un compte de service. Vous pouvez malgré tout forcer un profil
    | différent via la variable d’environnement GOOGLE_CALENDAR_AUTH_PROFILE.
    */
    'default_auth_profile' => env('GOOGLE_CALENDAR_AUTH_PROFILE', 'oauth'),

    /*
    |--------------------------------------------------------------------------
    | Profils disponibles
    |--------------------------------------------------------------------------
    */
    'auth_profiles' => [

        /*
         * ----- Compte de service (non utilisé dans notre cas) -----
         *
         * 1. Créez un service account dans Google Cloud.
         * 2. Téléchargez le JSON      →  storage/app/google-calendar/service-account-credentials.json
         * 3. Partagez le calendrier cible avec l’e-mail du compte de service.
         */
        'service_account' => [
            'credentials_json' => storage_path('app/google-calendar/service-account-credentials.json'),
        ],

        /*
         * ----- Flux OAuth utilisateur (recommandé) -----
         *
         * 1. Téléchargez le fichier « OAuth client » JSON             →
         *      storage/app/google-calendar/oauth-credentials.json
         * 2. Laissez Spatie écrire le token dans oauth-token.json.
         */
        'oauth' => [
            'credentials_json' => storage_path('app/google-calendar/oauth-credentials.json'),
            'token_json'       => storage_path('app/google-calendar/oauth-token.json'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Calendrier par défaut
    |--------------------------------------------------------------------------
    | "primary" = calendrier principal du thérapeute ; sinon l’ID complet
    | d’un agenda secondaire (ex. abc123@group.calendar.google.com).
    */
    'calendar_id' => env('GOOGLE_CALENDAR_ID', 'primary'),

    /*
    |--------------------------------------------------------------------------
    | Délégation d’identité (facultatif)
    |--------------------------------------------------------------------------
    | Renseignez l’e-mail à usurper si vous utilisez un compte de service
    | + G Suite domain-wide delegation. Laissez vide sinon.
    */
    'user_to_impersonate' => env('GOOGLE_CALENDAR_IMPERSONATE'),
];
