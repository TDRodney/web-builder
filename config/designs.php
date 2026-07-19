<?php

/*
|--------------------------------------------------------------------------
| Design catalog helpers
|--------------------------------------------------------------------------
| Every helper returns a plain block-AST node (id / type / props / children)
| using only types registered in config/blocks.php. The optional `reveal`
| and `revealDelay` props drive the public-site scroll entrance animation
| and are ignored by the editor canvas.
*/

$withReveal = static function (array $block, string $type = 'fade-up', int $delay = 0): array {
    $block['props']['reveal'] = $type;

    if ($delay > 0) {
        $block['props']['revealDelay'] = $delay;
    }

    return $block;
};

$patternText = static fn (string $id, string $role, string $content, array $props = []): array => [
    'id' => $id,
    'type' => 'AtomicText',
    'props' => [
        'patternRole' => $role,
        'content' => $content,
        'padding' => 0,
        'backgroundColor' => 'transparent',
        'fontSize' => '18px',
        'color' => '--theme-text',
        'fontFamily' => 'body',
        'fontWeight' => '400',
        'lineHeight' => '1.55',
        'letterSpacing' => '0em',
        'textAlign' => 'inherit',
        'maxWidth' => 'none',
        ...$props,
    ],
    'children' => [],
];

$patternButton = static fn (string $id, string $role, string $label, string $url, string $variant = 'primary'): array => [
    'id' => $id,
    'type' => 'ButtonBlock',
    'props' => [
        'patternRole' => $role,
        'label' => $label,
        'url' => $url,
        'variant' => $variant,
        'size' => 'lg',
        'alignment' => 'start',
        'padding' => 0,
        'backgroundColor' => 'transparent',
    ],
    'children' => [],
];

$patternImage = static fn (string $id, string $alt): array => [
    'id' => $id,
    'type' => 'ImageBlock',
    'props' => [
        'patternRole' => 'media',
        'src' => '',
        'alt' => $alt,
        'objectFit' => 'cover',
        'borderRadius' => 'var(--theme-border-radius)',
        'width' => '100%',
        'height' => '520px',
        'padding' => 0,
        'backgroundColor' => 'transparent',
        'reveal' => 'scale-in',
    ],
    'children' => [],
];

$patternColumn = static fn (string $id, array $children, array $props = []): array => [
    'id' => $id,
    'type' => 'LayoutColumn',
    'props' => [
        'padding' => 0,
        'backgroundColor' => 'transparent',
        'span' => 'auto',
        'width' => 'auto',
        'height' => 'auto',
        'gap' => '18px',
        'verticalAlign' => 'center',
        'horizontalAlign' => 'stretch',
        ...$props,
    ],
    'children' => $children,
];

$heroSection = static function (
    string $id,
    string $headline,
    string $subheadline,
    string $patternKey,
    string $eyebrow,
    string $buttonLabel,
    string $buttonUrl,
    string $imageAlt,
) use ($patternButton, $patternColumn, $patternImage, $patternText): array {
    $alignment = $patternKey === 'hero-centered' ? 'center' : 'left';
    $copy = [
        $patternText("{$id}-eyebrow", 'eyebrow', $eyebrow, [
            'fontSize' => '12px',
            'fontWeight' => '700',
            'letterSpacing' => '0.14em',
            'textAlign' => $alignment,
        ]),
        $patternText("{$id}-heading", 'heading', $headline, [
            'fontSize' => $patternKey === 'hero-editorial' ? 'clamp(3.8rem, 10vw, 9rem)' : 'clamp(3rem, 7vw, 6.75rem)',
            'fontFamily' => 'heading',
            'fontWeight' => '700',
            'lineHeight' => $patternKey === 'hero-editorial' ? '0.86' : '0.96',
            'letterSpacing' => '-0.05em',
            'textAlign' => $alignment,
            'maxWidth' => $patternKey === 'hero-centered' ? '14ch' : '12ch',
            'reveal' => 'fade-up',
        ]),
        $patternText("{$id}-body", 'body', $subheadline, [
            'fontSize' => '18px',
            'lineHeight' => '1.65',
            'textAlign' => $alignment,
            'maxWidth' => '56ch',
            'reveal' => 'fade-up',
            'revealDelay' => 100,
        ]),
        $patternButton("{$id}-action", 'primaryAction', $buttonLabel, $buttonUrl),
    ];

    if ($patternKey === 'hero-centered' || $patternKey === 'hero-minimal') {
        $copy[3]['props']['alignment'] = $patternKey === 'hero-centered' ? 'center' : 'start';

        return [
            'id' => $id,
            'type' => 'SectionBlock',
            'props' => [
                'patternKey' => $patternKey,
                'padding' => 0,
                'sectionPadding' => 72,
                'backgroundColor' => 'transparent',
                'backgroundImage' => '',
                'contentWidth' => $patternKey === 'hero-centered' ? 980 : 1080,
                'minHeight' => $patternKey === 'hero-centered' ? 560 : 420,
                'verticalAlign' => 'center',
                'textAlign' => $alignment,
                'overlayOpacity' => 0,
            ],
            'children' => [
                $patternColumn("{$id}-copy", $copy, [
                    'horizontalAlign' => $patternKey === 'hero-centered' ? 'center' : 'stretch',
                ]),
            ],
        ];
    }

    $copyColumn = $patternColumn("{$id}-copy", $copy);
    $mediaColumn = $patternColumn("{$id}-media-column", [
        $patternImage("{$id}-media", $imageAlt),
    ]);
    $imageFirst = $patternKey === 'hero-split-left';

    return [
        'id' => $id,
        'type' => 'SectionBlock',
        'props' => [
            'patternKey' => $patternKey,
            'padding' => 0,
            'sectionPadding' => 72,
            'backgroundColor' => 'transparent',
            'backgroundImage' => '',
            'contentWidth' => 1240,
            'minHeight' => $patternKey === 'hero-editorial' ? 700 : 600,
            'verticalAlign' => 'center',
            'textAlign' => 'left',
            'overlayOpacity' => 0,
        ],
        'children' => [[
            'id' => "{$id}-grid",
            'type' => 'LayoutGrid',
            'props' => [
                'padding' => 0,
                'backgroundColor' => 'transparent',
                'columns' => 2,
                'gap' => 'clamp(2rem, 6vw, 6rem)',
                'columnTemplate' => $imageFirst ? 'wide-left' : 'wide-right',
                'alignItems' => 'center',
            ],
            'children' => $imageFirst ? [$mediaColumn, $copyColumn] : [$copyColumn, $mediaColumn],
        ]],
    ];
};

