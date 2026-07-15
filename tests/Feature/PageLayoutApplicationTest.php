<?php

use App\Actions\Designs\BuildPageLayouts;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    $this->catalog = config('designs');
});

// ── Page-layout application: success cases ───────────────────────────────

test('store with a valid layout_key creates a page with cloned block tree', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create([
        'user_id' => $user->id,
        'subdomain' => 'layout-apply',
    ]);

    $layoutKey = 'restaurant-about';
    $layoutBlocks = $this->catalog['page_layouts'][$layoutKey]['blocks'];

    $response = $this->actingAs($user)
        ->postJson("http://{$tenant->subdomain}.domain.localhost/editor/pages", [
            'title' => 'Our Story',
            'slug' => 'story',
            'layout_key' => $layoutKey,
        ]);

    $response->assertOk()
        ->assertJson(['status' => 'success']);

    $page = $tenant->pages()->where('slug', 'story')->first();

    expect($page)->not->toBeNull()
        ->and($page->draft_config)->not->toBeNull()
        ->and(count($page->draft_config))->toBe(count($layoutBlocks))
        // Block types and props match the catalog layout
        ->and(collect($page->draft_config)->pluck('type')->all())
        ->toBe(collect($layoutBlocks)->pluck('type')->all());

    // published_config remains null (draft only)
    expect($page->published_config)->toBeNull();
});

test('store without layout_key still works with the default hero block', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create([
        'user_id' => $user->id,
        'subdomain' => 'layout-blank',
    ]);

    $response = $this->actingAs($user)
        ->postJson("http://{$tenant->subdomain}.domain.localhost/editor/pages", [
            'title' => 'Services',
            'slug' => 'services',
        ]);

    $response->assertOk()
        ->assertJson(['status' => 'success']);

    $page = $tenant->pages()->where('slug', 'services')->first();

    expect($page->draft_config)->toHaveCount(1)
        ->and($page->draft_config[0]['type'])->toBe('HeroBlock');
});

test('block IDs are regenerated and differ from catalog originals', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create([
        'user_id' => $user->id,
        'subdomain' => 'layout-ids',
    ]);

    $layoutKey = 'restaurant-home';
    $catalogBlockIds = collect($this->catalog['page_layouts'][$layoutKey]['blocks'])
        ->map(fn (array $node) => $node['id'])
        ->all();

    $this->actingAs($user)
        ->postJson("http://{$tenant->subdomain}.domain.localhost/editor/pages", [
            'title' => 'Landing',
            'slug' => 'landing',
            'layout_key' => $layoutKey,
        ])
        ->assertOk();

    $page = $tenant->pages()->where('slug', 'landing')->first();
    $appliedBlockIds = collect($page->draft_config)->pluck('id')->all();

    // No overlap with catalog IDs
    expect(array_intersect($appliedBlockIds, $catalogBlockIds))->toBeEmpty();

    // All applied IDs use the regenerated prefix
    foreach ($appliedBlockIds as $id) {
        expect(str_starts_with($id, 'blk-'))->toBeTrue();
    }
});

test('applying a layout marks setup complete', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->awaitingSiteSetup()->create([
        'user_id' => $user->id,
        'subdomain' => 'layout-setup',
    ]);

    expect($tenant->site_setup_completed_at)->toBeNull();

    $this->actingAs($user)
        ->postJson("http://{$tenant->subdomain}.domain.localhost/editor/pages", [
            'title' => 'Home',
            'slug' => 'home',
            'layout_key' => 'retail-home',
        ])
        ->assertOk();

    expect($tenant->refresh()->site_setup_completed_at)->not->toBeNull();
});

// ── Page-layout application: safety guards ───────────────────────────────

test('store with an unknown layout_key fails validation', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create([
        'user_id' => $user->id,
        'subdomain' => 'layout-unknown',
    ]);

    $this->actingAs($user)
        ->postJson("http://{$tenant->subdomain}.domain.localhost/editor/pages", [
            'title' => 'Custom',
            'slug' => 'custom',
            'layout_key' => 'nonexistent-layout',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('layout_key');
});

test('non-owners cannot create a page with a layout', function () {
    $owner = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create([
        'user_id' => $owner->id,
        'subdomain' => 'layout-forbidden',
    ]);
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->postJson("http://{$tenant->subdomain}.domain.localhost/editor/pages", [
            'title' => 'About',
            'slug' => 'about',
            'layout_key' => 'restaurant-about',
        ])
        ->assertForbidden();
});

test('a layout can be applied to a page in an established workspace', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create([
        'user_id' => $user->id,
        'subdomain' => 'layout-established',
    ]);

    // The workspace already has a homepage; adding a layout-based page should still work.
    $response = $this->actingAs($user)
        ->postJson("http://{$tenant->subdomain}.domain.localhost/editor/pages", [
            'title' => 'Rooms',
            'slug' => 'rooms',
            'layout_key' => 'hotel-rooms',
        ]);

    $response->assertOk();

    $page = $tenant->pages()->where('slug', 'rooms')->first();
    expect($page->draft_config)->not->toBeNull()
        ->and(count($page->draft_config))->toBeGreaterThan(1);
});

test('cross-tenant isolation when applying layouts', function () {
    $ownerA = User::factory()->create();
    $tenantA = Tenant::factory()->withHomePage()->create([
        'user_id' => $ownerA->id,
        'subdomain' => 'layout-isol-a',
    ]);
    $ownerB = User::factory()->create();
    $tenantB = Tenant::factory()->withHomePage()->create([
        'user_id' => $ownerB->id,
        'subdomain' => 'layout-isol-b',
    ]);

    $this->actingAs($ownerA)
        ->postJson("http://{$tenantA->subdomain}.domain.localhost/editor/pages", [
            'title' => 'Menu',
            'slug' => 'menu',
            'layout_key' => 'restaurant-menu',
        ])
        ->assertOk();

    // Query the DB directly to bypass TenantScope (which is bound to tenantA).
    expect(DB::table('pages')->where('tenant_id', $tenantA->id)->count())->toBe(2)
        // homepage + menu
        ->and(DB::table('pages')->where('tenant_id', $tenantB->id)->count())->toBe(1); // only homepage
});

// ── Page-layout gallery metadata ─────────────────────────────────────────

test('every page layout has page_type and industry metadata', function () {
    $pageLayouts = config('designs.page_layouts');

    foreach ($pageLayouts as $key => $layout) {
        expect($layout)->toHaveKey('page_type')
            ->and($layout['page_type'])->toBeString()
            ->and($layout['page_type'])->not->toBeEmpty()
            ->and($layout)->toHaveKey('industry')
            ->and($layout['industry'])->toBeString()
            ->and($layout['industry'])->not->toBeEmpty();
    }
});

test('build page layouts action returns lightweight gallery data', function () {
    $builder = app(BuildPageLayouts::class);
    $layouts = $builder->handle();

    expect($layouts)->toHaveCount(13);

    foreach ($layouts as $layout) {
        expect($layout)->toHaveKey('key')
            ->and($layout)->toHaveKey('label')
            ->and($layout)->toHaveKey('page_type')
            ->and($layout)->toHaveKey('industry')
            ->and($layout)->toHaveKey('preview_blocks')
            ->and($layout)->toHaveKey('block_count')
            // Preview blocks are trimmed (max 3)
            ->and(count($layout['preview_blocks']))->toBeLessThanOrEqual(3);
    }
});
