<?php

return [
    'default_navbar_variant' => 'floating-island',
    'default_menu_mode' => 'simple',

    'navbar_surface_modes' => [
        'design' => [
            'label' => 'No color (default)',
            'description' => 'Start with no navbar background so the page surface shows through.',
        ],
        'transparent' => [
            'label' => 'No color',
            'description' => 'Remove the navbar surface so the page background shows through.',
        ],
        'theme' => [
            'label' => 'Site background',
            'description' => 'Use the background and text colors from the active site theme.',
        ],
        'custom' => [
            'label' => 'Custom color',
            'description' => 'Choose independent navbar background and text colors.',
        ],
    ],

    'menu_modes' => [
        'simple' => [
            'label' => 'Simple navigation',
            'description' => 'Show the saved navigation links directly in the navbar.',
        ],
        'mega' => [
            'label' => 'Mega menu',
            'description' => 'Open the saved navigation links inside a large interactive panel.',
        ],
    ],

    'action_positions' => [
        'end' => [
            'label' => 'Right side',
            'description' => 'Place action buttons after the navigation links.',
        ],
        'start' => [
            'label' => 'Before links',
            'description' => 'Place action buttons between the logo and navigation links.',
        ],
    ],

    'action_variants' => [
        'primary' => 'Filled',
        'outline' => 'Outline',
        'text' => 'Text',
    ],

    'navbar_variants' => [
        'classic-inline' => [
            'label' => 'Classic Inline',
            'description' => 'A dependable full-width header with clear links and a strong action.',
            'category' => 'Essential',
        ],
        'floating-island' => [
            'label' => 'Floating Island',
            'description' => 'A compact elevated navbar with a soft entrance and rounded shell.',
            'category' => 'Modern',
        ],
        'centered-brand' => [
            'label' => 'Centered Brand',
            'description' => 'An editorial arrangement that keeps the brand at the visual center.',
            'category' => 'Editorial',
        ],
    ],

    'footer_variants' => [
        'minimal' => [
            'label' => 'Minimal',
            'description' => 'A quiet finish with brand, social links, and copyright.',
        ],
        'columns' => [
            'label' => 'Link columns',
            'description' => 'Brand context beside organized navigation groups.',
        ],
        'newsletter' => [
            'label' => 'Callout footer',
            'description' => 'A prominent callout alongside brand and navigation modules.',
        ],
        'editorial' => [
            'label' => 'Editorial',
            'description' => 'Large typography and an expressive asymmetric arrangement.',
        ],
    ],

    'legacy_navbar_variants' => ['mega-menu'],
];