$imageBlock = static fn (string $id, string $alt, string $height = '420px', string $reveal = 'scale-in'): array => [
    'id' => $id,
    'type' => 'ImageBlock',
    'props' => [
        'src' => '',
        'alt' => $alt,
        'objectFit' => 'cover',
        'borderRadius' => 'var(--theme-border-radius)',
        'width' => '100%',
        'height' => $height,
        'padding' => 0,
        'backgroundColor' => 'transparent',
        'reveal' => $reveal,
    ],
];

$headingBlock = static fn (string $id, string $content): array => [
    'id' => $id,
    'type' => 'AtomicText',
    'props' => [
        'padding' => 20,
        'backgroundColor' => 'transparent',
        'content' => $content,
        'fontSize' => '32px',
        'color' => '--theme-text',
        'reveal' => 'fade-up',
    ],
];

$richTextBlock = static fn (string $id, string $html): array => [
    'id' => $id,
    'type' => 'RichTextBlock',
    'props' => [
        'html' => $html,
        'padding' => 20,
        'backgroundColor' => 'transparent',
        'reveal' => 'fade-up',
    ],
];

$featureGrid = static function (string $id, array $features): array {
    return [
        'id' => $id,
        'type' => 'LayoutGrid',
        'props' => [
            'padding' => 20,
            'backgroundColor' => 'transparent',
            'columns' => 3,
            'gap' => '1.5rem',
        ],
        'children' => array_map(
            static fn (array $feature, int $index): array => [
                'id' => "{$id}-item-".($index + 1),
                'type' => 'FeatureBlock',
                'props' => [
                    'padding' => 20,
                    'backgroundColor' => 'transparent',
                    'title' => $feature['title'],
                    'body' => $feature['body'],
                    'reveal' => 'fade-up',
                    'revealDelay' => min(($index % 3) * 120, 450),
                ],
            ],
            $features,
            array_keys($features),
        ),
    ];
};

$galleryGrid = static function (string $id, array $imageAlts, string $height = '280px') use ($imageBlock): array {
    return [
        'id' => $id,
        'type' => 'LayoutGrid',
        'props' => [
            'padding' => 20,
            'backgroundColor' => 'transparent',
            'columns' => count($imageAlts) >= 3 ? 3 : count($imageAlts),
            'gap' => '1rem',
        ],
        'children' => array_map(
            static function (string $alt, int $index) use ($id, $imageBlock, $height): array {
                $image = $imageBlock("{$id}-image-".($index + 1), $alt, $height, 'fade-up');
                $image['props']['revealDelay'] = min(($index % 3) * 120, 450);

                return $image;
            },
            $imageAlts,
            array_keys($imageAlts),
        ),
    ];
};

$faqBlock = static fn (string $id, array $items): array => [
    'id' => $id,
    'type' => 'FAQBlock',
    'props' => [
        'items' => $items,
        'padding' => 40,
        'backgroundColor' => 'transparent',
        'reveal' => 'fade-up',
    ],
];

$buttonBlock = static fn (string $id, string $label, string $url): array => [
    'id' => $id,
    'type' => 'ButtonBlock',
    'props' => [
        'label' => $label,
        'variant' => 'primary',
        'url' => $url,
        'size' => 'lg',
        'reveal' => 'fade-up',
        'revealDelay' => 100,
    ],
];

$testimonialBlock = static fn (string $id, string $quote, string $authorName, string $authorRole): array => [
    'id' => $id,
    'type' => 'TestimonialBlock',
    'props' => [
        'quote' => $quote,
        'authorName' => $authorName,
        'authorRole' => $authorRole,
        'avatarSrc' => '',
        'padding' => 40,
        'backgroundColor' => 'transparent',
        'reveal' => 'scale-in',
    ],
];

$contactFormBlock = static fn (string $id, string $submitLabel, string $successMessage, string $messagePlaceholder): array => [
    'id' => $id,
    'type' => 'ContactFormBlock',
    'props' => [
        'fields' => [
            ['type' => 'text', 'label' => 'Your Name', 'placeholder' => 'Full name', 'required' => 'yes'],
            ['type' => 'email', 'label' => 'Your Email', 'placeholder' => 'you@example.com', 'required' => 'yes'],
            ['type' => 'textarea', 'label' => 'Message', 'placeholder' => $messagePlaceholder, 'required' => 'yes'],
        ],
        'submitLabel' => $submitLabel,
        'successMessage' => $successMessage,
        'padding' => 40,
        'backgroundColor' => 'transparent',
        'reveal' => 'fade-up',
    ],
];

$storeBlock = static fn (string $id, string $type, array $props, int $padding = 40): array => [
    'id' => $id,
    'type' => $type,
    'props' => ['padding' => $padding, 'backgroundColor' => 'transparent', ...$props],
];

$imageWithText = static fn (string $id, array $props, string $reveal = 'fade-up'): array => [
    'id' => $id,
    'type' => 'ImageWithTextBlock',
    'props' => ['padding' => 0, 'backgroundColor' => 'transparent', 'reveal' => $reveal, ...$props],
];

$menuBlock = static fn (string $id, string $heading, string $subheading, array $items, int $columns = 2): array => [
    'id' => $id,
    'type' => 'MenuBlock',
    'props' => [
        'heading' => $heading,
        'subheading' => $subheading,
        'columns' => $columns,
        'items' => $items,
        'padding' => 40,
        'backgroundColor' => 'transparent',
        'reveal' => 'fade-up',
    ],
];

$starterProducts = [
    ['key' => 'linen-throw', 'title' => 'Linen throw', 'priceLabel' => '$48.00', 'compareAtLabel' => '', 'badge' => 'New', 'imageSrc' => '', 'imageAlt' => 'Replace with a linen throw product image', 'url' => '/product'],
    ['key' => 'stoneware-cup', 'title' => 'Stoneware cup', 'priceLabel' => '$24.00', 'compareAtLabel' => '', 'badge' => '', 'imageSrc' => '', 'imageAlt' => 'Replace with a stoneware cup product image', 'url' => '/product'],
    ['key' => 'canvas-tote', 'title' => 'Canvas tote', 'priceLabel' => '$36.00', 'compareAtLabel' => '', 'badge' => '', 'imageSrc' => '', 'imageAlt' => 'Replace with a canvas tote product image', 'url' => '/product'],
    ['key' => 'cedar-candle', 'title' => 'Cedar candle', 'priceLabel' => '$32.00', 'compareAtLabel' => '$40.00', 'badge' => 'Sale', 'imageSrc' => '', 'imageAlt' => 'Replace with a cedar candle product image', 'url' => '/product'],
];

