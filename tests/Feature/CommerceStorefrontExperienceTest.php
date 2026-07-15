<?php

use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function commerceTenant(string $subdomain): Tenant
{
    $user = User::factory()->create();

    return Tenant::factory()->create([
        'user_id' => $user->id,
        'subdomain' => $subdomain,
        'site_setup_completed_at' => now(),
        'navigation_config' => [
            'header' => ['showLogo' => true, 'items' => []],
            'footer' => ['copyright' => ''],
            'commerce' => ['enabled' => true, 'cart_slug' => 'cart', 'product_slug' => 'product'],
        ],
    ]);
}

test('fixture cart validates variants and returns provider-calculated totals', function () {
    commerceTenant('fixture-cart');

    $this->getJson('http://fixture-cart.domain.localhost/commerce/cart')
        ->assertOk()
        ->assertJsonPath('cart.itemCount', 0)
        ->assertJsonPath('cart.total.formatted', '$0.00');

    $this->postJson('http://fixture-cart.domain.localhost/commerce/cart/lines', [
        'variant_id' => 'fixture-variant-linen-throw-1',
        'quantity' => 2,
    ])->assertOk()
        ->assertJsonPath('cart.itemCount', 2)
        ->assertJsonPath('cart.lines.0.title', 'Linen throw')
        ->assertJsonPath('cart.lines.0.lineTotal.amountMinor', 9600)
        ->assertJsonPath('cart.total.formatted', '$96.00');

    $this->patchJson('http://fixture-cart.domain.localhost/commerce/cart/lines/fixture-variant-linen-throw-1', [
        'quantity' => 3,
    ])->assertOk()
        ->assertJsonPath('cart.itemCount', 3)
        ->assertJsonPath('cart.total.amountMinor', 14400);
});

test('fixture cart rejects unavailable variants without changing its state', function () {
    commerceTenant('fixture-unavailable');

    $this->postJson('http://fixture-unavailable.domain.localhost/commerce/cart/lines', [
        'variant_id' => 'fixture-variant-market-basket-1',
        'quantity' => 1,
    ])->assertUnprocessable()
        ->assertJsonPath('message', 'That option is no longer available.')
        ->assertJsonPath('cart.itemCount', 0);
});

test('fixture cart sessions are isolated by tenant', function () {
    commerceTenant('cart-one');
    commerceTenant('cart-two');

    $this->postJson('http://cart-one.domain.localhost/commerce/cart/lines', [
        'variant_id' => 'fixture-variant-stoneware-cup-1',
        'quantity' => 1,
    ])->assertOk();

    $this->getJson('http://cart-one.domain.localhost/commerce/cart')
        ->assertJsonPath('cart.itemCount', 1);
    $this->getJson('http://cart-two.domain.localhost/commerce/cart')
        ->assertJsonPath('cart.itemCount', 0);
});

test('commerce interaction routes remain unavailable for non-commerce tenants', function () {
    Tenant::factory()->create(['subdomain' => 'plain-site']);

    $this->getJson('http://plain-site.domain.localhost/commerce/cart')
        ->assertNotFound();
});

test('checkout returns a provider URL and renders the fixture handoff page', function () {
    commerceTenant('fixture-checkout');

    $this->postJson('http://fixture-checkout.domain.localhost/commerce/cart/lines', [
        'variant_id' => 'fixture-variant-cedar-candle-1',
        'quantity' => 1,
    ])->assertOk();

    $response = $this->postJson('http://fixture-checkout.domain.localhost/commerce/checkout')
        ->assertOk()
        ->assertJsonPath('checkoutUrl', 'http://fixture-checkout.domain.localhost/commerce/fixture-checkout');

    $this->get($response->json('checkoutUrl'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tenant/FixtureCheckout')
            ->where('cart.itemCount', 1)
            ->where('cart.total.formatted', '$32.00')
        );
});

test('public product query and editor preview override runtime hydration only', function () {
    $tenant = commerceTenant('fixture-preview');
    $productBlock = [[
        'id' => 'product-preview',
        'type' => 'ProductDetailBlock',
        'props' => ['bindingVersion' => 1, 'sourceKey' => 'linen-throw'],
        'children' => [],
    ]];
    $tenant->pages()->create([
        'title' => 'Product',
        'slug' => 'product',
        'is_homepage' => true,
        'draft_config' => $productBlock,
        'published_config' => $productBlock,
    ]);

    $this->get('http://fixture-preview.domain.localhost/?commerce_product=market-basket')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('commerce_hydration.blocks.product-preview.source', 'market-basket')
            ->where('commerce_hydration.blocks.product-preview.data.available', false)
            ->where('page.published_config.0.props.sourceKey', 'linen-throw')
        );

    $this->actingAs($tenant->user)
        ->get('http://fixture-preview.domain.localhost/editor?commerce_preview=cedar-candle')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('commerce_preview.selected', 'cedar-candle')
            ->where('commerce_hydration.blocks.product-preview.source', 'cedar-candle')
            ->where('page.draft_config.0.props.sourceKey', 'linen-throw')
        );
});
