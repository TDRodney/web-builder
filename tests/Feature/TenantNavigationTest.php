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
            'showLogo' => true,
            'items' => [
                ['label' => 'Home', 'slug' => 'home', 'type' => 'internal'],
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
    expect($tenant->navigation_config)->toBe($navConfig);
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
