<?php

use App\Models\Tenant;
use App\Models\User;

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
