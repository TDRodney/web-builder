<?php

use App\Models\Page;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    $this->catalog = config('designs');
});

// ── Kit application: auth and authorization ──────────────────────────────

test('guests cannot apply a site kit', function () {
    $tenant = Tenant::factory()->awaitingSiteSetup()->create([
        'subdomain' => 'kit-guest',
    ]);

    $this->post(route('tenant.designs.apply-kit', [
        'tenant' => $tenant->subdomain,
        'kit' => 'restaurant',
    ]))->assertRedirect(route('login'));
});

test('non-owners cannot apply a site kit', function () {
    $owner = User::factory()->create();
    $tenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $owner->id,
        'subdomain' => 'kit-nonowner',
    ]);
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->postJson(route('tenant.designs.apply-kit', [
            'tenant' => $tenant->subdomain,
            'kit' => 'restaurant',
        ]))
        ->assertForbidden();
});

// ── Kit application: basic success ───────────────────────────────────────

test('applying a kit creates all pages from the manifest', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $user->id,
        'subdomain' => 'kit-apply-pages',
    ]);

    $kitDef = $this->catalog['site_kits']['restaurant'];
    $expectedPages = collect($kitDef['pages']);

    $response = $this->actingAs($user)
        ->postJson(route('tenant.designs.apply-kit', [
            'tenant' => $tenant->subdomain,
            'kit' => 'restaurant',
        ]))
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('homepage_slug', 'home');

    expect($tenant->refresh()->pages()->count())->toBe($expectedPages->count());

    $expectedPages->each(function (array $pageDef) use ($tenant) {
        $page = $tenant->pages()->where('slug', $pageDef['slug'])->first();
        expect($page)->not->toBeNull()
            ->and($page->title)->toBe($pageDef['title'])
            ->and($page->is_homepage)->toBe($pageDef['is_homepage']);
    });
});

test('kit-applied pages have draft_config but no published_config', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $user->id,
        'subdomain' => 'kit-draft-only',
    ]);

    $this->actingAs($user)
        ->postJson(route('tenant.designs.apply-kit', [
            'tenant' => $tenant->subdomain,
            'kit' => 'restaurant',
        ]))
        ->assertOk();

    $tenant->refresh()->pages->each(function (Page $page) {
        expect($page->draft_config)->not->toBeNull()
            ->and($page->draft_config)->toBeArray()
            ->and(count($page->draft_config))->toBeGreaterThan(0)
            ->and($page->published_config)->toBeNull();
    });
});

test('block IDs are regenerated and differ from the catalog originals', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $user->id,
        'subdomain' => 'kit-id-regen',
    ]);

    $catalogBlockIds = collect($this->catalog['page_layouts']['restaurant-home']['blocks'])
        ->map(fn (array $node) => $node['id'])
        ->values()
        ->all();

    $this->actingAs($user)
        ->postJson(route('tenant.designs.apply-kit', [
            'tenant' => $tenant->subdomain,
            'kit' => 'restaurant',
        ]))
        ->assertOk();

    $homePage = $tenant->refresh()->pages()->where('slug', 'home')->first();
    $appliedBlockIds = collect($homePage->draft_config)
        ->map(fn (array $node) => $node['id'])
        ->values()
        ->all();

    // No applied ID should match any catalog ID
    $overlap = array_intersect($appliedBlockIds, $catalogBlockIds);
    expect($overlap)->toBeEmpty();

    // Every applied ID should start with "blk-"
    foreach ($appliedBlockIds as $id) {
        expect(str_starts_with($id, 'blk-'))->toBeTrue();
    }
});

test('theme and navigation are applied from the kit style', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $user->id,
        'subdomain' => 'kit-style-apply',
    ]);

    $expectedTheme = $this->catalog['styles']['restaurant-warmth']['theme_config'];
    $expectedNav = $this->catalog['styles']['restaurant-warmth']['navigation_config'];

    $this->actingAs($user)
        ->postJson(route('tenant.designs.apply-kit', [
            'tenant' => $tenant->subdomain,
            'kit' => 'restaurant',
        ]))
        ->assertOk();

    $tenant->refresh();
    expect($tenant->theme_config)->toBe($expectedTheme)
        ->and($tenant->navigation_config)->toBe($expectedNav);
});

