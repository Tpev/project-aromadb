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
];
