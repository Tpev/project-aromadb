<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */
	'jitsi' => [
		'base_url' => env('JITSI_BASE_URL', 'https://visio.olithea.fr'),
		'domain' => env('JITSI_DOMAIN'),
		'app_id' => env('JITSI_APP_ID'),
        'secret' => env('JITSI_APP_SECRET'),
	],
    'social' => [
        'facebook_url' => env('SOCIAL_FACEBOOK_URL', 'https://www.facebook.com/profile.php?id=100089128162095'),
        'instagram_url' => env('SOCIAL_INSTAGRAM_URL'),
    ],
    'stripe' => [
        'secret' => env('STRIPE_SECRET'),
        'finance_secret' => env('STRIPE_FINANCE_SECRET', env('STRIPE_SECRET')),
		'webhook' => env('STRIPE_WEBHOOK_SECRET'),
    ],
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
	'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
    'model'   => env('MODEL_NAME', 'gpt-4.1'),
],
    'google_business' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_BUSINESS_REDIRECT_URI'),
        'scopes'        => explode(',', env('GOOGLE_BUSINESS_SCOPES', 'https://www.googleapis.com/auth/business.manage')),
    ],

    'super_pdp' => [
        'environment' => env('SUPER_PDP_ENV', 'sandbox'),
        'base_url' => env('SUPER_PDP_BASE_URL', 'https://api.superpdp.tech'),
        'authorize_url' => env('SUPER_PDP_AUTHORIZE_URL', 'https://api.superpdp.tech/oauth2/authorize'),
        'token_url' => env('SUPER_PDP_TOKEN_URL', 'https://api.superpdp.tech/oauth2/token'),
        'revoke_url' => env('SUPER_PDP_REVOKE_URL', 'https://api.superpdp.tech/oauth2/revoke'),
        'client_id' => env('SUPER_PDP_CLIENT_ID'),
        'client_secret' => env('SUPER_PDP_CLIENT_SECRET'),
        'redirect_uri' => env('SUPER_PDP_REDIRECT_URI'),
        'allowed_emails' => array_values(array_filter(array_map(
            static fn ($email) => strtolower(trim($email)),
            explode(',', env('SUPER_PDP_ALLOWED_EMAILS', 'john.satch00@gmail.com'))
        ))),
    ],


];
