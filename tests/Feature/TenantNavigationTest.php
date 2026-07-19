<?php

use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('editor receives the saved navigation configuration', function () {
    $user = User::factory()->create();
    $navigationConfig = [
        'header' => [
            'showLogo' => true,
            'items' => [
                ['label' => 'Home', 'slug' => 'home', 'type' => 'internal'],
            ],
            'ctaButton' => ['show' => false, 'label' => 'Contact', 'slug' => 'home'],
        ],
        'footer' => [
            'copyright' => '© 2026 My Brand',
        ],
    ];
    $tenant = Tenant::factory()->create([
        'user_id' => $user->id,
        'navigation_config' => $navigationConfig,
    ]);
    $tenant->pages()->create([
        'slug' => 'home',
        'title' => 'Home',
        'is_homepage' => true,
        'draft_config' => [],
    ]);

    $this->actingAs($user)
        ->get("http://{$tenant->subdomain}.domain.localhost/editor")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tenant/Editor')
            ->where('tenant.navigation_config', $navigationConfig)
        );
});

test('public page receives the saved navigation configuration', function () {
    $navigationConfig = [
        'header' => [
            'showLogo' => true,
            'items' => [
                ['label' => 'Home', 'slug' => 'home', 'type' => 'internal'],
                ['label' => 'External', 'href' => 'https://example.com', 'type' => 'external'],
            ],
            'ctaButton' => ['show' => true, 'label' => 'Contact', 'slug' => 'home'],
        ],
        'footer' => [
            'copyright' => '© 2026 My Brand',
        ],
    ];
    $tenant = Tenant::factory()->create([
        'navigation_config' => $navigationConfig,
    ]);
    $tenant->pages()->create([
        'slug' => 'home',
        'title' => 'Home',
        'is_homepage' => true,
        'draft_config' => [],
        'published_config' => [
            [
                'id' => 'hero-1',
                'type' => 'HeroBlock',
                'props' => [
                    'padding' => 40,
                    'backgroundColor' => 'transparent',
                    'headline' => 'Public Site',
                    'subheadline' => 'Published content.',
                ],
                'children' => [],
            ],
        ],
    ]);

    $this->get("http://{$tenant->subdomain}.domain.localhost/")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tenant/PublicPage')
            ->where('tenant.navigation_config', $navigationConfig)
        );
});