return [
    'schema_version' => 1,

    'styles' => [
        'restaurant-warmth' => [
            'label' => 'Restaurant Warmth',
            'theme_config' => [
                'colors' => [
                    'primary' => '#8B3A2B',
                    'secondary' => '#D6A85F',
                    'background' => '#FFF9F2',
                    'text' => '#241A16',
                ],
                'typography' => ['headingFont' => 'Playfair Display', 'bodyFont' => 'Inter'],
                'borderRadius' => '4px',
            ],
            'navigation_config' => [
                'header' => [
                    'showLogo' => true,
                    'items' => [
                        ['label' => 'Home', 'slug' => 'home'],
                        ['label' => 'Menu', 'slug' => 'menu'],
                        ['label' => 'About', 'slug' => 'about'],
                    ],
                    'ctaButton' => ['show' => true, 'label' => 'Reserve', 'slug' => 'reservations'],
                ],
                'footer' => [
                    'variant' => 'columns',
                    'moduleOrder' => ['brand', 'links', 'social', 'copyright'],
                    'backgroundMode' => 'contrast',
                    'contentWidth' => 1200,
                    'showLinks' => true,
                    'showSocial' => true,
                    'showCopyright' => true,
                    'brand' => [
                        'show' => true,
                        'title' => 'Restaurant',
                        'description' => 'Seasonal cooking, generous hospitality, and memorable evenings around the table.',
                        'imageUrl' => '',
                        'alt' => 'Restaurant logo',
                    ],
                    'linkGroups' => [
                        ['id' => 'restaurant-footer-visit', 'label' => 'Visit', 'links' => [
                            ['id' => 'restaurant-footer-home', 'label' => 'Home', 'type' => 'internal', 'slug' => 'home'],
                            ['id' => 'restaurant-footer-menu', 'label' => 'Menu', 'type' => 'internal', 'slug' => 'menu'],
                            ['id' => 'restaurant-footer-reservations', 'label' => 'Reservations', 'type' => 'internal', 'slug' => 'reservations'],
                        ]],
                        ['id' => 'restaurant-footer-story', 'label' => 'Restaurant', 'links' => [
                            ['id' => 'restaurant-footer-about', 'label' => 'Our story', 'type' => 'internal', 'slug' => 'about'],
                        ]],
                    ],
                    'newsletter' => ['show' => false, 'eyebrow' => 'From the restaurant', 'heading' => 'Seasonal notes and special evenings', 'body' => 'Direct visitors to your preferred mailing-list or contact service.', 'buttonLabel' => 'Stay in touch', 'buttonUrl' => '/reservations'],
                    'socialLinks' => [
                        ['id' => 'restaurant-instagram', 'label' => 'Instagram', 'href' => 'https://instagram.com'],
                    ],
                    'copyright' => '',
                ],
            ],
        ],
        'retail-editorial' => [
            'label' => 'Retail Editorial',
            'theme_config' => [
                'colors' => [
                    'primary' => '#111827',
                    'secondary' => '#C08457',
                    'background' => '#FAFAF9',
                    'text' => '#1C1917',
                ],
                'typography' => ['headingFont' => 'Outfit', 'bodyFont' => 'Inter'],
                'borderRadius' => '0px',
            ],
            'navigation_config' => [
                'header' => [
                    'showLogo' => true,
                    'items' => [
                        ['label' => 'Home', 'slug' => 'home'],
                        ['label' => 'Shop', 'slug' => 'shop'],
                        ['label' => 'About', 'slug' => 'about'],
                    ],
                    'ctaButton' => ['show' => true, 'label' => 'Contact', 'slug' => 'contact'],
                ],
                'footer' => [
                    'variant' => 'editorial',
                    'moduleOrder' => ['newsletter', 'brand', 'links', 'social', 'copyright'],
                    'backgroundMode' => 'contrast',
                    'contentWidth' => 1280,
                    'showLinks' => true,
                    'showSocial' => true,
                    'showCopyright' => true,
                    'brand' => [
                        'show' => true,
                        'title' => 'Retail',
                        'description' => 'A considered edit of useful, beautiful pieces made to live beyond the season.',
                        'imageUrl' => '',
                        'alt' => 'Retail logo',
                    ],
                    'linkGroups' => [
                        ['id' => 'retail-footer-shop', 'label' => 'Shop', 'links' => [
                            ['id' => 'retail-footer-home', 'label' => 'Home', 'type' => 'internal', 'slug' => 'home'],
                            ['id' => 'retail-footer-catalog', 'label' => 'All products', 'type' => 'internal', 'slug' => 'shop'],
                            ['id' => 'retail-footer-cart', 'label' => 'Cart', 'type' => 'internal', 'slug' => 'cart'],
                        ]],
                        ['id' => 'retail-footer-company', 'label' => 'Company', 'links' => [
                            ['id' => 'retail-footer-about', 'label' => 'Our approach', 'type' => 'internal', 'slug' => 'about'],
                            ['id' => 'retail-footer-contact', 'label' => 'Contact', 'type' => 'internal', 'slug' => 'contact'],
                        ]],
                    ],
                    'newsletter' => ['show' => true, 'eyebrow' => 'The occasional note', 'heading' => 'New objects, makers, and stories', 'body' => 'Connect this callout to your preferred mailing-list service or contact page.', 'buttonLabel' => 'Stay in touch', 'buttonUrl' => '/contact'],
                    'socialLinks' => [
                        ['id' => 'retail-instagram', 'label' => 'Instagram', 'href' => 'https://instagram.com'],
                        ['id' => 'retail-pinterest', 'label' => 'Pinterest', 'href' => 'https://pinterest.com'],
                    ],
                    'copyright' => '',
                ],
                'commerce' => ['enabled' => true, 'cart_slug' => 'cart', 'product_slug' => 'product'],
            ],
        ],
        'hotel-refined' => [
            'label' => 'Hotel Refined',
            'theme_config' => [
                'colors' => [
                    'primary' => '#153B4A',
                    'secondary' => '#C9A86A',
                    'background' => '#F7F5F0',
                    'text' => '#1E2930',
                ],
                'typography' => ['headingFont' => 'EB Garamond', 'bodyFont' => 'Instrument Sans'],
                'borderRadius' => '4px',
            ],
            'navigation_config' => [
                'header' => [
                    'showLogo' => true,
                    'items' => [
                        ['label' => 'Home', 'slug' => 'home'],
                        ['label' => 'Rooms', 'slug' => 'rooms'],
                        ['label' => 'Amenities', 'slug' => 'amenities'],
                    ],
                    'ctaButton' => ['show' => true, 'label' => 'Book a stay', 'slug' => 'contact'],
                ],
                'footer' => [
                    'variant' => 'newsletter',
                    'moduleOrder' => ['brand', 'links', 'newsletter', 'social', 'copyright'],
                    'backgroundMode' => 'contrast',
                    'contentWidth' => 1240,
                    'showLinks' => true,
                    'showSocial' => true,
                    'showCopyright' => true,
                    'brand' => [
                        'show' => true,
                        'title' => 'Hotel',
                        'description' => 'A refined retreat shaped by thoughtful design, attentive service, and a strong sense of place.',
                        'imageUrl' => '',
                        'alt' => 'Hotel logo',
                    ],
                    'linkGroups' => [
                        ['id' => 'hotel-footer-stay', 'label' => 'Your stay', 'links' => [
                            ['id' => 'hotel-footer-home', 'label' => 'Home', 'type' => 'internal', 'slug' => 'home'],
                            ['id' => 'hotel-footer-rooms', 'label' => 'Rooms and suites', 'type' => 'internal', 'slug' => 'rooms'],
                            ['id' => 'hotel-footer-amenities', 'label' => 'Amenities', 'type' => 'internal', 'slug' => 'amenities'],
                        ]],
                        ['id' => 'hotel-footer-plan', 'label' => 'Plan', 'links' => [
                            ['id' => 'hotel-footer-contact', 'label' => 'Contact the hotel', 'type' => 'internal', 'slug' => 'contact'],
                        ]],
                    ],
                    'newsletter' => ['show' => true, 'eyebrow' => 'Plan something memorable', 'heading' => 'Let us shape your stay', 'body' => 'Tell the hotel what brings you here and the team can follow up with recommendations.', 'buttonLabel' => 'Send an enquiry', 'buttonUrl' => '/contact'],
                    'socialLinks' => [
                        ['id' => 'hotel-instagram', 'label' => 'Instagram', 'href' => 'https://instagram.com'],
                    ],
                    'copyright' => '',
                ],
            ],
        ],
    ],

    'page_layouts' => [
        'restaurant-home' => [
            'label' => 'Restaurant Home',
            'page_type' => 'home',
            'industry' => 'restaurant',
            'blocks' => [
                $heroSection('restaurant-home-hero', 'A table worth gathering around', 'Seasonal cooking, generous hospitality, and memorable evenings in the heart of the neighborhood.', 'hero-split-right', 'Seasonal restaurant · Neighborhood hospitality', 'Explore the menu', '/menu', 'Replace with a signature dining-room or tablescape image'),
                $imageBlock('restaurant-home-image', 'Replace with a signature restaurant or dining-room image', '480px'),
                $buttonBlock('restaurant-home-menu-button', 'Explore the menu', '/menu'),
                $headingBlock('restaurant-home-highlights-heading', 'What makes the experience special'),
                $featureGrid('restaurant-home-highlights', [
                    ['title' => 'Season-led menus', 'body' => 'Thoughtful dishes built around peak ingredients and trusted local producers.'],
                    ['title' => 'Warm hospitality', 'body' => 'Attentive, unhurried service that makes every table feel welcome.'],
                    ['title' => 'Private gatherings', 'body' => 'Flexible spaces and tailored menus for celebrations, teams, and families.'],
                ]),
                $imageWithText('restaurant-home-story', [
                    'eyebrow' => 'From the kitchen',
                    'heading' => 'Cooking that follows the seasons',
                    'body' => 'Our menus change with the market. The kitchen works closely with nearby growers and fishers, so every plate reflects what is best right now.',
                    'imageSrc' => '',
                    'imageAlt' => 'Replace with a chef-at-work or open-kitchen image',
                    'imagePosition' => 'left',
                    'linkLabel' => 'Meet the team',
                    'linkUrl' => '/about',
                ]),
                $galleryGrid('restaurant-home-gallery', [
                    'Replace with a plated dish image',
                    'Replace with a dining-room atmosphere image',
                    'Replace with a bar or drinks image',
                ]),
                $testimonialBlock('restaurant-home-testimonial', 'Every course felt considered, and the room had exactly the right energy.', 'A recent guest', 'Dinner service'),
                $headingBlock('restaurant-home-cta-heading', 'Join us for an evening'),
                $buttonBlock('restaurant-home-reserve-button', 'Reserve a table', '/reservations'),
            ],
        ],
        'restaurant-menu' => [
            'label' => 'Restaurant Menu',
            'page_type' => 'menu',
            'industry' => 'restaurant',
            'blocks' => [
                $heroSection('restaurant-menu-hero', 'The menu', 'A concise, seasonal selection designed for sharing, lingering, and discovering something new.', 'hero-editorial', 'From the kitchen · Updated seasonally', 'Reserve a table', '/reservations', 'Replace with an expressive seasonal dish or kitchen image'),
                $imageBlock('restaurant-menu-image', 'Replace with a seasonal dish or menu image', '360px'),
                $menuBlock('restaurant-menu-list', 'À la carte', 'Served daily · Kitchen closes at 10pm', [
                    ['category' => 'To begin', 'name' => 'Garden plate', 'description' => 'Market vegetables, cultured cream, herbs, and toasted seeds.', 'price' => '$14'],
                    ['category' => 'To begin', 'name' => 'House bread', 'description' => 'Warm sourdough, whipped butter, and smoked sea salt.', 'price' => '$8'],
                    ['category' => 'To begin', 'name' => 'Daily crudo', 'description' => 'Fresh catch, citrus, aromatic oil, and a bright seasonal garnish.', 'price' => '$18'],
                    ['category' => 'From the kitchen', 'name' => 'Fire-roasted fish', 'description' => 'Charred greens, lemon, capers, and a light herb sauce.', 'price' => '$32'],
                    ['category' => 'From the kitchen', 'name' => 'Slow-cooked short rib', 'description' => 'Silky mash, glazed roots, and rich pan jus.', 'price' => '$34'],
                    ['category' => 'From the kitchen', 'name' => 'Wild mushroom grain bowl', 'description' => 'Ancient grains, woodland mushrooms, greens, and aged cheese.', 'price' => '$26'],
                    ['category' => 'To finish', 'name' => 'Dark chocolate tart', 'description' => 'Sea salt, crème fraîche, and cocoa nib.', 'price' => '$12'],
                    ['category' => 'To finish', 'name' => 'Seasonal fruit crumble', 'description' => 'Warm oat crumble with vanilla custard.', 'price' => '$11'],
                ]),
                $galleryGrid('restaurant-menu-gallery', [
                    'Replace with a starter course image',
                    'Replace with a main course image',
                    'Replace with a dessert image',
                ], '240px'),
                $buttonBlock('restaurant-menu-reserve-button', 'Reserve a table', '/reservations'),
            ],
        ],
        'restaurant-about' => [
            'label' => 'Restaurant About',
            'page_type' => 'about',
            'industry' => 'restaurant',
            'blocks' => [
                $heroSection('restaurant-about-hero', 'Made with care, served with heart', 'Good ingredients, skilled hands, and genuine hospitality under one roof.', 'hero-split-left', 'Our story · Our people', 'Reserve a table', '/reservations', 'Replace with a chef, team, or restaurant story image'),
                $imageBlock('restaurant-about-image', 'Replace with a chef, team, or restaurant story image'),
                $richTextBlock('restaurant-about-story', '<h2>Our story</h2><p>We opened with a simple idea: create a place where the food is thoughtful, the welcome is easy, and guests always have a reason to return. Our menus follow the seasons and our team works closely with producers who care about quality as much as we do.</p><p>From a quick midweek supper to a milestone celebration, every service is built around the people at the table.</p>'),
                $featureGrid('restaurant-about-values', [
                    ['title' => 'Local relationships', 'body' => 'Long-term partnerships with growers, fishers, bakers, and makers.'],
                    ['title' => 'Low-waste thinking', 'body' => 'Whole-ingredient cooking and careful purchasing guide the kitchen.'],
                    ['title' => 'People first', 'body' => 'A supportive team culture translates into better hospitality for every guest.'],
                ]),
                $imageWithText('restaurant-about-room', [
                    'eyebrow' => 'The room',
                    'heading' => 'Designed for lingering',
                    'body' => 'Soft light, warm materials, and tables with breathing room. The space is built for long dinners and easy conversation.',
                    'imageSrc' => '',
                    'imageAlt' => 'Replace with a dining-room interior image',
                    'imagePosition' => 'right',
                    'linkLabel' => 'Reserve a table',
                    'linkUrl' => '/reservations',
                ]),
                $testimonialBlock('restaurant-about-testimonial', 'The kind of place that remembers your name and your favorite table.', 'A regular guest', 'Neighborhood resident'),
            ],
        ],
        'restaurant-reservations' => [
            'label' => 'Restaurant Reservations',
            'page_type' => 'contact',
            'industry' => 'restaurant',
            'blocks' => [
                $heroSection('restaurant-reservations-hero', 'Reserve your table', 'Send a reservation enquiry and tell us anything we should know before you arrive.', 'hero-minimal', 'Dining enquiries', 'View the menu', '/menu', 'Replace with a considered table-setting image'),
                $imageBlock('restaurant-reservations-image', 'Replace with a table setting or private dining image', '320px'),
                $richTextBlock('restaurant-reservations-details', '<p>This form sends an enquiry; it does not confirm live availability. For larger parties, private dining, accessibility requests, or dietary questions, include the details below and the restaurant team can follow up directly.</p>'),
                $contactFormBlock('restaurant-reservations-form', 'Request a reservation', 'Thank you. The restaurant team can now follow up about availability.', 'Preferred date, time, party size, and any special requests'),
                $faqBlock('restaurant-reservations-faq', [
                    ['question' => 'Do you welcome walk-ins?', 'answer' => 'Add your walk-in policy here — for example, bar seating held for guests without a reservation.'],
                    ['question' => 'Can you host larger groups?', 'answer' => 'Describe your private dining or group options and how far in advance to enquire.'],
                    ['question' => 'How do you handle dietary requirements?', 'answer' => 'Explain how the kitchen accommodates allergies and preferences when notified ahead of a visit.'],
                ]),
            ],
        ],
        'retail-home' => [
            'label' => 'Retail Home',
            'page_type' => 'home',
            'industry' => 'retail',
            'blocks' => [
                $storeBlock('retail-home-announcement', 'AnnouncementBlock', ['text' => 'Complimentary delivery on selected orders'], 0),
                $heroSection('retail-home-hero', 'Objects with a life beyond the season', 'A considered edit of useful, beautiful pieces for home and daily ritual.', 'hero-editorial', 'The current edit · Objects for living', 'Shop the collection', '/shop', 'Replace with a full-width editorial campaign image'),
                $imageBlock('retail-home-campaign', 'Replace with a full-width seasonal campaign image', '560px'),
                $buttonBlock('retail-home-shop-button', 'Shop the current edit', '/shop'),
                $withReveal($storeBlock('retail-home-collections', 'CollectionListBlock', ['eyebrow' => 'Shop by collection', 'heading' => 'Find your everyday favorites', 'bindingVersion' => 1, 'sourceKey' => 'featured', 'collections' => [
                    ['title' => 'Home', 'subtitle' => 'Objects for considered rooms', 'imageSrc' => '', 'imageAlt' => 'Replace with a home collection image', 'url' => '/shop'],
                    ['title' => 'Wear', 'subtitle' => 'Useful pieces made to last', 'imageSrc' => '', 'imageAlt' => 'Replace with a wear collection image', 'url' => '/shop'],
                    ['title' => 'Gift', 'subtitle' => 'Thoughtful finds for giving', 'imageSrc' => '', 'imageAlt' => 'Replace with a gift collection image', 'url' => '/shop'],
                ]])),
                $withReveal($storeBlock('retail-home-products', 'ProductGridBlock', ['eyebrow' => 'Curated selection', 'heading' => 'The current edit', 'bindingVersion' => 1, 'sourceKey' => 'featured', 'limit' => 4, 'columns' => 4, 'viewAllLabel' => 'View all', 'viewAllUrl' => '/shop', 'products' => $starterProducts])),
                $withReveal($storeBlock('retail-home-story', 'ImageWithTextBlock', ['eyebrow' => 'Our point of view', 'heading' => 'Fewer, better things', 'body' => 'We select honest materials, enduring forms, and pieces that become more familiar with use.', 'imageSrc' => '', 'imageAlt' => 'Replace with a maker or studio image', 'imagePosition' => 'left', 'linkLabel' => 'Read our story', 'linkUrl' => '/about'], 0)),
                $withReveal($storeBlock('retail-home-values', 'TrustValuesBlock', ['items' => [['title' => 'Considered sourcing', 'body' => 'Materials and makers selected with care.'], ['title' => 'Personal service', 'body' => 'Helpful guidance from real people.'], ['title' => 'Secure checkout', 'body' => 'Ready for your connected store experience.']]])),
                $withReveal($storeBlock('retail-home-newsletter', 'NewsletterBlock', ['eyebrow' => 'Stay in touch', 'heading' => 'Notes from the shop', 'body' => 'New collections and thoughtful stories, occasionally.', 'placeholder' => 'Email address', 'buttonLabel' => 'Subscribe'], 0)),
            ],
        ],
        'retail-shop' => [
            'label' => 'Retail Shop',
            'page_type' => 'shop',
            'industry' => 'retail',
            'blocks' => [
                $storeBlock('retail-shop-announcement', 'AnnouncementBlock', ['text' => 'The current collection · selected in small runs'], 0),
                $heroSection('retail-shop-hero', 'Shop the collection', 'Browse an editorial catalog ready to be hydrated by your connected products and collections.', 'hero-minimal', 'New arrivals · Considered essentials', 'Our approach', '/about', 'Replace with a curated collection image'),
                $imageBlock('retail-shop-campaign', 'Replace with a shop or collection campaign image', '420px'),
                $withReveal($storeBlock('retail-shop-grid', 'ProductGridBlock', ['eyebrow' => 'All products', 'heading' => 'Objects for everyday life', 'bindingVersion' => 1, 'sourceKey' => 'all', 'limit' => 12, 'columns' => 4, 'showControls' => true, 'pageSize' => 4, 'viewAllLabel' => '', 'viewAllUrl' => '', 'products' => $starterProducts])),
                $withReveal($storeBlock('retail-shop-values', 'TrustValuesBlock', ['items' => [['title' => 'Small-run selection', 'body' => 'A focused assortment rather than endless inventory.'], ['title' => 'Material clarity', 'body' => 'Make care, origin, and materials easy to understand.'], ['title' => 'Human support', 'body' => 'Invite questions before and after purchase.']]])),
                $richTextBlock('retail-shop-notice', '<p>This starter storefront is presentation-ready. Product keys provide a stable future hydration boundary; live inventory and checkout require a connected commerce provider.</p>'),
            ],
        ],
        'retail-product' => [
            'label' => 'Retail Product', 'page_type' => 'product', 'industry' => 'retail',
            'blocks' => [
                $heroSection('retail-product-hero', 'One piece, considered closely', 'Give every product room for its materials, details, and story before the connected store supplies live availability.', 'hero-minimal', 'Product story · Material detail', 'Return to the collection', '/shop', 'Replace with an editorial product detail image'),
                $withReveal($storeBlock('retail-product-detail', 'ProductDetailBlock', ['bindingVersion' => 1, 'sourceKey' => 'linen-throw', 'vendor' => 'Independent maker', 'title' => 'Linen throw', 'priceLabel' => '$48.00', 'description' => 'A tactile everyday layer made with considered materials and a relaxed, lived-in finish.', 'options' => ['Natural', 'Charcoal'], 'images' => [['src' => '', 'alt' => 'Replace with the main product image'], ['src' => '', 'alt' => 'Replace with a product detail image']], 'buttonLabel' => 'Add to cart', 'meta' => 'Taxes, inventory, and checkout will be supplied by the connected store.']), 'fade-in'),
                $imageBlock('retail-product-lifestyle', 'Replace with a full-width product lifestyle image', '520px'),
                $withReveal($storeBlock('retail-product-related', 'ProductGridBlock', ['eyebrow' => 'Complete the edit', 'heading' => 'You may also like', 'bindingVersion' => 1, 'sourceKey' => 'related', 'limit' => 4, 'columns' => 4, 'viewAllLabel' => 'View collection', 'viewAllUrl' => '/shop', 'products' => $starterProducts])),
                $withReveal($storeBlock('retail-product-newsletter', 'NewsletterBlock', ['eyebrow' => 'Stay in touch', 'heading' => 'Notes from the shop', 'body' => 'New collections and thoughtful stories, occasionally.', 'placeholder' => 'Email address', 'buttonLabel' => 'Subscribe'], 0)),
            ],
        ],
        'retail-cart' => [
            'label' => 'Retail — Cart', 'page_type' => 'cart', 'industry' => 'Retail',
            'blocks' => [
                $heroSection('retail-cart-hero', 'Your edit, gathered', 'Review the pieces you have selected before continuing to the provider-owned checkout experience.', 'hero-minimal', 'Shopping bag · Review before checkout', 'Continue shopping', '/shop', 'Replace with a thoughtful packaging or customer-service image'),
                $withReveal($storeBlock('retail-cart-main', 'CartBlock', ['eyebrow' => 'Your selection', 'heading' => 'Shopping bag', 'body' => 'Review your items before continuing to the provider-owned checkout.', 'emptyHeading' => 'Your bag is empty', 'emptyBody' => 'Explore the current collection and find something considered.', 'continueUrl' => '/shop', 'checkoutLabel' => 'Continue to fixture checkout']), 'fade-in'),
                $withReveal($storeBlock('retail-cart-values', 'TrustValuesBlock', ['items' => [['title' => 'Authoritative totals', 'body' => 'Prices and totals come from the active provider.'], ['title' => 'Availability checked', 'body' => 'Variant availability is checked before checkout.'], ['title' => 'Hosted checkout', 'body' => 'Payment remains outside the page builder.']]])),
                $imageBlock('retail-cart-editorial', 'Replace with a customer service or packaging image', '360px'),
            ],
        ],
        'retail-about' => [
            'label' => 'Retail About',
            'page_type' => 'about',
            'industry' => 'retail',
            'blocks' => [
                $heroSection('retail-about-hero', 'A slower approach to retail', 'We choose fewer, better things—and share the stories, materials, and people behind them.', 'hero-split-right', 'Materials · Makers · Meaning', 'Explore the collection', '/shop', 'Replace with a maker, material, or workshop image'),
                $imageBlock('retail-about-image', 'Replace with a founder, studio, or brand story image'),
                $richTextBlock('retail-about-story', '<h2>How we curate</h2><p>Our assortment balances independent makers, established studios, and practical design. We look for honest materials, responsible production, and pieces that become more familiar with time.</p><p>The result is an evolving collection with a clear point of view and room for discovery.</p>'),
                $featureGrid('retail-about-values', [
                    ['title' => 'Purposeful selection', 'body' => 'Every item is considered for usefulness, craft, and lasting appeal.'],
                    ['title' => 'Maker relationships', 'body' => 'We value transparent sourcing and direct, respectful partnerships.'],
                    ['title' => 'Personal service', 'body' => 'Knowledgeable recommendations without pressure or unnecessary noise.'],
                ]),
                $galleryGrid('retail-about-gallery', [
                    'Replace with a workshop or making-process image',
                    'Replace with a materials close-up image',
                    'Replace with a finished-product styling image',
                ], '240px'),
            ],
        ],
        'retail-contact' => [
            'label' => 'Retail Contact',
            'page_type' => 'contact',
            'industry' => 'retail',
            'blocks' => [
                $heroSection('retail-contact-hero', 'We are here to help', 'Ask about product details, availability, gifting, local pickup, or an existing order.', 'hero-centered', 'Personal service', 'Browse the shop', '/shop', 'Replace with a storefront or service image'),
                $imageBlock('retail-contact-image', 'Replace with a storefront, service desk, or packaging image', '320px'),
                $featureGrid('retail-contact-options', [
                    ['title' => 'Visit the shop', 'body' => 'Add your address, opening hours, parking, and accessibility details here.'],
                    ['title' => 'Product support', 'body' => 'Share the item name so the team can help as quickly as possible.'],
                    ['title' => 'Trade enquiries', 'body' => 'Tell us about sourcing, wholesale, press, or partnership opportunities.'],
                ]),
                $contactFormBlock('retail-contact-form', 'Send enquiry', 'Thanks for getting in touch. The shop team can now reply to your enquiry.', 'How can we help?'),
            ],
        ],
        'hotel-home' => [
            'label' => 'Hotel Home',
            'page_type' => 'home',
            'industry' => 'hotel',
            'blocks' => [
                $heroSection('hotel-home-hero', 'Arrive, exhale, stay awhile', 'A refined retreat where thoughtful design, attentive service, and a strong sense of place come together.', 'hero-split-left', 'A considered stay · A strong sense of place', 'Explore rooms', '/rooms', 'Replace with the hotel exterior, lobby, or destination view'),
                $imageBlock('hotel-home-image', 'Replace with the hotel exterior, lobby, or destination image', '480px'),
                $buttonBlock('hotel-home-rooms-button', 'Explore rooms', '/rooms'),
                $headingBlock('hotel-home-experience-heading', 'Your stay, considered'),
                $featureGrid('hotel-home-experience', [
                    ['title' => 'Restful rooms', 'body' => 'Calm interiors, quality linens, and the details that make settling in effortless.'],
                    ['title' => 'Local character', 'body' => 'Experiences shaped by the landscape, culture, and people around us.'],
                    ['title' => 'Genuine service', 'body' => 'Warm, observant hospitality tailored to the way you prefer to travel.'],
                ]),
                $imageWithText('hotel-home-suite-story', [
                    'eyebrow' => 'Rooms and suites',
                    'heading' => 'Space to slow down',
                    'body' => 'Each room balances calm interiors with a strong sense of place — natural materials, soft light, and views worth waking up for.',
                    'imageSrc' => '',
                    'imageAlt' => 'Replace with a suite interior or balcony-view image',
                    'imagePosition' => 'right',
                    'linkLabel' => 'See rooms and suites',
                    'linkUrl' => '/rooms',
                ]),
                $galleryGrid('hotel-home-gallery', [
                    'Replace with a guest-room detail image',
                    'Replace with a breakfast or dining image',
                    'Replace with a landscape or destination image',
                ]),
                $testimonialBlock('hotel-home-testimonial', 'Beautifully quiet, deeply comfortable, and cared for from the moment we arrived.', 'A recent guest', 'Weekend stay'),
                $headingBlock('hotel-home-cta-heading', 'Begin planning your stay'),
                $buttonBlock('hotel-home-enquire-button', 'Send a stay enquiry', '/contact'),
            ],
        ],
        'hotel-rooms' => [
            'label' => 'Hotel Rooms',
            'page_type' => 'rooms',
            'industry' => 'hotel',
            'blocks' => [
                $heroSection('hotel-rooms-hero', 'Rooms and suites', 'Comfortable, characterful spaces designed for deep rest, slow mornings, and unhurried stays.', 'hero-editorial', 'Rest well · Wake slowly', 'Send a stay enquiry', '/contact', 'Replace with a signature room, suite, or balcony view'),
                $imageBlock('hotel-rooms-image', 'Replace with a guest room or suite image'),
                $featureGrid('hotel-rooms-options', [
                    ['title' => 'Classic room', 'body' => 'An elegant base with a queen bed, walk-in shower, and considered essentials.'],
                    ['title' => 'Terrace room', 'body' => 'More room to unwind, with a private outdoor space and generous natural light.'],
                    ['title' => 'Signature suite', 'body' => 'A separate living area, premium amenities, and the most expansive outlook.'],
                ]),
                $imageWithText('hotel-rooms-signature', [
                    'eyebrow' => 'The signature suite',
                    'heading' => 'Our most requested stay',
                    'body' => 'A separate living space, a deep soaking tub, and the best outlook in the house. Ideal for special occasions and longer visits.',
                    'imageSrc' => '',
                    'imageAlt' => 'Replace with a signature suite image',
                    'imagePosition' => 'left',
                    'linkLabel' => 'Enquire about this suite',
                    'linkUrl' => '/contact',
                ]),
                $richTextBlock('hotel-rooms-inclusions', '<h2>Included with every stay</h2><p>High-speed Wi-Fi, daily housekeeping, premium bath amenities, in-room refreshments, and support from the guest team throughout your visit.</p>'),
                $galleryGrid('hotel-rooms-gallery', [
                    'Replace with a bed and linens detail image',
                    'Replace with a bathroom detail image',
                    'Replace with a room-view or terrace image',
                ], '240px'),
                $buttonBlock('hotel-rooms-book-button', 'Send a stay enquiry', '/contact'),
            ],
        ],
        'hotel-amenities' => [
            'label' => 'Hotel Amenities',
            'page_type' => 'amenities',
            'industry' => 'hotel',
            'blocks' => [
                $heroSection('hotel-amenities-hero', 'Everything you need, nothing you do not', 'Spaces and services that make each day easier, richer, and distinctly your own.', 'hero-split-right', 'Wellbeing · Dining · Discovery', 'Plan your stay', '/contact', 'Replace with a spa, pool, dining, or destination image'),
                $imageBlock('hotel-amenities-image', 'Replace with a dining, wellness, pool, or guest-experience image'),
                $featureGrid('hotel-amenities-list', [
                    ['title' => 'Breakfast and dining', 'body' => 'Seasonal menus, locally roasted coffee, and relaxed spaces from morning onward.'],
                    ['title' => 'Wellness', 'body' => 'A calm place to move, recover, and reset during your stay.'],
                    ['title' => 'Work and gatherings', 'body' => 'Flexible rooms with practical support for meetings and private occasions.'],
                    ['title' => 'Concierge', 'body' => 'Local recommendations, reservations, transport, and tailored itineraries.'],
                    ['title' => 'Family stays', 'body' => 'Useful extras and thoughtful arrangements for guests travelling with children.'],
                    ['title' => 'Accessible hospitality', 'body' => 'Clear information and responsive support for individual access requirements.'],
                ]),
                $galleryGrid('hotel-amenities-gallery', [
                    'Replace with a wellness or pool image',
                    'Replace with a restaurant or lounge image',
                    'Replace with a garden, terrace, or shared-space image',
                ], '240px'),
            ],
        ],
        'hotel-contact' => [
            'label' => 'Hotel Contact',
            'page_type' => 'contact',
            'industry' => 'hotel',
            'blocks' => [
                $heroSection('hotel-contact-hero', 'Plan your stay', 'Ask about dates, room recommendations, special occasions, group stays, or anything else the hotel can arrange.', 'hero-centered', 'Stay enquiries', 'Explore rooms', '/rooms', 'Replace with a welcoming arrival or destination image'),
                $imageBlock('hotel-contact-image', 'Replace with a destination, arrival, or guest-service image', '320px'),
                $richTextBlock('hotel-contact-details', '<p>This form sends a booking enquiry; it does not check live inventory or confirm a reservation. Share your preferred dates, number of guests, and priorities for the stay so the reservations team can respond with suitable options.</p>'),
                $contactFormBlock('hotel-contact-form', 'Send stay enquiry', 'Thank you. The reservations team can now follow up about your stay.', 'Preferred dates, guest count, room type, and any special requests'),
                $faqBlock('hotel-contact-faq', [
                    ['question' => 'What are check-in and check-out times?', 'answer' => 'Add your standard times here, plus how early arrival or late departure requests are handled.'],
                    ['question' => 'Is parking available?', 'answer' => 'Describe on-site or nearby parking, charges, and anything guests should arrange ahead.'],
                    ['question' => 'Can you host celebrations or group stays?', 'answer' => 'Explain how the team supports special occasions, events, and multi-room bookings.'],
                ]),
            ],
        ],
    ],

    'site_kits' => [
        'restaurant' => [
            'label' => 'Restaurant',
            'industry' => 'Food and Dining',
            'description' => 'A warm editorial site for menus, story, hospitality, and reservation enquiries.',
            'tier' => 'free',
            'style_key' => 'restaurant-warmth',
            'pages' => [
                ['title' => 'Home', 'slug' => 'home', 'layout_key' => 'restaurant-home', 'is_homepage' => true],
                ['title' => 'Menu', 'slug' => 'menu', 'layout_key' => 'restaurant-menu', 'is_homepage' => false],
                ['title' => 'About', 'slug' => 'about', 'layout_key' => 'restaurant-about', 'is_homepage' => false],
                ['title' => 'Reservations', 'slug' => 'reservations', 'layout_key' => 'restaurant-reservations', 'is_homepage' => false],
            ],
        ],
        'retail' => [
            'label' => 'Retail',
            'industry' => 'Retail and Commerce',
            'description' => 'A modifiable editorial storefront with fixture-backed catalog, product, cart, and checkout previews.',
            'tier' => 'free',
            'style_key' => 'retail-editorial',
            'pages' => [
                ['title' => 'Home', 'slug' => 'home', 'layout_key' => 'retail-home', 'is_homepage' => true],
                ['title' => 'Shop', 'slug' => 'shop', 'layout_key' => 'retail-shop', 'is_homepage' => false],
                ['title' => 'Product', 'slug' => 'product', 'layout_key' => 'retail-product', 'is_homepage' => false],
                ['title' => 'Cart', 'slug' => 'cart', 'layout_key' => 'retail-cart', 'is_homepage' => false],
                ['title' => 'About', 'slug' => 'about', 'layout_key' => 'retail-about', 'is_homepage' => false],
                ['title' => 'Contact', 'slug' => 'contact', 'layout_key' => 'retail-contact', 'is_homepage' => false],
            ],
        ],
        'hotel' => [
            'label' => 'Hotel',
            'industry' => 'Hospitality and Accommodation',
            'description' => 'A refined hospitality site for rooms, amenities, guest experience, and booking enquiries.',
            'tier' => 'free',
            'style_key' => 'hotel-refined',
            'pages' => [
                ['title' => 'Home', 'slug' => 'home', 'layout_key' => 'hotel-home', 'is_homepage' => true],
                ['title' => 'Rooms', 'slug' => 'rooms', 'layout_key' => 'hotel-rooms', 'is_homepage' => false],
                ['title' => 'Amenities', 'slug' => 'amenities', 'layout_key' => 'hotel-amenities', 'is_homepage' => false],
                ['title' => 'Contact', 'slug' => 'contact', 'layout_key' => 'hotel-contact', 'is_homepage' => false],
            ],
        ],
    ],
];
