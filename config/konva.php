<?php

return [

    // Formats = the “real” export size (stage size)
    'formats' => [
        [
            'id'    => 'square_1080',
            'label' => 'Post carré',
            'w'     => 1080,
            'h'     => 1080,
            'hint'  => 'Instagram / Facebook (1:1)',
        ],
        [
            'id'    => 'story_1080_1920',
            'label' => 'Story / Reels',
            'w'     => 1080,
            'h'     => 1920,
            'hint'  => 'Instagram Story (9:16)',
        ],
        [
            'id'    => 'landscape_1920_1080',
            'label' => 'Paysage',
            'w'     => 1920,
            'h'     => 1080,
            'hint'  => 'YouTube thumbnail (16:9)',
        ],
    ],

    // Templates must be linked to a format_id
    'templates' => [
        [
            'id'        => 'quote',
            'label'     => '💬 Citation',
            'hint'      => 'Template citation classique',
            'format_id' => 'square_1080',
        ],
        [
            'id'        => 'promo',
            'label'     => '💸 Promo',
            'hint'      => 'Promo / offre spéciale',
            'format_id' => 'square_1080',
        ],
        [
            'id'        => 'event',
            'label'     => '📅 Atelier',
            'hint'      => 'Annonce d’atelier / événement',
            'format_id' => 'square_1080',
        ],
        [
            'id'        => 'testimonial',
            'label'     => '⭐ Avis client',
            'hint'      => 'Témoignage client',
            'format_id' => 'square_1080',
        ],

        [
            'id'        => 'tip_story',
            'label'     => '🌿 Astuce (Story)',
            'hint'      => 'Astuce bien-être en story',
            'format_id' => 'story_1080_1920',
        ],
        [
            'id'        => 'event_story',
            'label'     => '📅 Atelier (Story)',
            'hint'      => 'Annonce story',
            'format_id' => 'story_1080_1920',
        ],

        [
            'id'        => 'checklist_landscape',
            'label'     => '✅ Checklist (Paysage)',
            'hint'      => 'Slide / banner',
            'format_id' => 'landscape_1920_1080',
        ],
    ],
];
