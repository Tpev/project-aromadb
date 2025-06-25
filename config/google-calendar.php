<?php

return [

    'default_auth_profile' => 'oauth',

'auth_profiles' => [
    'oauth' => [
        'credentials_json' => storage_path('app/google-calendar/oauth-credentials.json'),
        'token_json'       => storage_path('app/google-calendar/oauth-token.json'), // ← plus NULL
    ],
],

    'calendar_id'        => env('GOOGLE_CALENDAR_ID', 'primary'),
    'user_to_impersonate'=> env('GOOGLE_CALENDAR_IMPERSONATE'),
];