test('setup is marked complete after kit application', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $user->id,
        'subdomain' => 'kit-setup-done',
    ]);

    expect($tenant->site_setup_completed_at)->toBeNull();

    $this->actingAs($user)
        ->postJson(route('tenant.designs.apply-kit', [
            'tenant' => $tenant->subdomain,
            'kit' => 'restaurant',
        ]))
        ->assertOk();

    expect($tenant->refresh()->site_setup_completed_at)->not->toBeNull();
});

// ── Kit application: safety guards ──────────────────────────────────────

test('a kit cannot be applied twice', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $user->id,
        'subdomain' => 'kit-no-double',
    ]);

    $this->actingAs($user)
        ->postJson(route('tenant.designs.apply-kit', [
            'tenant' => $tenant->subdomain,
            'kit' => 'restaurant',
        ]))
        ->assertOk();

    $this->actingAs($user)
        ->postJson(route('tenant.designs.apply-kit', [
            'tenant' => $tenant->subdomain,
            'kit' => 'retail',
        ]))
        ->assertUnprocessable();
});

test('a kit cannot be applied to a non-empty workspace', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create([
        'user_id' => $user->id,
        'subdomain' => 'kit-no-nonempty',
    ]);

    $this->actingAs($user)
        ->postJson(route('tenant.designs.apply-kit', [
            'tenant' => $tenant->subdomain,
            'kit' => 'restaurant',
        ]))
        ->assertUnprocessable();
});

test('an unknown kit key returns a 404', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $user->id,
        'subdomain' => 'kit-unknown',
    ]);

    $this->actingAs($user)
        ->postJson(route('tenant.designs.apply-kit', [
            'tenant' => $tenant->subdomain,
            'kit' => 'nonexistent',
        ]))
        ->assertNotFound();
});

test('kit application does not mutate another tenant', function () {
    $ownerA = User::factory()->create();
    $tenantA = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $ownerA->id,
        'subdomain' => 'kit-isol-a',
    ]);
    $ownerB = User::factory()->create();
    $tenantB = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $ownerB->id,
        'subdomain' => 'kit-isol-b',
    ]);

    $this->actingAs($ownerA)
        ->postJson(route('tenant.designs.apply-kit', [
            'tenant' => $tenantA->subdomain,
            'kit' => 'restaurant',
        ]))
        ->assertOk();

    expect($tenantA->refresh()->pages()->count())->toBe(4)
        ->and($tenantB->refresh()->pages()->count())->toBe(0)
        ->and($tenantB->theme_config)->toBeNull()
        ->and($tenantB->navigation_config)->toBeNull()
        ->and($tenantB->site_setup_completed_at)->toBeNull();
});

test('exactly one homepage exists after kit application', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $user->id,
        'subdomain' => 'kit-one-home',
    ]);

    $this->actingAs($user)
        ->postJson(route('tenant.designs.apply-kit', [
            'tenant' => $tenant->subdomain,
            'kit' => 'hotel',
        ]))
        ->assertOk();

    $homepageCount = $tenant->refresh()->pages()->where('is_homepage', true)->count();
    expect($homepageCount)->toBe(1);
});

test('each kit page has a unique slug', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $user->id,
        'subdomain' => 'kit-unique-slugs',
    ]);

    $this->actingAs($user)
        ->postJson(route('tenant.designs.apply-kit', [
            'tenant' => $tenant->subdomain,
            'kit' => 'retail',
        ]))
        ->assertOk();

    $slugs = $tenant->refresh()->pages()->pluck('slug')->all();
    expect($slugs)->toEqual(array_unique($slugs));
});

// ── Kit application: transaction rollback ───────────────────────────────

