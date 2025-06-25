<?php

return [
    'default_auth_profile' => 'oauth',
    'auth_profiles' => [
        'oauth' => [
            'credentials_json' => storage_path('app/google-calendar/oauth-credentials.json'),
            // valeur factice ; sera écrasée dynamiquement
            'token_json'       => storage_path('app/google-calendar/tokens/dummy.json'),
        ],
    ],
    'calendar_id' => env('GOOGLE_CALENDAR_ID', 'primary'),
];

