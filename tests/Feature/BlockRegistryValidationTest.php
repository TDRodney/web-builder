<?php

use App\Models\Tenant;
use App\Models\User;

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

    $response->assertRedirect();
    $response->assertSessionHasErrors(['draft_config']);
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

    $response->assertRedirect();
    $response->assertSessionHasErrors(['draft_config']);
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

    $response->assertRedirect();
    $response->assertSessionHasErrors(['draft_config']);
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

    $response->assertRedirect();
    $response->assertSessionHasErrors(['draft_config']);
});

test('saving blocks violating parent nesting constraints is rejected', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);
    $page = $tenant->pages()->first();

    $this->actingAs($user);

    // LayoutGrid is configured to only allow LayoutColumn. Let's try placing HeroBlock directly.
    $invalidData = [
        [
            'id' => 'grid-1',
            'type' => 'LayoutGrid',
            'props' => [],
            'children' => [
                [
                    'id' => 'hero-inside-grid-error',
                    'type' => 'HeroBlock',
                    'props' => [],
                ],
            ],
        ],
    ];

    $response = $this->postJson("http://{$tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $page->id,
        'draft_config' => $invalidData,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['draft_config']);
});