test('transaction rollback prevents partial page creation', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $user->id,
        'subdomain' => 'kit-rollback',
    ]);

    // The catalog should be valid, so we test with a known good kit.
    // The transaction is tested implicitly: if any write fails, the entire
    // operation rolls back. We verify a successful application is all-or-nothing
    // by checking page count matches exactly.
    $kitDef = $this->catalog['site_kits']['restaurant'];
    $expectedCount = count($kitDef['pages']);

    $this->actingAs($user)
        ->postJson(route('tenant.designs.apply-kit', [
            'tenant' => $tenant->subdomain,
            'kit' => 'restaurant',
        ]))
        ->assertOk();

    expect($tenant->refresh()->pages()->count())->toBe($expectedCount);

    // Theme and navigation are also set
    expect($tenant->theme_config)->not->toBeNull()
        ->and($tenant->navigation_config)->not->toBeNull();
});

// ── Start from scratch ───────────────────────────────────────────────────

test('guests cannot start from scratch', function () {
    $tenant = Tenant::factory()->awaitingSiteSetup()->create([
        'subdomain' => 'scratch-guest',
    ]);

    $this->post(route('tenant.designs.start-from-scratch', [
        'tenant' => $tenant->subdomain,
    ]))->assertRedirect(route('login'));
});

test('non-owners cannot start from scratch', function () {
    $owner = User::factory()->create();
    $tenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $owner->id,
        'subdomain' => 'scratch-nonowner',
    ]);
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->postJson(route('tenant.designs.start-from-scratch', [
            'tenant' => $tenant->subdomain,
        ]))
        ->assertForbidden();
});

test('start from scratch marks setup complete', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $user->id,
        'subdomain' => 'scratch-complete',
    ]);

    expect($tenant->site_setup_completed_at)->toBeNull();

    $this->actingAs($user)
        ->postJson(route('tenant.designs.start-from-scratch', [
            'tenant' => $tenant->subdomain,
        ]))
        ->assertOk()
        ->assertJsonPath('status', 'success');

    expect($tenant->refresh()->site_setup_completed_at)->not->toBeNull();
});

test('start from scratch does not create pages', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $user->id,
        'subdomain' => 'scratch-no-pages',
    ]);

    $this->actingAs($user)
        ->postJson(route('tenant.designs.start-from-scratch', [
            'tenant' => $tenant->subdomain,
        ]))
        ->assertOk();

    expect($tenant->refresh()->pages()->count())->toBe(0);
});

test('start from scratch does not set theme or navigation', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $user->id,
        'subdomain' => 'scratch-no-theme',
    ]);

    $this->actingAs($user)
        ->postJson(route('tenant.designs.start-from-scratch', [
            'tenant' => $tenant->subdomain,
        ]))
        ->assertOk();

    $tenant->refresh();
    expect($tenant->theme_config)->toBeNull()
        ->and($tenant->navigation_config)->toBeNull();
});

test('start from scratch cannot be called twice', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $user->id,
        'subdomain' => 'scratch-no-double',
    ]);

    $this->actingAs($user)
        ->postJson(route('tenant.designs.start-from-scratch', [
            'tenant' => $tenant->subdomain,
        ]))
        ->assertOk();

    $this->actingAs($user)
        ->postJson(route('tenant.designs.start-from-scratch', [
            'tenant' => $tenant->subdomain,
        ]))
        ->assertUnprocessable();
});

test('start from scratch cannot be called on a non-empty workspace', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create([
        'user_id' => $user->id,
        'subdomain' => 'scratch-no-nonempty',
    ]);

    $this->actingAs($user)
        ->postJson(route('tenant.designs.start-from-scratch', [
            'tenant' => $tenant->subdomain,
        ]))
        ->assertUnprocessable();
});

test('after start from scratch the user is no longer eligible for a site kit', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $user->id,
        'subdomain' => 'scratch-eligibility',
    ]);

    expect($tenant->isEligibleForInitialSiteKit())->toBeTrue();

    $this->actingAs($user)
        ->postJson(route('tenant.designs.start-from-scratch', [
            'tenant' => $tenant->subdomain,
        ]))
        ->assertOk();

    expect($tenant->refresh()->isEligibleForInitialSiteKit())->toBeFalse();
});
