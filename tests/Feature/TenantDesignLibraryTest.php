<?php

use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected from the design library to login', function () {
    $tenant = Tenant::factory()->create(['subdomain' => 'design-guest']);

    $this->get(route('tenant.designs.index', ['tenant' => $tenant->subdomain]))
        ->assertRedirect(route('login'));
});

test('tenant owners can preview the three approved site kits', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $user->id,
        'subdomain' => 'design-owner',
    ]);

    $this->actingAs($user)
        ->get(route('tenant.designs.index', ['tenant' => $tenant->subdomain]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('DesignLibrary')
            ->where('tenant.id', $tenant->id)
            ->where('can_apply_site_kit', true)
            ->has('kits', 3)
            ->where('kits.0.key', 'restaurant')
            ->where('kits.1.key', 'retail')
            ->where('kits.2.key', 'hotel')
            ->has('kits.0.pages', 4)
            ->has('kits.0.preview_blocks')
            ->missing('kits.0.pages.0.blocks')
        );
});

test('established workspaces can preview kits but cannot apply them', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create([
        'user_id' => $user->id,
        'subdomain' => 'design-established',
    ]);

    $this->actingAs($user)
        ->get(route('tenant.designs.index', ['tenant' => $tenant->subdomain]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('DesignLibrary')
            ->where('can_apply_site_kit', false)
        );
});

test('users cannot view another tenant design library', function () {
    $owner = User::factory()->create();
    $tenant = Tenant::factory()->create([
        'user_id' => $owner->id,
        'subdomain' => 'design-protected',
    ]);
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->get(route('tenant.designs.index', ['tenant' => $tenant->subdomain]))
        ->assertForbidden();
});

test('initial site kit eligibility requires a completely empty pending workspace', function () {
    $eligibleTenant = Tenant::factory()->awaitingSiteSetup()->create();
    $tenantWithPage = Tenant::factory()->awaitingSiteSetup()->create();
    $tenantWithPage->pages()->create([
        'title' => 'Home',
        'slug' => 'home',
        'is_homepage' => true,
        'draft_config' => [],
    ]);
    $tenantWithTheme = Tenant::factory()->awaitingSiteSetup()->create([
        'theme_config' => ['borderRadius' => '8px'],
    ]);
    $tenantWithNavigation = Tenant::factory()->awaitingSiteSetup()->create([
        'navigation_config' => ['header' => [], 'footer' => []],
    ]);
    $completedTenant = Tenant::factory()->create();

    expect($eligibleTenant->isEligibleForInitialSiteKit())->toBeTrue()
        ->and($tenantWithPage->isEligibleForInitialSiteKit())->toBeFalse()
        ->and($tenantWithTheme->isEligibleForInitialSiteKit())->toBeFalse()
        ->and($tenantWithNavigation->isEligibleForInitialSiteKit())->toBeFalse()
        ->and($completedTenant->isEligibleForInitialSiteKit())->toBeFalse();
});

test('pending empty workspaces are redirected from the editor to the design library', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $user->id,
        'subdomain' => 'design-pending',
    ]);

    $this->actingAs($user)
        ->get(route('tenant.editor', ['tenant' => $tenant->subdomain]))
        ->assertRedirect(route('tenant.designs.index', ['tenant' => $tenant->subdomain]));

    expect($tenant->pages()->count())->toBe(0);
});

test('successful workspace mutations permanently complete initial setup', function () {
    $pageUser = User::factory()->create();
    $pageTenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $pageUser->id,
        'subdomain' => 'setup-page-change',
    ]);
    $page = $pageTenant->pages()->create([
        'title' => 'Home',
        'slug' => 'home',
        'is_homepage' => true,
        'draft_config' => [],
    ]);

    $this->actingAs($pageUser)
        ->postJson(route('tenant.page.save', ['tenant' => $pageTenant->subdomain]), [
            'page_id' => $page->id,
            'draft_config' => [
                ['id' => 'setup-hero', 'type' => 'HeroBlock', 'props' => ['headline' => 'Started', 'subheadline' => 'Editing']],
            ],
        ])
        ->assertOk();

    $themeUser = User::factory()->create();
    $themeTenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $themeUser->id,
        'subdomain' => 'setup-theme-change',
    ]);

    $this->actingAs($themeUser)
        ->patchJson(route('tenant.theme.update', ['tenant' => $themeTenant->subdomain]), [
            'colors' => [
                'primary' => '#112233',
                'secondary' => '#445566',
                'background' => '#ffffff',
                'text' => '#111111',
            ],
        ])
        ->assertOk();

    $navigationUser = User::factory()->create();
    $navigationTenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $navigationUser->id,
        'subdomain' => 'setup-navigation-change',
    ]);

    $this->actingAs($navigationUser)
        ->patchJson(route('tenant.navigation.update', ['tenant' => $navigationTenant->subdomain]), [
            'navigation_config' => [
                'header' => ['showLogo' => true, 'items' => [], 'ctaButton' => ['show' => false]],
                'footer' => ['copyright' => ''],
            ],
        ])
        ->assertOk();

    expect($pageTenant->refresh()->site_setup_completed_at)->not->toBeNull()
        ->and($themeTenant->refresh()->site_setup_completed_at)->not->toBeNull()
        ->and($navigationTenant->refresh()->site_setup_completed_at)->not->toBeNull();
});
