<?php

use App\Models\Page;
use App\Models\Tenant;
use App\Models\User;

// ── Public visibility gate ───────────────────────────────────────────────

test('a published and listed page renders on the public site', function () {
    $tenant = Tenant::create([
        'user_id' => User::factory()->create()->id,
        'subdomain' => 'vis-live',
    ]);

    $publishedData = [
        ['id' => 'hero-1', 'type' => 'HeroBlock', 'props' => ['padding' => 40, 'headline' => 'Live content']],
    ];

    $tenant->pages()->create([
        'slug' => 'about',
        'is_published' => true,
        'draft_config' => [],
        'published_config' => $publishedData,
    ]);

    $this->get('http://vis-live.domain.localhost/about')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Tenant/PublicPage')
            ->where('page.published_config.0.props.headline', 'Live content')
        );
});

test('an unlisted page returns a public 404 even with published config intact', function () {
    $tenant = Tenant::create([
        'user_id' => User::factory()->create()->id,
        'subdomain' => 'vis-unlisted',
    ]);

    $publishedData = [
        ['id' => 'hero-1', 'type' => 'HeroBlock', 'props' => ['padding' => 40, 'headline' => 'Should stay hidden']],
    ];

    $page = $tenant->pages()->create([
        'slug' => 'about',
        'is_published' => false,
        'draft_config' => [],
        'published_config' => $publishedData,
    ]);

    $this->get('http://vis-unlisted.domain.localhost/about')->assertNotFound();

    // The published snapshot must survive unlisting.
    expect($page->refresh()->published_config)->toBe($publishedData);
});

test('a listed page with no published config still returns a public 404', function () {
    $tenant = Tenant::create([
        'user_id' => User::factory()->create()->id,
        'subdomain' => 'vis-draftonly',
    ]);

    $tenant->pages()->create([
        'slug' => 'about',
        'is_published' => true,
        'draft_config' => [['id' => 'hero-1', 'type' => 'HeroBlock', 'props' => ['headline' => 'Draft only']]],
        'published_config' => null,
    ]);

    $this->get('http://vis-draftonly.domain.localhost/about')->assertNotFound();
});

// ── is_published default ─────────────────────────────────────────────────

test('new pages default to is_published true', function () {
    $tenant = Tenant::create([
        'user_id' => User::factory()->create()->id,
        'subdomain' => 'vis-default',
    ]);

    $page = $tenant->pages()->create([
        'slug' => 'about',
        'draft_config' => [],
    ]);

    expect($page->refresh()->is_published)->toBeTrue();
});

// ── Visibility toggle endpoint ───────────────────────────────────────────

test('tenant owners can unlist and relist a non-homepage page', function () {
    $user = User::factory()->create();
    $tenant = Tenant::create([
        'user_id' => $user->id,
        'subdomain' => 'vis-toggle',
    ]);
    $publishedData = [
        ['id' => 'hero-1', 'type' => 'HeroBlock', 'props' => ['padding' => 40, 'headline' => 'Live']],
    ];
    $page = $tenant->pages()->create([
        'slug' => 'about',
        'is_homepage' => false,
        'is_published' => true,
        'draft_config' => [],
        'published_config' => $publishedData,
    ]);

    $this->actingAs($user);

    // Unlist: page is hidden, content preserved.
    $this->patchJson("http://vis-toggle.domain.localhost/editor/pages/{$page->id}/visibility", [
        'is_published' => false,
    ])
        ->assertOk()
        ->assertJsonPath('status', 'success');

    expect($page->refresh()->is_published)->toBeFalse()
        ->and($page->published_config)->toBe($publishedData);
    $this->get('http://vis-toggle.domain.localhost/about')->assertNotFound();

    // Relist: page is live again, content unchanged.
    $this->patchJson("http://vis-toggle.domain.localhost/editor/pages/{$page->id}/visibility", [
        'is_published' => true,
    ])->assertOk();

    expect($page->refresh()->is_published)->toBeTrue();
    $this->get('http://vis-toggle.domain.localhost/about')->assertOk();
});

test('the homepage cannot be unlisted', function () {
    $user = User::factory()->create();
    $tenant = Tenant::create([
        'user_id' => $user->id,
        'subdomain' => 'vis-homepage',
    ]);
    $page = $tenant->pages()->create([
        'slug' => 'home',
        'is_homepage' => true,
        'is_published' => true,
        'draft_config' => [],
        'published_config' => [],
    ]);

    $this->actingAs($user)
        ->patchJson("http://vis-homepage.domain.localhost/editor/pages/{$page->id}/visibility", [
            'is_published' => false,
        ])
        ->assertStatus(422);

    expect($page->refresh()->is_published)->toBeTrue();
});

test('non-owners cannot toggle page visibility', function () {
    $owner = User::factory()->create();
    $tenant = Tenant::create([
        'user_id' => $owner->id,
        'subdomain' => 'vis-authz',
    ]);
    $page = $tenant->pages()->create([
        'slug' => 'about',
        'is_published' => true,
        'draft_config' => [],
        'published_config' => [],
    ]);

    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->patchJson("http://vis-authz.domain.localhost/editor/pages/{$page->id}/visibility", [
            'is_published' => false,
        ])
        ->assertForbidden();
});

test('cross-tenant visibility toggle cannot reach another tenant page', function () {
    $ownerA = User::factory()->create();
    $tenantA = Tenant::create([
        'user_id' => $ownerA->id,
        'subdomain' => 'vis-tenant-a',
    ]);
    $pageA = $tenantA->pages()->create([
        'slug' => 'about',
        'is_published' => true,
        'draft_config' => [],
        'published_config' => [],
    ]);

    $ownerB = User::factory()->create();
    $tenantB = Tenant::create([
        'user_id' => $ownerB->id,
        'subdomain' => 'vis-tenant-b',
    ]);

    // ownerB is authenticated, but the request host resolves tenantA (via the
    // IdentifyTenant middleware), so the controller's ownership check rejects it.
    $this->actingAs($ownerB)
        ->patchJson("http://vis-tenant-a.domain.localhost/editor/pages/{$pageA->id}/visibility", [
            'is_published' => false,
        ])
        ->assertForbidden();

    expect($pageA->refresh()->is_published)->toBeTrue();
});
