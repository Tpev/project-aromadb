<?php

return [
    'default_radius_km' => 10,

    'ign_search_endpoint' => env('IGN_GEOCODING_SEARCH_ENDPOINT', 'https://data.geopf.fr/geocodage/search'),

    'specialty_aliases' => [
        'naturopathie' => [
            'naturopathie',
            'naturopathe',
            'naturo',
            'praticien naturopathe',
            'conseiller en naturopathie',
        ],
        'reflexologie' => [
            'reflexologie',
            'réflexologie',
            'reflexologue',
            'réflexologue',
            'reflexotherapie',
            'réflexothérapie',
        ],
        'sophrologie' => [
            'sophrologie',
            'sophrologue',
        ],
        'hypnose' => [
            'hypnose',
            'hypnotherapie',
            'hypnothérapie',
            'hypnotherapeute',
            'hypnothérapeute',
        ],
        'osteopathie' => [
            'osteopathie',
            'ostéopathie',
            'osteopathe',
            'ostéopathe',
        ],
        'massage bien etre' => [
            'massage',
            'massages',
            'massage bien etre',
            'massage bien-être',
            'masseuse bien etre',
            'masseuse bien-être',
            'masseur bien etre',
            'masseur bien-être',
        ],
        'shiatsu' => [
            'shiatsu',
            'praticien shiatsu',
        ],
        'ayurveda' => [
            'ayurveda',
            'ayurvédique',
            'ayurvedique',
            'praticien ayurveda',
        ],
        'energetique' => [
            'energetique',
            'énergétique',
            'energeticien',
            'énergéticien',
            'soin energetique',
            'soin énergétique',
        ],
        'aromatherapie' => [
            'aromatherapie',
            'aromathérapie',
            'aromatherapeute',
            'aromathérapeute',
        ],
    ],
];
