<?php

use App\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->tenant = Tenant::factory()->withHomePage()->create(['user_id' => $this->user->id]);
    $this->page = $this->tenant->pages()->first();

    $this->actingAs($this->user);
});

test('duplicated block payload saves successfully', function () {
    $payload = [
        [
            'id' => 'hero-1',
            'type' => 'HeroBlock',
            'props' => ['padding' => 40, 'backgroundColor' => '#ffffff', 'headline' => 'Original', 'subheadline' => ''],
            'children' => [],
        ],
        [
            'id' => 'hero-2',
            'type' => 'HeroBlock',
            'props' => ['padding' => 40, 'backgroundColor' => '#ffffff', 'headline' => 'Duplicate', 'subheadline' => ''],
            'children' => [],
        ],
    ];

    $response = $this->postJson("http://{$this->tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $this->page->id,
        'draft_config' => $payload,
    ]);

    $response->assertOk();
    $response->assertJson(['status' => 'success']);
});

test('reordered block payload saves successfully', function () {
    $payload = [
        [
            'id' => 'feat-2',
            'type' => 'FeatureBlock',
            'props' => ['padding' => 20, 'backgroundColor' => '#f8fafc', 'title' => 'Second', 'body' => ''],
            'children' => [],
        ],
        [
            'id' => 'hero-1',
            'type' => 'HeroBlock',
            'props' => ['padding' => 40, 'backgroundColor' => '#ffffff', 'headline' => 'First', 'subheadline' => ''],
            'children' => [],
        ],
    ];

    $response = $this->postJson("http://{$this->tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $this->page->id,
        'draft_config' => $payload,
    ]);

    $response->assertOk();
    $response->assertJson(['status' => 'success']);
});

test('delete block payload saves successfully', function () {
    $payload = [
        [
            'id' => 'hero-1',
            'type' => 'HeroBlock',
            'props' => ['padding' => 40, 'backgroundColor' => '#ffffff', 'headline' => 'Survivor', 'subheadline' => ''],
            'children' => [],
        ],
    ];

    $response = $this->postJson("http://{$this->tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $this->page->id,
        'draft_config' => $payload,
    ]);

    $response->assertOk();
    $response->assertJson(['status' => 'success']);
});

test('block wrapped in container saves successfully', function () {
    $payload = [
        [
            'id' => 'column-1',
            'type' => 'LayoutColumn',
            'props' => ['padding' => 20, 'backgroundColor' => 'transparent', 'span' => 'auto', 'width' => 'auto', 'height' => 'auto', 'gap' => '0px'],
            'children' => [
                [
                    'id' => 'hero-1',
                    'type' => 'HeroBlock',
                    'props' => ['padding' => 40, 'backgroundColor' => '#ffffff', 'headline' => 'Wrapped', 'subheadline' => ''],
                    'children' => [],
                ],
            ],
        ],
    ];

    $response = $this->postJson("http://{$this->tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $this->page->id,
        'draft_config' => $payload,
    ]);

    $response->assertOk();
    $response->assertJson(['status' => 'success']);
});

