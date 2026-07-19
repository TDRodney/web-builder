<?php

use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard', ['tenant' => 'test-tenant']));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $themeConfig = [
        'colors' => [
            'primary' => '#4f46e5',
            'secondary' => '#0ea5e9',
            'background' => '#ffffff',
            'text' => '#0f172a',
        ],
        'typography' => [
            'headingFont' => 'Instrument Sans',
            'bodyFont' => 'Inter',
        ],
        'borderRadius' => '8px',
    ];
    $navigationConfig = [
        'header' => [
            'variant' => 'centered-brand',
            'showLogo' => true,
            'items' => [],
            'ctaButton' => ['show' => false],
        ],
        'footer' => ['copyright' => ''],
    ];
    $tenant = Tenant::create([
        'user_id' => $user->id,
        'subdomain' => 'test-tenant',
        'theme_config' => $themeConfig,
        'navigation_config' => $navigationConfig,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard', ['tenant' => 'test-tenant']));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('CentralDashboard')
            ->where('tenant.id', $tenant->id)
            ->where('tenant.subdomain', 'test-tenant')
            ->where('theme_config', $themeConfig)
            ->where('navigation_config', $navigationConfig)
            ->where('can_apply_site_kit', false)
            ->where('site_summary.page_count', 0)
            ->where('site_summary.published_page_count', 0)
            ->where('site_summary.last_edited_at', null)
            ->where('pages', [])
            ->where('central_navigation.account_settings_url', route('profile.edit'))
            ->where('central_navigation.logout_url', route('logout'))
            ->where('central_navigation.csrf_token', fn (mixed $token): bool => is_string($token) && strlen($token) === 40)
        );
});

test('authenticated users cannot visit another tenant dashboard', function () {
    $owner = User::factory()->create();
    $tenant = Tenant::factory()->create([
        'user_id' => $owner->id,
        'subdomain' => 'protected-tenant',
    ]);
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->get(route('dashboard', ['tenant' => $tenant->subdomain]))
        ->assertForbidden();
});

test('dashboard reports page and publish status from tenant data', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create([
        'user_id' => $user->id,
        'subdomain' => 'status-summary',
    ]);
    $tenant->pages()->createMany([
        [
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'draft_config' => [],
            'published_config' => [],
        ],
        [
            'title' => 'About',
            'slug' => 'about',
            'draft_config' => [],
            'published_config' => null,
        ],
    ]);

    $this->actingAs($user)
        ->get(route('dashboard', ['tenant' => $tenant->subdomain]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('CentralDashboard')
            ->where('site_summary.page_count', 2)
            ->where('site_summary.published_page_count', 1)
            ->where(
                'site_summary.last_edited_at',
                fn (mixed $lastEditedAt): bool => is_string($lastEditedAt),
            )
            ->count('pages', 2)
            ->where('pages.0.title', 'Home')
            ->where('pages.0.is_homepage', true)
            ->where('pages.0.is_published', true)
            ->where('pages.1.title', 'About')
            ->where('pages.1.is_published', false)
        );
});

test('legacy navbar studio links redirect tenant owners into the unified builder', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create([
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('tenant.navbars.index', ['tenant' => $tenant->subdomain]))
        ->assertRedirect(route('tenant.editor', [
            'tenant' => $tenant->subdomain,
            'workspace' => 'navigation',
        ]));
});

test('users cannot visit another tenant navbar library', function () {
    $owner = User::factory()->create();
    $tenant = Tenant::factory()->create(['user_id' => $owner->id]);
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->get(route('tenant.navbars.index', ['tenant' => $tenant->subdomain]))
        ->assertForbidden();
});
