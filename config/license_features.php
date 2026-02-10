<?php

return [

    'features' => [
		'client_profiles',
    ],

    'plans' => [

        'legacy' => [

			'client_profiles',
        ],

        'free' => [
        ],



        'starter' => [

			'client_profiles',
			'appointement',
			'facturation',
			'products',
			'questionnaires',
			'client_profiles_pro',
        ],

        'pro' => [

			'client_profiles',
			'client_profiles_pro',
			'client_profile_advanced',
			'espace-client',
			'review',
			'appointement',
			'facturation',
			'livre_recettes',
			'events',
			'products',
			'questionnaires',
			'integration',
			'conseil',
			'inventory',
        ],

        'premium' => [

			'client_profiles',
			'client_profiles_pro',
			'client_profile_advanced',
			'espace-client',
			'review',
			'appointement',
			'facturation',
			'livre_recettes',
			'events',
			'products',
			'questionnaires',
			'integration',
						'conseil',
						'inventory',
        ],
		        'trial' => [

			'client_profiles',
            'client_profiles_pro',
			'client_profile_advanced',
			'espace-client',
			'review',
			'appointement',
			'facturation',
			'livre_recettes',
			'events',
			'products',
			'questionnaires',
			'integration',
						'conseil',
						'inventory',
        ],
    ],
];
