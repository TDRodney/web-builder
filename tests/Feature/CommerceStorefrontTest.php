<?php

use App\Models\CommerceTemplate;
use App\Models\Page;
use App\Models\Tenant;
use App\Models\User;
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

test('cart is provider owned and redirects to hosted checkout', function () {
    $tenant = Tenant::factory()->create(['subdomain' => 'cart-store']);
    $response = $this->postJson(route('tenant.commerce.cart.lines.store', ['tenant' => $tenant->subdomain]), ['variantId' => 'linen-throw-default', 'quantity' => 1]);
    $response->assertOk()->assertJsonPath('cart.id', 'fake-cart');
    $this->post(route('tenant.commerce.checkout', ['tenant' => $tenant->subdomain]))->assertRedirect('https://checkout.example.test/fake-cart');
});

test('retail kit adds draft commerce templates without publishing or changing legacy pages', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->awaitingSiteSetup()->create(['user_id' => $user->id, 'subdomain' => 'retail-commerce-kit']);
    $response = $this->actingAs($user)->postJson(route('tenant.designs.apply-kit', ['tenant' => $tenant->subdomain, 'kit' => 'retail']))->assertOk();
    expect($tenant->commerceTemplates()->count())->toBe(3)
        ->and($tenant->commerceTemplates()->whereNotNull('published_config')->count())->toBe(0)
        ->and($tenant->pages()->count())->toBe(4);
    $response->assertJsonPath('commerce_template_id', $tenant->commerceTemplates()->where('type', 'home')->value('id'));
});