test('duplicated nested block tree saves successfully', function () {
    $payload = [
        [
            'id' => 'grid-1',
            'type' => 'LayoutGrid',
            'props' => ['columns' => 2, 'gap' => '1rem', 'padding' => '1rem', 'backgroundColor' => 'transparent'],
            'children' => [
                [
                    'id' => 'col-1',
                    'type' => 'LayoutColumn',
                    'props' => ['padding' => 20, 'backgroundColor' => 'transparent', 'span' => 'auto', 'width' => 'auto', 'height' => 'auto', 'gap' => '0px'],
                    'children' => [
                        [
                            'id' => 'text-1',
                            'type' => 'AtomicText',
                            'props' => ['content' => 'Hello', 'fontSize' => '16px', 'color' => '#111', 'padding' => 0, 'backgroundColor' => 'transparent'],
                            'children' => [],
                        ],
                    ],
                ],
            ],
        ],
        [
            'id' => 'grid-2',
            'type' => 'LayoutGrid',
            'props' => ['columns' => 2, 'gap' => '1rem', 'padding' => '1rem', 'backgroundColor' => 'transparent'],
            'children' => [
                [
                    'id' => 'col-2',
                    'type' => 'LayoutColumn',
                    'props' => ['padding' => 20, 'backgroundColor' => 'transparent', 'span' => 'auto', 'width' => 'auto', 'height' => 'auto', 'gap' => '0px'],
                    'children' => [
                        [
                            'id' => 'text-2',
                            'type' => 'AtomicText',
                            'props' => ['content' => 'Hello Duplicated', 'fontSize' => '16px', 'color' => '#111', 'padding' => 0, 'backgroundColor' => 'transparent'],
                            'children' => [],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $response = $this->postJson("http://{$this->tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $this->page->id,
        'draft_config' => $payload,
    ]);

    $response->assertOk();
    $response->assertJson(['status' => 'success']);
});

test('blocks with reveal animation props save successfully', function () {
    $payload = [
        [
            'id' => 'hero-reveal',
            'type' => 'HeroBlock',
            'props' => ['padding' => 40, 'backgroundColor' => 'transparent', 'headline' => 'Animated', 'subheadline' => '', 'reveal' => 'fade-up', 'revealDelay' => 200],
            'children' => [],
        ],
        [
            'id' => 'grid-reveal',
            'type' => 'LayoutGrid',
            'props' => ['padding' => 20, 'backgroundColor' => 'transparent', 'columns' => 3, 'gap' => '1rem'],
            'children' => [
                [
                    'id' => 'image-reveal',
                    'type' => 'ImageBlock',
                    'props' => ['src' => '', 'alt' => 'Placeholder', 'padding' => 0, 'backgroundColor' => 'transparent', 'reveal' => 'scale-in'],
                    'children' => [],
                ],
            ],
        ],
    ];

    $response = $this->postJson("http://{$this->tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $this->page->id,
        'draft_config' => $payload,
    ]);

    $response->assertOk();
    $response->assertJson(['status' => 'success']);

    expect($this->page->refresh()->draft_config[0]['props']['reveal'])->toBe('fade-up')
        ->and($this->page->draft_config[0]['props']['revealDelay'])->toBe(200);
});

test('section pattern and content presets shaped payloads save successfully', function () {
    $payload = [
        [
            'id' => 'section-hero-centered',
            'type' => 'SectionBlock',
            'props' => [
                'patternKey' => 'hero-centered',
                'padding' => 0,
                'sectionPadding' => 72,
                'backgroundColor' => 'transparent',
                'backgroundImage' => '',
                'contentWidth' => 980,
                'minHeight' => 560,
                'verticalAlign' => 'center',
                'textAlign' => 'center',
                'overlayOpacity' => 0,
            ],
            'children' => [
                [
                    'id' => 'col-copy',
                    'type' => 'LayoutColumn',
                    'props' => [
                        'padding' => 0,
                        'backgroundColor' => 'transparent',
                        'horizontalAlign' => 'center',
                    ],
                    'children' => [
                        [
                            'id' => 'heading-role',
                            'type' => 'AtomicText',
                            'props' => [
                                'patternRole' => 'heading',
                                'content' => 'A clear statement',
                                'padding' => 0,
                                'backgroundColor' => 'transparent',
                                'color' => '--theme-text',
                            ],
                            'children' => [],
                        ],
                        [
                            'id' => 'cta-role',
                            'type' => 'ButtonBlock',
                            'props' => [
                                'patternRole' => 'primaryAction',
                                'label' => 'Explore',
                                'url' => '/menu',
                                'variant' => 'primary',
                                'size' => 'lg',
                            ],
                            'children' => [],
                        ],
                    ],
                ],
            ],
        ],
        [
            'id' => 'menu-preset',
            'type' => 'MenuBlock',
            'props' => [
                'heading' => 'Our Menu',
                'subheading' => 'Freshly prepared',
                'columns' => 2,
                'items' => [
                    ['category' => 'Starters', 'name' => 'Salad', 'description' => 'Fresh', 'price' => '$12'],
                ],
                'padding' => 40,
                'backgroundColor' => 'transparent',
            ],
            'children' => [],
        ],
        [
            'id' => 'products-preset',
            'type' => 'ProductGridBlock',
            'props' => [
                'eyebrow' => 'Curated',
                'heading' => 'The current edit',
                'bindingVersion' => 1,
                'sourceKey' => 'featured',
                'limit' => 4,
                'columns' => 4,
                'products' => [
                    ['key' => 'linen-throw', 'title' => 'Linen throw', 'priceLabel' => '$48.00', 'imageSrc' => '', 'imageAlt' => '', 'url' => '/product'],
                ],
                'padding' => 40,
                'backgroundColor' => 'transparent',
            ],
            'children' => [],
        ],
    ];

    $this->postJson("http://{$this->tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $this->page->id,
        'draft_config' => $payload,
    ])->assertOk()->assertJson(['status' => 'success']);

    $page = $this->page->refresh();

    expect($page->draft_config[0]['props']['patternKey'])->toBe('hero-centered')
        ->and($page->draft_config[0]['children'][0]['children'][0]['props']['patternRole'])->toBe('heading')
        ->and($page->draft_config[1]['type'])->toBe('MenuBlock')
        ->and($page->draft_config[2]['type'])->toBe('ProductGridBlock');
});

test('stat hero, logo strip, testimonials, and promo tile preset payloads save successfully', function () {
    $payload = [
        [
            'id' => 'section-hero-stats',
            'type' => 'SectionBlock',
            'props' => [
                'patternKey' => 'hero-stats',
                'padding' => 0,
                'sectionPadding' => 72,
                'backgroundColor' => 'transparent',
                'contentWidth' => 1180,
                'minHeight' => 560,
                'verticalAlign' => 'center',
                'textAlign' => 'left',
            ],
            'children' => [
                [
                    'id' => 'stats-grid',
                    'type' => 'LayoutGrid',
                    'props' => ['padding' => 0, 'backgroundColor' => 'transparent', 'columns' => 2, 'gap' => '2rem', 'columnTemplate' => 'wide-left'],
                    'children' => [
                        [
                            'id' => 'stats-callouts',
                            'type' => 'LayoutColumn',
                            'props' => ['padding' => 0, 'backgroundColor' => 'transparent', 'gap' => '14px'],
                            'children' => [
                                [
                                    'id' => 'stat-one-value',
                                    'type' => 'AtomicText',
                                    'props' => ['patternRole' => 'statOneValue', 'content' => '10k+', 'color' => '--theme-primary', 'padding' => 0, 'backgroundColor' => 'transparent'],
                                    'children' => [],
                                ],
                                [
                                    'id' => 'stat-one-label',
                                    'type' => 'AtomicText',
                                    'props' => ['patternRole' => 'statOneLabel', 'content' => 'Happy customers', 'color' => '--theme-text', 'padding' => 0, 'backgroundColor' => 'transparent'],
                                    'children' => [],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        [
            'id' => 'logo-strip',
            'type' => 'LayoutGrid',
            'props' => ['padding' => 40, 'backgroundColor' => 'transparent', 'columns' => 6, 'gap' => '1.5rem'],
            'children' => [
                [
                    'id' => 'logo-1',
                    'type' => 'ImageBlock',
                    'props' => ['src' => '', 'alt' => 'Brand logo 1 — replace from your media library', 'objectFit' => 'contain', 'height' => '48px', 'padding' => 0, 'backgroundColor' => 'transparent'],
                    'children' => [],
                ],
            ],
        ],
        [
            'id' => 'testimonials-row',
            'type' => 'LayoutGrid',
            'props' => ['padding' => 40, 'backgroundColor' => 'transparent', 'columns' => 3, 'gap' => '1.5rem'],
            'children' => [
                [
                    'id' => 'quote-1',
                    'type' => 'TestimonialBlock',
                    'props' => ['quote' => 'Beautifully made.', 'authorName' => 'Olivia R.', 'authorRole' => 'Verified customer', 'avatarSrc' => '', 'padding' => 10, 'backgroundColor' => 'transparent'],
                    'children' => [],
                ],
            ],
        ],
        [
            'id' => 'promo-tiles',
            'type' => 'LayoutGrid',
            'props' => ['padding' => 40, 'backgroundColor' => 'transparent', 'columns' => 2, 'gap' => '1.5rem'],
            'children' => [
                [
                    'id' => 'promo-tile-1',
                    'type' => 'LayoutColumn',
                    'props' => ['padding' => 36, 'backgroundColor' => 'var(--theme-primary)', 'gap' => '10px'],
                    'children' => [
                        [
                            'id' => 'promo-heading',
                            'type' => 'AtomicText',
                            'props' => ['content' => 'Season sale — up to 15% off', 'color' => '#ffffff', 'padding' => 0, 'backgroundColor' => 'transparent'],
                            'children' => [],
                        ],
                        [
                            'id' => 'promo-cta',
                            'type' => 'ButtonBlock',
                            'props' => ['label' => 'Shop the sale', 'variant' => 'secondary', 'url' => '/shop', 'size' => 'md', 'padding' => 0, 'backgroundColor' => 'transparent'],
                            'children' => [],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $this->postJson("http://{$this->tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $this->page->id,
        'draft_config' => $payload,
    ])->assertOk()->assertJson(['status' => 'success']);

    $page = $this->page->refresh();

    expect($page->draft_config[0]['props']['patternKey'])->toBe('hero-stats')
        ->and($page->draft_config[1]['children'][0]['type'])->toBe('ImageBlock')
        ->and($page->draft_config[2]['children'][0]['type'])->toBe('TestimonialBlock')
        ->and($page->draft_config[3]['children'][0]['props']['backgroundColor'])->toBe('var(--theme-primary)');
});

test('rich text with inline selection color survives save and publish', function () {
    $payload = [
        [
            'id' => 'rich-color',
            'type' => 'RichTextBlock',
            'props' => [
                'html' => '<p>Welcome to <span style="color: #fbbf24">seasonal</span> cooking with <span style="color: var(--theme-primary)">brand</span> accents.</p>',
                'padding' => 20,
                'backgroundColor' => 'transparent',
            ],
            'children' => [],
        ],
    ];

    $this->postJson("http://{$this->tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $this->page->id,
        'draft_config' => $payload,
    ])->assertOk()->assertJson(['status' => 'success']);

    $this->postJson("http://{$this->tenant->subdomain}.domain.localhost/editor/publish", [
        'page_id' => $this->page->id,
    ])->assertOk();

    $html = $this->page->refresh()->published_config[0]['props']['html'];

    expect($html)->toContain('color: #fbbf24')
        ->and($html)->toContain('color: var(--theme-primary)');
});

test('menu, button link, and video thumbnail props save and publish', function () {
    $payload = [
        [
            'id' => 'menu-inline',
            'type' => 'MenuBlock',
            'props' => [
                'heading' => 'À la carte',
                'subheading' => 'Served daily',
                'columns' => 2,
                'items' => [
                    ['category' => 'Starters', 'name' => 'Garden plate', 'description' => 'Market vegetables', 'price' => '$14'],
                    ['category' => 'Mains', 'name' => 'Fire-roasted fish', 'description' => 'Charred greens', 'price' => '$32'],
                ],
                'padding' => 40,
                'backgroundColor' => 'transparent',
            ],
            'children' => [],
        ],
        [
            'id' => 'button-inline',
            'type' => 'ButtonBlock',
            'props' => [
                'label' => 'Reserve',
                'variant' => 'primary',
                'url' => '/reservations',
                'openInNewTab' => true,
                'size' => 'lg',
                'alignment' => 'center',
                'backgroundColor' => '#fbbf24',
                'textColor' => '--theme-text',
                'hoverBackgroundColor' => '#f59e0b',
                'hoverTextColor' => '#ffffff',
                'borderRadius' => '9999px',
            ],
            'children' => [],
        ],
        [
            'id' => 'button-ghost',
            'type' => 'ButtonBlock',
            'props' => [
                'label' => 'Learn more',
                'variant' => 'primary',
                'url' => '/about',
                'openInNewTab' => false,
                'size' => 'md',
                'alignment' => 'center',
                'backgroundColor' => 'transparent',
                'textColor' => '',
                'hoverBackgroundColor' => '',
                'hoverTextColor' => '',
                'borderRadius' => '',
            ],
            'children' => [],
        ],
        [
            'id' => 'video-inline',
            'type' => 'VideoEmbedBlock',
            'props' => [
                'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'provider' => 'youtube',
                'aspectRatio' => '16/9',
                'posterUrl' => '',
                'padding' => 20,
                'backgroundColor' => 'transparent',
            ],
            'children' => [],
        ],
    ];

    $this->postJson("http://{$this->tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $this->page->id,
        'draft_config' => $payload,
    ])->assertOk()->assertJson(['status' => 'success']);

    $this->postJson("http://{$this->tenant->subdomain}.domain.localhost/editor/publish", [
        'page_id' => $this->page->id,
    ])->assertOk();

    $page = $this->page->refresh();

    expect($page->published_config[0]['props']['items'][0]['name'])->toBe('Garden plate')
        ->and($page->published_config[0]['props']['columns'])->toBe(2)
        ->and($page->published_config[1]['props']['openInNewTab'])->toBeTrue()
        ->and($page->published_config[1]['props']['backgroundColor'])->toBe('#fbbf24')
        ->and($page->published_config[1]['props']['hoverBackgroundColor'])->toBe('#f59e0b')
        ->and($page->published_config[1]['props']['textColor'])->toBe('--theme-text')
        ->and($page->published_config[1]['props']['borderRadius'])->toBe('9999px')
        ->and($page->published_config[2]['props']['backgroundColor'])->toBe('transparent')
        ->and($page->published_config[2]['props']['textColor'])->toBeNull()
        ->and($page->published_config[3]['props']['provider'])->toBe('youtube');
});

test('inline edits to nested pricing and contact fields survive save and publish', function () {
    $payload = [
        [
            'id' => 'pricing-inline',
            'type' => 'PricingTableBlock',
            'props' => [
                'plans' => [[
                    'title' => 'Growth',
                    'price' => '$49',
                    'period' => '/month',
                    'features' => 'Ten projects, Priority support',
                    'ctaText' => 'Choose Growth',
                    'ctaUrl' => '#growth',
                    'isPopular' => 'yes',
                ]],
                'padding' => 40,
                'backgroundColor' => 'transparent',
            ],
            'children' => [],
        ],
        [
            'id' => 'contact-inline',
            'type' => 'ContactFormBlock',
            'props' => [
                'fields' => [[
                    'type' => 'email',
                    'label' => 'Work email',
                    'placeholder' => 'name@company.com',
                    'required' => 'yes',
                ]],
                'submitLabel' => 'Request a call',
                'successMessage' => 'We will be in touch.',
                'padding' => 40,
                'backgroundColor' => 'transparent',
            ],
            'children' => [],
        ],
    ];

    $this->postJson("http://{$this->tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $this->page->id,
        'draft_config' => $payload,
    ])->assertOk();

    $this->postJson("http://{$this->tenant->subdomain}.domain.localhost/editor/publish", [
        'page_id' => $this->page->id,
    ])->assertOk();

    $page = $this->page->refresh();

    expect($page->draft_config)->toBe($payload)
        ->and($page->published_config)->toBe($payload)
        ->and($page->published_config[0]['props']['plans'][0]['ctaText'])->toBe('Choose Growth')
        ->and($page->published_config[1]['props']['fields'][0]['label'])->toBe('Work email')
        ->and($page->published_config[1]['props']['submitLabel'])->toBe('Request a call');
});