test('authenticated tenant owner can save navigation configuration', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    $navConfig = [
        'header' => [
            'variant' => 'floating-island',
            'menu' => [
                'mode' => 'mega',
                'triggerLabel' => 'Collections',
                'openOn' => 'hover',
                'alignment' => 'center',
                'width' => 'wide',
                'columns' => 3,
                'animation' => 'scale',
                'groups' => [
                    ['id' => 'products', 'label' => 'Products', 'description' => 'Browse products'],
                ],
                'featured' => [
                    'show' => true,
                    'title' => 'Summer collection',
                    'description' => 'View the newest pieces.',
                    'imageUrl' => '/storage/media/featured.webp',
                    'href' => 'https://example.com/summer',
                ],
            ],
            'surface' => [
                'mode' => 'custom',
                'backgroundColor' => '#18181b',
                'textColor' => '#ffffff',
            ],
            'actionPosition' => 'start',
            'brand' => [
                'type' => 'image',
                'text' => 'Acme',
                'imageUrl' => '/storage/media/logo.svg',
                'alt' => 'Acme logo',
                'width' => 132,
                'mobileWidth' => 92,
                'hideOnMobile' => false,
            ],
            'layout' => [
                'contentWidth' => 1280,
                'height' => 76,
                'horizontalPadding' => 28,
                'position' => 'sticky',
                'stickyOffset' => 8,
                'fullWidth' => false,
                'borderWidth' => 1,
                'borderColor' => '#e4e4e7',
                'borderRadius' => 18,
                'shadow' => 'medium',
            ],
            'responsive' => [
                'breakpoint' => 800,
                'menuStyle' => 'drawer',
                'menuIcon' => 'grid',
                'alignment' => 'center',
                'showActions' => true,
            ],
            'states' => [
                'activeColor' => '#2563eb',
                'focusColor' => '#0f172a',
                'disabledOpacity' => 40,
                'scrolledBackgroundColor' => '#ffffff',
                'scrolledTextColor' => '#18181b',
            ],
            'actionStyle' => [
                'mode' => 'custom',
                'backgroundColor' => '#0f172a',
                'textColor' => '#f8fafc',
                'hoverBackgroundColor' => '#334155',
                'hoverTextColor' => '#ffffff',
            ],
            'linkStyle' => [
                'mode' => 'custom',
                'color' => '#475569',
                'hoverColor' => '#0f172a',
                'hoverEffect' => 'underline',
            ],
            'actions' => [
                [
                    'label' => 'Contact',
                    'id' => 'contact-action',
                    'type' => 'internal',
                    'slug' => 'contact',
                    'variant' => 'primary',
                    'icon' => 'arrow',
                    'iconPosition' => 'end',
                    'size' => 'large',
                    'animation' => 'arrow',
                    'backgroundColor' => '#0f172a',
                    'textColor' => '#ffffff',
                    'hoverBackgroundColor' => '#334155',
                    'hoverTextColor' => '#ffffff',
                    'borderColor' => '#0f172a',
                    'borderRadius' => 12,
                ],
                [
                    'label' => 'Docs',
                    'type' => 'external',
                    'href' => 'https://example.com/docs',
                    'variant' => 'outline',
                ],
            ],
            'showLogo' => true,
            'items' => [
                [
                    'id' => 'home-link',
                    'label' => 'Home',
                    'slug' => 'home',
                    'type' => 'internal',
                    'megaGroup' => 'products',
                    'children' => [
                        ['id' => 'home-child', 'label' => 'Overview', 'type' => 'internal', 'slug' => 'home'],
                    ],
                ],
                ['label' => 'External', 'href' => 'https://example.com', 'type' => 'external'],
            ],
            'ctaButton' => ['label' => 'Contact', 'slug' => 'contact'],
        ],
        'footer' => [
            'copyright' => '© 2026 My Brand',
        ],
    ];

    $response = $this->patchJson("http://{$tenant->subdomain}.domain.localhost/editor/navigation", [
        'navigation_config' => $navConfig,
    ]);

    $response->assertOk();
    $response->assertJson(['status' => 'success']);

    $tenant->refresh();
    expect($tenant->navigation_config)->toEqual($navConfig);
});

