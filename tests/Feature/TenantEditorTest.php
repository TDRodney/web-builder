<?php

use App\Models\Page;
use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests visiting editor are redirected to central login', function () {
    $user = User::factory()->create();
    $tenant = Tenant::create([
        'user_id' => $user->id,
        'subdomain' => 'test-tenant',
    ]);

    // Send request to tenant domain editor
    $response = $this->get('http://test-tenant.domain.localhost/editor');

    // Default Laravel auth middleware should redirect to route('login')
    // route('login') resolves to http://domain.localhost/login (since central_domain is domain.localhost)
    $response->assertRedirect('http://domain.localhost/login');
});

test('authorized tenant owners can access their editor canvas', function () {
    $user = User::factory()->create();
    $tenant = Tenant::create([
        'user_id' => $user->id,
        'subdomain' => 'test-tenant',
        'site_setup_completed_at' => now(),
    ]);

    $this->actingAs($user);

    $response = $this->get('http://test-tenant.domain.localhost/editor');

    $response->assertOk()
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->component('Tenant/Editor')
            ->where('urls.live', 'http://test-tenant.domain.localhost')
        );
});

test('editor receives the live URL for the page being edited', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create([
        'user_id' => $user->id,
        'subdomain' => 'test-tenant',
    ]);
    $page = Page::factory()->create([
        'tenant_id' => $tenant->id,
        'title' => 'About',
        'slug' => 'about',
        'is_homepage' => false,
    ]);

    $this->actingAs($user)
        ->get('http://test-tenant.domain.localhost/editor?page=about')
        ->assertOk()
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->component('Tenant/Editor')
            ->where('page.id', $page->id)
            ->where('urls.live', 'http://test-tenant.domain.localhost/about')
        );
});

test('tenant owners cannot access another tenant editor canvas', function () {
    $user1 = User::factory()->create();
    $tenant1 = Tenant::create([
        'user_id' => $user1->id,
        'subdomain' => 'tenant-one',
    ]);

    $user2 = User::factory()->create();
    $tenant2 = Tenant::create([
        'user_id' => $user2->id,
        'subdomain' => 'tenant-two',
    ]);

    $this->actingAs($user1);

    // Try to visit tenant2 editor
    $response = $this->get('http://tenant-two.domain.localhost/editor');

    $response->assertForbidden();
});

test('tenant owners can save page draft configuration', function () {
    $user = User::factory()->create();
    $tenant = Tenant::create([
        'user_id' => $user->id,
        'subdomain' => 'test-tenant',
    ]);

    $page = $tenant->pages()->create([
        'slug' => 'home',
        'draft_config' => [],
        'published_config' => [],
    ]);

    $this->actingAs($user);

    $draftData = [
        ['id' => 'hero-1', 'type' => 'HeroBlock', 'props' => ['padding' => 50, 'headline' => 'Updated']],
    ];

    // Post save request to tenant editor save
    $response = $this->postJson('http://test-tenant.domain.localhost/editor/save', [
        'page_id' => $page->id,
        'draft_config' => $draftData,
    ]);

    $response->assertOk();
    $response->assertJson(['status' => 'success']);

    // Check database
    $page->refresh();
    expect($page->draft_config)->toBe($draftData);
});

test('users cannot save page draft configuration of another tenant', function () {
    $user1 = User::factory()->create();
    $tenant1 = Tenant::create([
        'user_id' => $user1->id,
        'subdomain' => 'tenant-one',
    ]);
    $page1 = $tenant1->pages()->create([
        'slug' => 'home',
        'draft_config' => [],
    ]);

    $user2 = User::factory()->create();
    $tenant2 = Tenant::create([
        'user_id' => $user2->id,
        'subdomain' => 'tenant-two',
    ]);

    $this->actingAs($user2);

    // Post to tenant1 domain to save page belonging to tenant1 (using tenant2 auth context)
    // The IdentifyTenant middleware resolves tenant1 subdomain and binds tenant1 as currentTenant.
    // The controller checks auth()->id() !== tenant1->user_id (which is true because user2 is logged in),
    // and returns a 403 Forbidden.
    $response = $this->postJson('http://tenant-one.domain.localhost/editor/save', [
        'page_id' => $page1->id,
        'draft_config' => [],
    ]);

    $response->assertForbidden();
});

