<?php

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