test('tenant owner can save a structured footer with reorderable modules', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['user_id' => $user->id]);
    $footer = [
        'variant' => 'editorial',
        'moduleOrder' => ['newsletter', 'brand', 'links', 'social', 'copyright'],
        'backgroundMode' => 'custom',
        'backgroundColor' => '#18181b',
        'textColor' => '#ffffff',
        'contentWidth' => 1280,
        'showLinks' => true,
        'showSocial' => true,
        'showCopyright' => true,
        'brand' => [
            'show' => true,
            'title' => 'Acme',
            'description' => 'Objects made with care.',
            'imageUrl' => '/storage/media/logo.svg',
            'alt' => 'Acme logo',
        ],
        'linkGroups' => [[
            'id' => 'company',
            'label' => 'Company',
            'links' => [[
                'id' => 'about-link',
                'label' => 'About',
                'type' => 'internal',
                'slug' => 'about',
            ]],
        ]],
        'newsletter' => [
            'show' => true,
            'eyebrow' => 'Stay connected',
            'heading' => 'Hear what is next',
            'body' => 'Follow new releases and stories.',
            'buttonLabel' => 'Contact us',
            'buttonUrl' => '/contact',
        ],
        'socialLinks' => [[
            'id' => 'instagram',
            'label' => 'Instagram',
            'href' => 'https://instagram.com/acme',
        ]],
        'copyright' => '© 2026 Acme',
    ];

    $this->actingAs($user)
        ->patchJson("http://{$tenant->subdomain}.domain.localhost/editor/navigation", [
            'navigation_config' => [
                'header' => ['items' => []],
                'footer' => $footer,
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('navigation_config.footer.variant', 'editorial')
        ->assertJsonPath('navigation_config.footer.moduleOrder.0', 'newsletter');

    $savedFooter = $tenant->refresh()->navigation_config['footer'];

    expect($savedFooter['moduleOrder'])->toBe($footer['moduleOrder'])
        ->and($savedFooter)->toEqualCanonicalizing($footer);
});

test('invalid footer variants and duplicate modules are rejected', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->patchJson("http://{$tenant->subdomain}.domain.localhost/editor/navigation", [
            'navigation_config' => [
                'header' => ['items' => []],
                'footer' => [
                    'variant' => 'floating-circles',
                    'moduleOrder' => ['brand', 'brand'],
                ],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'navigation_config.footer.variant',
            'navigation_config.footer.moduleOrder.1',
        ]);
});

test('tenant owner can select every registered navbar variant', function (string $variant) {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['user_id' => $user->id]);

    $navigationConfig = [
        'header' => [
            'variant' => $variant,
            'showLogo' => true,
            'items' => [],
            'ctaButton' => ['show' => false],
        ],
        'footer' => ['copyright' => ''],
    ];

    $this->actingAs($user)
        ->patchJson("http://{$tenant->subdomain}.domain.localhost/editor/navigation", [
            'navigation_config' => $navigationConfig,
        ])
        ->assertOk()
        ->assertJsonPath('navigation_config.header.variant', $variant);

    expect($tenant->refresh()->navigation_config['header']['variant'])->toBe($variant);
})->with([
    'classic inline' => 'classic-inline',
    'floating island' => 'floating-island',
    'centered brand' => 'centered-brand',
]);

test('legacy mega menu variants remain valid while menu behavior is migrated', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['user_id' => $user->id]);

    $navigationConfig = [
        'header' => [
            'variant' => 'mega-menu',
            'items' => [],
        ],
        'footer' => ['copyright' => ''],
    ];

    $this->actingAs($user)
        ->patchJson("http://{$tenant->subdomain}.domain.localhost/editor/navigation", [
            'navigation_config' => $navigationConfig,
        ])
        ->assertOk();

    expect($tenant->refresh()->navigation_config['header']['variant'])->toBe('mega-menu');
});

test('tenant owner can select every registered navbar surface mode', function (string $mode) {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['user_id' => $user->id]);

    $navigationConfig = [
        'header' => [
            'variant' => 'floating-island',
            'surface' => [
                'mode' => $mode,
                'backgroundColor' => '#18181b',
                'textColor' => '#ffffff',
            ],
            'items' => [],
        ],
        'footer' => ['copyright' => ''],
    ];

    $this->actingAs($user)
        ->patchJson("http://{$tenant->subdomain}.domain.localhost/editor/navigation", [
            'navigation_config' => $navigationConfig,
        ])
        ->assertOk()
        ->assertJsonPath('navigation_config.header.surface.mode', $mode);

    expect($tenant->refresh()->navigation_config['header']['surface']['mode'])->toBe($mode);
})->with([
    'design default' => 'design',
    'transparent' => 'transparent',
    'site theme' => 'theme',
    'custom color' => 'custom',
]);