test('tenant owners can publish page draft configuration', function () {
    $user = User::factory()->create();
    $tenant = Tenant::create([
        'user_id' => $user->id,
        'subdomain' => 'test-tenant',
    ]);

    $draftData = [
        ['id' => 'hero-1', 'type' => 'HeroBlock', 'props' => ['padding' => 60, 'headline' => 'Published State']],
    ];

    $page = $tenant->pages()->create([
        'slug' => 'home',
        'draft_config' => $draftData,
        'published_config' => [],
    ]);

    $this->actingAs($user);

    $response = $this->postJson('http://test-tenant.domain.localhost/editor/publish', [
        'page_id' => $page->id,
    ]);

    $response->assertOk();
    $response->assertJson(['status' => 'success']);

    $page->refresh();
    expect($page->published_config)->toBe($draftData);
});

test('public pages are accessible and render published configuration', function () {
    $user = User::factory()->create();
    $tenant = Tenant::create([
        'user_id' => $user->id,
        'subdomain' => 'test-tenant',
    ]);

    $publishedData = [
        ['id' => 'hero-1', 'type' => 'HeroBlock', 'props' => ['padding' => 40, 'backgroundColor' => '#abcdef', 'headline' => 'Public Site View', 'subheadline' => 'Built with our engine.']],
    ];

    $tenant->pages()->create([
        'slug' => 'home',
        'draft_config' => [],
        'published_config' => $publishedData,
    ]);

    // Request public root page
    $response = $this->get('http://test-tenant.domain.localhost/');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Tenant/PublicPage')
        ->where('page.published_config.0.props.headline', 'Public Site View')
    );
});

test('sub-pages are accessible via public wildcard slug routing', function () {
    $user = User::factory()->create();
    $tenant = Tenant::create([
        'user_id' => $user->id,
        'subdomain' => 'test-tenant',
    ]);

    $publishedData = [
        ['id' => 'hero-1', 'type' => 'HeroBlock', 'props' => ['padding' => 40, 'backgroundColor' => '#ffffff', 'headline' => 'About Us page content', 'subheadline' => 'Built with our engine.']],
    ];

    $tenant->pages()->create([
        'slug' => 'about',
        'draft_config' => [],
        'published_config' => $publishedData,
    ]);

    // Request public sub-page
    $response = $this->get('http://test-tenant.domain.localhost/about');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Tenant/PublicPage')
        ->where('page.published_config.0.props.headline', 'About Us page content')
    );
});

test('tenant routes block invalid subdomain formats', function () {
    $user = User::factory()->create();
    $tenant = Tenant::create([
        'user_id' => $user->id,
        'subdomain' => 'invalid_subdomain',
    ]);

    // Send request to invalid subdomain
    $response = $this->get('http://invalid_subdomain.domain.localhost/');

    // The route domain pattern constraint ->where(['tenant' => '^[a-z0-9-]+$']) should prevent it from routing, resulting in 404
    $response->assertNotFound();
});

test('tenant owners can save and render recursive ast configuration', function () {
    $user = User::factory()->create();
    $tenant = Tenant::create([
        'user_id' => $user->id,
        'subdomain' => 'test-tenant',
    ]);

    $page = $tenant->pages()->create([
        'slug' => 'home',
        'draft_config' => [],
        'published_config' => [],
    ]);

    $this->actingAs($user);

    $recursiveData = [
        [
            'id' => 'grid-1',
            'type' => 'LayoutGrid',
            'props' => ['columns' => 3, 'gap' => '1rem', 'padding' => '1rem'],
            'children' => [
                [
                    'id' => 'column-1',
                    'type' => 'LayoutColumn',
                    'props' => ['span' => 2],
                    'children' => [
                        [
                            'id' => 'text-1',
                            'type' => 'AtomicText',
                            'props' => ['content' => 'Deeply nested text content', 'fontSize' => '18px', 'color' => '#ff0000'],
                        ],
                    ],
                ],
            ],
        ],
    ];

    // Post save request to tenant editor save
    $response = $this->postJson('http://test-tenant.domain.localhost/editor/save', [
        'page_id' => $page->id,
        'draft_config' => $recursiveData,
    ]);

    $response->assertOk();
    $response->assertJson(['status' => 'success']);

    // Check database
    $page->refresh();
    expect($page->draft_config)->toBe($recursiveData);
});
