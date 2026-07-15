<?php

use App\Models\CommerceTemplate;
use App\Models\Page;
use App\Models\Tenant;
use Inertia\Testing\AssertableInertia as Assert;

function publishedCommerceTemplate(Tenant $tenant, string $type): CommerceTemplate
{
    return CommerceTemplate::factory()->for($tenant)->create(['type' => $type, 'key' => 'default', 'is_default' => true, 'published_config' => ['schemaVersion' => 1, 'sections' => []]]);
}

test('published collection and product templates render provider resources', function () {
    $tenant = Tenant::factory()->create(['subdomain' => 'connected-store']);
    publishedCommerceTemplate($tenant, 'collection');
    publishedCommerceTemplate($tenant, 'product');
    $this->get(route('tenant.commerce.collection', ['tenant' => $tenant->subdomain, 'handle' => 'new-arrivals']))->assertOk()->assertInertia(fn (Assert $page) => $page->component('Tenant/CommerceStorefront')->where('type', 'collection')->where('resource.data.handle', 'new-arrivals')->has('resource.data.products', 4));
    $this->get(route('tenant.commerce.product', ['tenant' => $tenant->subdomain, 'handle' => 'linen-throw']))->assertOk()->assertInertia(fn (Assert $page) => $page->component('Tenant/CommerceStorefront')->where('type', 'product')->where('resource.data.handle', 'linen-throw'));
});

test('commerce cache is isolated by tenant', function () {
    $first = Tenant::factory()->create(['subdomain' => 'first-store']);
    $second = Tenant::factory()->create(['subdomain' => 'second-store']);
    publishedCommerceTemplate($first, 'product');
    publishedCommerceTemplate($second, 'product');
    $this->get(route('tenant.commerce.product', ['tenant' => $first->subdomain, 'handle' => 'shared']))->assertOk();
    $this->get(route('tenant.commerce.product', ['tenant' => $second->subdomain, 'handle' => 'shared']))->assertOk();
    expect(cache()->has("commerce:{$first->id}:product:shared"))->toBeTrue()->and(cache()->has("commerce:{$second->id}:product:shared"))->toBeTrue();
});

test('legacy public page route remains available', function () {
    $tenant = Tenant::factory()->create(['subdomain' => 'legacy-still-works']);
    Page::factory()->for($tenant)->create(['slug' => 'about', 'published_config' => [['id' => 'text', 'type' => 'AtomicText', 'props' => ['content' => 'Legacy']]]]);
    $this->get(route('tenant.page.public', ['tenant' => $tenant->subdomain, 'slug' => 'about']))->assertOk()->assertInertia(fn (Assert $page) => $page->component('Tenant/PublicPage'));
});