test('unknown navbar variants are rejected', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->patchJson("http://{$tenant->subdomain}.domain.localhost/editor/navigation", [
            'navigation_config' => [
                'header' => [
                    'variant' => 'unknown-navbar',
                    'items' => [],
                ],
                'footer' => [],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['navigation_config.header.variant']);

    expect($tenant->refresh()->navigation_config)->toBeNull();
});

test('unknown navbar surface modes are rejected', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->patchJson("http://{$tenant->subdomain}.domain.localhost/editor/navigation", [
            'navigation_config' => [
                'header' => [
                    'surface' => [
                        'mode' => 'gradient-magic',
                    ],
                ],
                'footer' => ['copyright' => ''],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['navigation_config.header.surface.mode']);

    expect($tenant->refresh()->navigation_config)->toBeNull();
});

test('custom navbar surfaces require valid background and text colors', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->patchJson("http://{$tenant->subdomain}.domain.localhost/editor/navigation", [
            'navigation_config' => [
                'header' => [
                    'surface' => [
                        'mode' => 'custom',
                        'backgroundColor' => 'black',
                        'textColor' => '#fff',
                    ],
                ],
                'footer' => ['copyright' => ''],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'navigation_config.header.surface.backgroundColor',
            'navigation_config.header.surface.textColor',
        ]);

    expect($tenant->refresh()->navigation_config)->toBeNull();
});

test('custom navbar buttons and links require valid normal and hover colors', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->patchJson("http://{$tenant->subdomain}.domain.localhost/editor/navigation", [
            'navigation_config' => [
                'header' => [
                    'actionStyle' => [
                        'mode' => 'custom',
                        'backgroundColor' => '#18181b',
                        'textColor' => '#ffffff',
                        'hoverBackgroundColor' => 'gray',
                        'hoverTextColor' => '#fff',
                    ],
                    'linkStyle' => [
                        'mode' => 'custom',
                        'color' => '#334155',
                        'hoverColor' => 'navy',
                    ],
                ],
                'footer' => ['copyright' => ''],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'navigation_config.header.actionStyle.hoverBackgroundColor',
            'navigation_config.header.actionStyle.hoverTextColor',
            'navigation_config.header.linkStyle.hoverColor',
        ]);

    expect($tenant->refresh()->navigation_config)->toBeNull();
});

test('unknown menu behavior and action positions are rejected', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->patchJson("http://{$tenant->subdomain}.domain.localhost/editor/navigation", [
            'navigation_config' => [
                'header' => [
                    'menu' => ['mode' => 'drawer'],
                    'actionPosition' => 'middle',
                ],
                'footer' => ['copyright' => ''],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'navigation_config.header.menu.mode',
            'navigation_config.header.actionPosition',
        ]);
});

test('navbar actions are limited to three buttons', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['user_id' => $user->id]);
    $actions = collect(range(1, 4))->map(fn (int $number): array => [
        'label' => "Action {$number}",
        'type' => 'internal',
        'slug' => 'home',
        'variant' => 'primary',
    ])->all();

    $this->actingAs($user)
        ->patchJson("http://{$tenant->subdomain}.domain.localhost/editor/navigation", [
            'navigation_config' => [
                'header' => ['actions' => $actions],
                'footer' => ['copyright' => ''],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['navigation_config.header.actions']);
});

test('invalid advanced navbar controls are rejected', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->patchJson("http://{$tenant->subdomain}.domain.localhost/editor/navigation", [
            'navigation_config' => [
                'header' => [
                    'menu' => ['columns' => 8, 'openOn' => 'always'],
                    'layout' => ['height' => 20],
                    'responsive' => ['breakpoint' => 200, 'menuStyle' => 'popover'],
                    'linkStyle' => ['hoverEffect' => 'spin'],
                    'actions' => [[
                        'label' => 'Invalid',
                        'type' => 'internal',
                        'slug' => 'home',
                        'variant' => 'primary',
                        'animation' => 'bounce-forever',
                    ]],
                ],
                'footer' => ['copyright' => ''],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'navigation_config.header.menu.columns',
            'navigation_config.header.menu.openOn',
            'navigation_config.header.layout.height',
            'navigation_config.header.responsive.breakpoint',
            'navigation_config.header.responsive.menuStyle',
            'navigation_config.header.linkStyle.hoverEffect',
            'navigation_config.header.actions.0.animation',
        ]);
});

test('tenant owner can enable header site search with a custom placeholder', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->patchJson("http://{$tenant->subdomain}.domain.localhost/editor/navigation", [
            'navigation_config' => [
                'header' => [
                    'items' => [],
                    'search' => ['show' => true, 'placeholder' => 'Find a page…'],
                ],
                'footer' => ['copyright' => ''],
            ],
        ])
        ->assertOk()
        ->assertJsonPath('navigation_config.header.search.show', true)
        ->assertJsonPath('navigation_config.header.search.placeholder', 'Find a page…');

    expect($tenant->refresh()->navigation_config['header']['search']['show'])->toBeTrue();
});

test('overlong search placeholders are rejected', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->patchJson("http://{$tenant->subdomain}.domain.localhost/editor/navigation", [
            'navigation_config' => [
                'header' => [
                    'items' => [],
                    'search' => ['show' => true, 'placeholder' => str_repeat('a', 61)],
                ],
                'footer' => ['copyright' => ''],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['navigation_config.header.search.placeholder']);
});

test('public page receives published page titles and slugs when search is enabled', function () {
    $tenant = Tenant::factory()->create([
        'navigation_config' => [
            'header' => ['search' => ['show' => true]],
            'footer' => [],
        ],
    ]);
    $publishedBlock = [[
        'id' => 'hero-1',
        'type' => 'HeroBlock',
        'props' => ['padding' => 40, 'backgroundColor' => 'transparent', 'headline' => 'Hi', 'subheadline' => ''],
        'children' => [],
    ]];
    $tenant->pages()->create([
        'slug' => 'home',
        'title' => 'Home',
        'is_homepage' => true,
        'draft_config' => [],
        'published_config' => $publishedBlock,
    ]);
    $tenant->pages()->create([
        'slug' => 'menu',
        'title' => 'Menu',
        'is_homepage' => false,
        'draft_config' => [],
        'published_config' => $publishedBlock,
    ]);
    $tenant->pages()->create([
        'slug' => 'draft-only',
        'title' => 'Draft Only',
        'is_homepage' => false,
        'draft_config' => [],
    ]);

    $this->get("http://{$tenant->subdomain}.domain.localhost/")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tenant/PublicPage')
            ->has('pages', 2)
            ->where('pages.0.slug', 'home')
            ->where('pages.1.title', 'Menu')
        );
});

test('public page omits the searchable page list when search is disabled', function () {
    $tenant = Tenant::factory()->create([
        'navigation_config' => ['header' => [], 'footer' => []],
    ]);
    $tenant->pages()->create([
        'slug' => 'home',
        'title' => 'Home',
        'is_homepage' => true,
        'draft_config' => [],
        'published_config' => [[
            'id' => 'hero-1',
            'type' => 'HeroBlock',
            'props' => ['padding' => 40, 'backgroundColor' => 'transparent', 'headline' => 'Hi', 'subheadline' => ''],
            'children' => [],
        ]],
    ]);

    $this->get("http://{$tenant->subdomain}.domain.localhost/")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tenant/PublicPage')
            ->has('pages', 0)
        );
});

test('non-owner cannot save navigation configuration', function () {
    $userA = User::factory()->create();
    $tenantA = Tenant::factory()->create(['user_id' => $userA->id]);

    $userB = User::factory()->create();

    $this->actingAs($userB);

    $response = $this->patchJson("http://{$tenantA->subdomain}.domain.localhost/editor/navigation", [
        'navigation_config' => [
            'header' => [],
            'footer' => [],
        ],
    ]);

    $response->assertStatus(403);
});

test('unauthenticated users cannot save navigation configuration', function () {
    $tenant = Tenant::factory()->create();

    $response = $this->patchJson("http://{$tenant->subdomain}.domain.localhost/editor/navigation", [
        'navigation_config' => [
            'header' => [],
            'footer' => [],
        ],
    ]);

    $response->assertStatus(401);
});
