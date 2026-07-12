<?php

return [
    'enabled' => env('OFFER_JOURNEYS_ENABLED', false),
    'public_pages_enabled' => env('OFFER_JOURNEYS_PUBLIC_PAGES_ENABLED', false),
    'automation_enabled' => env('OFFER_JOURNEYS_AUTOMATION_ENABLED', false),
    'email_enabled' => env('OFFER_JOURNEYS_EMAIL_ENABLED', false),
    'tracking_enabled' => env('OFFER_JOURNEYS_TRACKING_ENABLED', false),
    'pause_all_marketing_emails' => env('OFFER_JOURNEYS_PAUSE_ALL_MARKETING_EMAILS', true),
    'allow_all_eligible_users' => env('OFFER_JOURNEYS_ALLOW_ALL', false),
    'client_tags_enabled' => env('OFFER_JOURNEYS_CLIENT_TAGS_ENABLED', false),
    'segment_campaigns_enabled' => env('OFFER_JOURNEYS_SEGMENT_CAMPAIGNS_ENABLED', false),
    'email_editor_enabled' => env('OFFER_JOURNEYS_EMAIL_EDITOR_ENABLED', false),

    'deliverability' => [
        'enabled' => env('OFFER_JOURNEYS_SES_EVENTS_ENABLED', false),
        'sns_topic_arns' => array_values(array_filter(array_map(
            static fn (string $arn): string => trim($arn),
            explode(',', (string) env('OFFER_JOURNEYS_SES_SNS_TOPIC_ARNS', ''))
        ))),
        'auto_confirm_subscription' => env('OFFER_JOURNEYS_SES_AUTO_CONFIRM_SUBSCRIPTION', false),
        'configuration_set' => env('OFFER_JOURNEYS_SES_CONFIGURATION_SET'),
        'domain' => env('OFFER_JOURNEYS_EMAIL_DOMAIN', 'olithea.fr'),
        'dkim_selectors' => array_values(array_filter(array_map(
            static fn (string $selector): string => trim($selector),
            explode(',', (string) env('OFFER_JOURNEYS_DKIM_SELECTORS', ''))
        ))),
        'timestamp_tolerance_seconds' => (int) env('OFFER_JOURNEYS_SNS_TIMESTAMP_TOLERANCE', 3600),
        'minimum_volume_for_reputation' => (int) env('OFFER_JOURNEYS_REPUTATION_MINIMUM_VOLUME', 20),
        'max_bounce_rate' => (float) env('OFFER_JOURNEYS_MAX_BOUNCE_RATE', 0.05),
        'max_complaint_rate' => (float) env('OFFER_JOURNEYS_MAX_COMPLAINT_RATE', 0.001),
        'progressive_monthly_limits' => [
            ['minimum_account_age_days' => 0, 'limit' => (int) env('OFFER_JOURNEYS_NEW_ACCOUNT_EMAIL_LIMIT', 100)],
            ['minimum_account_age_days' => 14, 'limit' => (int) env('OFFER_JOURNEYS_ESTABLISHED_ACCOUNT_EMAIL_LIMIT', 500)],
            ['minimum_account_age_days' => 60, 'limit' => (int) env('OFFER_JOURNEYS_TRUSTED_ACCOUNT_EMAIL_LIMIT', 2000)],
        ],
    ],

    'support_console_enabled' => env('OFFER_JOURNEYS_SUPPORT_CONSOLE_ENABLED', false),
    'publication_assistance_enabled' => env('OFFER_JOURNEYS_PUBLICATION_ASSISTANCE_ENABLED', false),
    'template_library_enabled' => env('OFFER_JOURNEYS_TEMPLATE_LIBRARY_ENABLED', false),
    'rich_editor_enabled' => env('OFFER_JOURNEYS_RICH_EDITOR_ENABLED', false),
    'writing_assistant_enabled' => env('OFFER_JOURNEYS_WRITING_ASSISTANT_ENABLED', false),
    'custom_forms_enabled' => env('OFFER_JOURNEYS_CUSTOM_FORMS_ENABLED', false),
    'message_tools_enabled' => env('OFFER_JOURNEYS_MESSAGE_TOOLS_ENABLED', false),
    'contact_frequency_hours' => (int) env('OFFER_JOURNEYS_CONTACT_FREQUENCY_HOURS', 72),
    'campaigns_enabled' => env('OFFER_JOURNEYS_CAMPAIGNS_ENABLED', false),
    'abandonment_reminders_enabled' => env('OFFER_JOURNEYS_ABANDONMENT_REMINDERS_ENABLED', false),
    'abandonment_delay_hours' => (int) env('OFFER_JOURNEYS_ABANDONMENT_DELAY_HOURS', 24),
    'commercial_tools_enabled' => env('OFFER_JOURNEYS_COMMERCIAL_TOOLS_ENABLED', false),
    'contact_import_enabled' => env('OFFER_JOURNEYS_CONTACT_IMPORT_ENABLED', false),

    'legal' => [
        'consent_text_version' => env('OFFER_JOURNEYS_CONSENT_TEXT_VERSION', 'draft-v1-legal-review-required'),
        'request_privacy_text' => env(
            'OFFER_JOURNEYS_REQUEST_PRIVACY_TEXT',
            'Les informations saisies sont utilisees par le praticien pour repondre a cette demande.'
        ),
        'marketing_consent_text' => env(
            'OFFER_JOURNEYS_MARKETING_CONSENT_TEXT',
            'J accepte de recevoir par email les informations liees a cette offre. Je peux me desinscrire a tout moment.'
        ),
    ],

    'retention' => [
        'enabled' => env('OFFER_JOURNEYS_RETENTION_ENABLED', false),
        'contact_days' => (int) env('OFFER_JOURNEYS_CONTACT_RETENTION_DAYS', 1095),
        'analytics_days' => (int) env('OFFER_JOURNEYS_ANALYTICS_RETENTION_DAYS', 395),
        'delivery_days' => (int) env('OFFER_JOURNEYS_DELIVERY_RETENTION_DAYS', 395),
        'consent_evidence_days' => (int) env('OFFER_JOURNEYS_CONSENT_RETENTION_DAYS', 1825),
    ],

    'beta_user_ids' => array_values(array_filter(array_map(
        static fn (string $id): ?int => ctype_digit(trim($id)) ? (int) trim($id) : null,
        explode(',', (string) env('OFFER_JOURNEYS_BETA_USER_IDS', ''))
    ))),

    'limits' => [
        'active_per_user' => (int) env('OFFER_JOURNEYS_MAX_ACTIVE_PER_USER', 10),
        'monthly_marketing_emails' => (int) env('OFFER_JOURNEYS_MONTHLY_EMAIL_LIMIT', 2000),
        'v1_message_steps' => 3,
        'custom_form_fields' => 3,
    ],

    'attribution_days' => (int) env('OFFER_JOURNEYS_ATTRIBUTION_DAYS', 30),
    'resource_link_minutes' => (int) env('OFFER_JOURNEYS_RESOURCE_LINK_MINUTES', 10080),
    'quiet_hours' => [
        'start' => env('OFFER_JOURNEYS_QUIET_HOURS_START', '20:00'),
        'end' => env('OFFER_JOURNEYS_QUIET_HOURS_END', '08:00'),
    ],

    'allowed_objectives' => [
        'appointment',
        'event',
        'lead_magnet',
        'training',
        'gift_voucher',
        'contact_request',
    ],

    'allowed_page_types' => [
        'landing',
        'opt_in',
        'content',
        'sales',
        'qualification',
        'booking',
        'event_registration',
        'checkout',
        'training_access',
        'thank_you',
    ],

    'objective_labels' => [
        'appointment' => 'Réservations',
        'event' => 'Atelier ou événement',
        'lead_magnet' => 'Ressource gratuite',
        'training' => 'Formation',
        'gift_voucher' => 'Bon cadeau',
        'contact_request' => 'Demande qualifiée',
    ],

    'page_type_labels' => [
        'landing' => 'Présentation',
        'opt_in' => 'Formulaire de capture',
        'content' => 'Contenu',
        'sales' => 'Page d’offre',
        'qualification' => 'Demande qualifiée',
        'booking' => 'Réservation',
        'event_registration' => 'Inscription à un événement',
        'checkout' => 'Paiement',
        'training_access' => 'Accès à une formation',
        'thank_you' => 'Confirmation',
    ],

    'status_labels' => [
        'draft' => 'Brouillon',
        'published' => 'Publié',
        'paused' => 'En pause',
        'archived' => 'Archivé',
    ],
];
