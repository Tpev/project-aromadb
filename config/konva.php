<?php

return [
    'formats' => [
        [
            'id' => 'square_1080',
            'label' => 'Post carre',
            'w' => 1080,
            'h' => 1080,
            'hint' => 'Instagram / Facebook (1:1)',
        ],
        [
            'id' => 'story_1080_1920',
            'label' => 'Story / Reels',
            'w' => 1080,
            'h' => 1920,
            'hint' => 'Instagram Story (9:16)',
        ],
        [
            'id' => 'landscape_1920_1080',
            'label' => 'Paysage',
            'w' => 1920,
            'h' => 1080,
            'hint' => 'YouTube / LinkedIn (16:9)',
        ],
    ],

    'templates' => [
        [
            'id' => 'quote_minimal_square',
            'label' => 'Citation minimaliste',
            'hint' => 'Post citation premium pret a publier',
            'format_id' => 'square_1080',
        ],
        [
            'id' => 'promo_flash_square',
            'label' => 'Promo flash',
            'hint' => 'Offre limitee avec code promo',
            'format_id' => 'square_1080',
        ],
        [
            'id' => 'event_masterclass_square',
            'label' => 'Annonce masterclass',
            'hint' => 'Date + promesse + CTA clair',
            'format_id' => 'square_1080',
        ],
        [
            'id' => 'testimonial_proof_square',
            'label' => 'Avis client',
            'hint' => 'Preuve sociale et resultat client',
            'format_id' => 'square_1080',
        ],
        [
            'id' => 'before_after_square',
            'label' => 'Avant / Apres',
            'hint' => 'Comparaison visuelle en 2 zones',
            'format_id' => 'square_1080',
        ],
        [
            'id' => 'carousel_cover_square',
            'label' => 'Couverture carrousel',
            'hint' => 'Slide 1 avec promesse forte',
            'format_id' => 'square_1080',
        ],
        [
            'id' => 'story_announcement_vertical',
            'label' => 'Story annonce',
            'hint' => 'Annonce rapide de nouveaute',
            'format_id' => 'story_1080_1920',
        ],
        [
            'id' => 'story_tip_vertical',
            'label' => 'Story astuce',
            'hint' => 'Format pedagogique en 3 etapes',
            'format_id' => 'story_1080_1920',
        ],
        [
            'id' => 'story_countdown_vertical',
            'label' => 'Story countdown',
            'hint' => 'Compte a rebours evenement',
            'format_id' => 'story_1080_1920',
        ],
        [
            'id' => 'story_client_review_vertical',
            'label' => 'Story avis client',
            'hint' => 'Temoignage + note + appel action',
            'format_id' => 'story_1080_1920',
        ],
        [
            'id' => 'webinar_banner_landscape',
            'label' => 'Banniere webinar',
            'hint' => 'Visuel hero 16:9 pour inscription',
            'format_id' => 'landscape_1920_1080',
        ],
        [
            'id' => 'youtube_thumbnail_landscape',
            'label' => 'Miniature video',
            'hint' => 'Titre impactant + contraste fort',
            'format_id' => 'landscape_1920_1080',
        ],
        [
            'id' => 'checklist_landscape_pro',
            'label' => 'Checklist paysage',
            'hint' => 'Support de post educatif 16:9',
            'format_id' => 'landscape_1920_1080',
        ],
    ],

    'branding_fonts' => [
        [
            'key' => 'cormorant',
            'label' => 'Cormorant Garamond',
            'family' => '"Cormorant Garamond", Georgia, serif',
        ],
        [
            'key' => 'montserrat',
            'label' => 'Montserrat',
            'family' => 'Montserrat, "Avenir Next", sans-serif',
        ],
        [
            'key' => 'avenir_next',
            'label' => 'Avenir Next',
            'family' => '"Avenir Next", Montserrat, sans-serif',
        ],
    ],

    'branding_presets' => [
        [
            'id' => 'zen_olive',
            'label' => 'Olithea',
            'fonts' => ['heading' => 'cormorant', 'body' => 'montserrat'],
            'colors' => [
                'primary' => '#A7B88A',
                'secondary' => '#6B4A3A',
                'accent' => '#E9B07A',
                'background' => '#F6F2EB',
                'text' => '#3F2B22',
            ],
        ],
        [
            'id' => 'soft_terracotta',
            'label' => 'Soft Terracotta',
            'fonts' => ['heading' => 'cormorant', 'body' => 'montserrat'],
            'colors' => [
                'primary' => '#9A3412',
                'secondary' => '#C2410C',
                'accent' => '#FDBA74',
                'background' => '#FFF7ED',
                'text' => '#312E2B',
            ],
        ],
        [
            'id' => 'forest_clarity',
            'label' => 'Forest Clarity',
            'fonts' => ['heading' => 'cormorant', 'body' => 'montserrat'],
            'colors' => [
                'primary' => '#166534',
                'secondary' => '#15803D',
                'accent' => '#86EFAC',
                'background' => '#F0FDF4',
                'text' => '#052E16',
            ],
        ],
        [
            'id' => 'midnight_gold',
            'label' => 'Midnight Gold',
            'fonts' => ['heading' => 'cormorant', 'body' => 'montserrat'],
            'colors' => [
                'primary' => '#0F172A',
                'secondary' => '#1E293B',
                'accent' => '#F59E0B',
                'background' => '#F8FAFC',
                'text' => '#0F172A',
            ],
        ],
    ],
];
