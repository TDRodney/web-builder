<?php

use App\Models\Tenant;
use App\Models\User;

test('atomic text exposes simple font size controls with advanced values preserved', function () {
    $definition = config('blocks.definitions.AtomicText');
    $fontSizeField = collect($definition['inspectorFields'])->firstWhere('key', 'fontSize');

    expect($fontSizeField['label'])->toBe('Font Size')
        ->and($fontSizeField['type'])->toBe('font-size')
        ->and($definition['defaultProps']['fontSize'])->toBe('16px');
});

test('authenticated tenant owners can save a valid block schema configuration', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);
    $page = $tenant->pages()->first();

    $this->actingAs($user);

    $validData = [
        [
            'id' => 'hero-123',
            'type' => 'HeroBlock',
            'props' => [
                'padding' => 50,
                'backgroundColor' => '#ffffff',
                'headline' => 'Test Headline',
                'subheadline' => 'Test Subheadline',
            ],
            'children' => [],
        ],
    ];

    $response = $this->postJson("http://{$tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $page->id,
        'draft_config' => $validData,
    ]);

    $response->assertOk();
    $response->assertJson(['status' => 'success']);

    $page->refresh();
    expect($page->draft_config)->toBe($validData);
});

test('saving blocks with missing id is rejected', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);
    $page = $tenant->pages()->first();

    $this->actingAs($user);

    $invalidData = [
        [
            'type' => 'HeroBlock',
            'props' => ['padding' => 50],
        ],
    ];

    $response = $this->postJson("http://{$tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $page->id,
        'draft_config' => $invalidData,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('draft_config');
});

test('saving blocks with unrecognized block types is rejected', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);
    $page = $tenant->pages()->first();

    $this->actingAs($user);

    $invalidData = [
        [
            'id' => 'malicious-block-1',
            'type' => 'SQLInjectionBlock',
            'props' => [],
        ],
    ];

    $response = $this->postJson("http://{$tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $page->id,
        'draft_config' => $invalidData,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('draft_config');
});

test('saving blocks with missing props key is rejected', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);
    $page = $tenant->pages()->first();

    $this->actingAs($user);

    $invalidData = [
        [
            'id' => 'hero-1',
            'type' => 'HeroBlock',
        ],
    ];

    $response = $this->postJson("http://{$tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $page->id,
        'draft_config' => $invalidData,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('draft_config');
});

test('saving blocks with malformed children array is rejected', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);
    $page = $tenant->pages()->first();

    $this->actingAs($user);

    $invalidData = [
        [
            'id' => 'grid-1',
            'type' => 'LayoutGrid',
            'props' => [],
            'children' => 'not-an-array',
        ],
    ];

    $response = $this->postJson("http://{$tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $page->id,
        'draft_config' => $invalidData,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('draft_config');
});

test('saving layouts with unrestricted block types succeeds', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);
    $page = $tenant->pages()->first();

    $this->actingAs($user);

    // LayoutGrid now accepts all block types as direct children
    $validData = [
        [
            'id' => 'grid-1',
            'type' => 'LayoutGrid',
            'props' => ['columns' => 2, 'padding' => 20, 'backgroundColor' => 'transparent', 'gap' => '1rem'],
            'children' => [
                [
                    'id' => 'hero-inside-grid',
                    'type' => 'HeroBlock',
                    'props' => ['padding' => 40, 'backgroundColor' => '#ffffff', 'headline' => 'Inside Grid', 'subheadline' => 'Works'],
                ],
            ],
        ],
    ];

    $response = $this->postJson("http://{$tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $page->id,
        'draft_config' => $validData,
    ]);

    $response->assertOk();
    $response->assertJson(['status' => 'success']);

    $page->refresh();
    expect($page->draft_config)->toBe($validData);
});

test('a composed section with editable hero roles saves successfully', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);
    $page = $tenant->pages()->first();
    $section = [
        'id' => 'section-hero',
        'type' => 'SectionBlock',
        'props' => [
            'patternKey' => 'hero-split-right',
            'padding' => 0,
            'sectionPadding' => 72,
            'backgroundColor' => 'transparent',
            'backgroundImage' => '',
            'contentWidth' => 1180,
            'minHeight' => 560,
            'verticalAlign' => 'center',
            'textAlign' => 'left',
            'overlayOpacity' => 0,
        ],
        'children' => [[
            'id' => 'section-heading',
            'type' => 'AtomicText',
            'props' => [
                'patternRole' => 'heading',
                'content' => 'Composed hero',
                'padding' => 0,
                'backgroundColor' => 'transparent',
            ],
            'children' => [],
        ]],
    ];

    $this->actingAs($user)
        ->postJson("http://{$tenant->subdomain}.domain.localhost/editor/save", [
            'page_id' => $page->id,
            'draft_config' => [$section],
        ])
        ->assertSuccessful();

    $savedSection = $page->refresh()->draft_config[0];

    expect($savedSection['type'])->toBe('SectionBlock')
        ->and($savedSection['props']['patternKey'])->toBe('hero-split-right')
        ->and($savedSection['props']['backgroundImage'])->toBeNull()
        ->and($savedSection['children'][0]['props']['patternRole'])->toBe('heading')
        ->and($savedSection['children'][0]['props']['content'])->toBe('Composed hero');
});
