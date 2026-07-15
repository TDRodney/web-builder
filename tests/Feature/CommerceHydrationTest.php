<?php

use App\Commerce\CommerceHydrator;
use App\Commerce\Contracts\CommerceProvider;
use App\Commerce\FixtureCommerceProvider;
use App\Commerce\NullCommerceProvider;
use App\Models\Tenant;
use App\Models\User;
use App\Rules\ValidatesBlockSchema;
use Illuminate\Support\Facades\Validator;
use Inertia\Testing\AssertableInertia as Assert;

function boundStorefrontBlocks(): array
{
    return [
        [
            'id' => 'grid-one',
            'type' => 'ProductGridBlock',
            'props' => ['bindingVersion' => 1, 'sourceKey' => 'featured', 'limit' => 4],
            'children' => [],
        ],
        [
            'id' => 'layout-one',
            'type' => 'LayoutGrid',
            'props' => [],
            'children' => [[
                'id' => 'product-one',
                'type' => 'ProductDetailBlock',
                'props' => ['bindingVersion' => 1, 'sourceKey' => 'linen-throw'],
                'children' => [],
            ]],
        ],
    ];
}

test('hydration is keyed by block id and never changes saved block configuration', function () {
    $tenant = Tenant::factory()->create();
    $blocks = boundStorefrontBlocks();
    $original = $blocks;

    $hydration = (new CommerceHydrator(new FixtureCommerceProvider))->hydrate($tenant, $blocks);

    expect($hydration['schemaVersion'])->toBe(1)
        ->and($hydration['provider'])->toBe('fixture')
        ->and($hydration['blocks'])->toHaveKeys(['grid-one', 'product-one'])
        ->and($hydration['blocks']['grid-one']['binding'])->toMatchArray([
            'version' => 1,
            'resource' => 'product-list',
            'source' => 'featured',
        ])
        ->and($hydration['blocks']['grid-one']['data']['products'])->toHaveCount(4)
        ->and($hydration['blocks']['product-one']['data']['price'])->toMatchArray([
            'amountMinor' => 4800,
            'currency' => 'USD',
            'formatted' => '$48.00',
        ])
        ->and($blocks)->toBe($original);
});

test('the null provider returns an explicit safe unavailable state', function () {
    $tenant = Tenant::factory()->create();
    $hydration = (new CommerceHydrator(new NullCommerceProvider))->hydrate($tenant, boundStorefrontBlocks());

    expect($hydration['provider'])->toBe('null')
        ->and($hydration['blocks']['grid-one']['status'])->toBe('unavailable')
        ->and($hydration['blocks']['grid-one']['data'])->toBeNull()
        ->and($hydration['blocks']['grid-one']['message'])->toContain('Connect a commerce provider');
});

test('provider resolution always receives the current tenant', function () {
    $provider = new class implements CommerceProvider
    {
        public array $tenantIds = [];

        public function key(): string
        {
            return 'recording';
        }

        public function resolve(Tenant $tenant, string $resource, string $source, array $options = []): array
        {
            $this->tenantIds[] = $tenant->id;

            return ['status' => 'ready', 'resource' => $resource, 'source' => $source, 'data' => [], 'message' => null];
        }
    };
    $firstTenant = Tenant::factory()->create();
    $secondTenant = Tenant::factory()->create();
    $hydrator = new CommerceHydrator($provider);

    $hydrator->hydrate($firstTenant, [boundStorefrontBlocks()[0]]);
    $hydrator->hydrate($secondTenant, [boundStorefrontBlocks()[0]]);

    expect($provider->tenantIds)->toBe([$firstTenant->id, $secondTenant->id]);
});

test('commerce binding validation is additive and rejects malformed values', function () {
    $blocks = boundStorefrontBlocks();
    $blocks[0]['props']['sourceKey'] = '../another-tenant';

    $validator = Validator::make(['blocks' => $blocks], [
        'blocks' => ['required', 'array', new ValidatesBlockSchema],
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('blocks'))->toContain('sourceKey');
});

test('editor receives fixture hydration separately from the page draft', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create([
        'user_id' => $user->id,
        'subdomain' => 'hydration-editor',
        'site_setup_completed_at' => now(),
    ]);
    $tenant->pages()->create([
        'title' => 'Home',
        'slug' => 'home',
        'is_homepage' => true,
        'draft_config' => [boundStorefrontBlocks()[0]],
    ]);

    $this->actingAs($user)
        ->get('http://hydration-editor.domain.localhost/editor')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tenant/Editor')
            ->where('page.draft_config.0.props.sourceKey', 'featured')
            ->where('commerce_hydration.provider', 'fixture')
            ->where('commerce_hydration.blocks.grid-one.status', 'ready')
            ->has('commerce_hydration.blocks.grid-one.data.products', 4)
        );
});

test('public pages hydrate only their published configuration', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create([
        'user_id' => $user->id,
        'subdomain' => 'hydration-public',
    ]);
    $tenant->pages()->create([
        'title' => 'Home',
        'slug' => 'home',
        'is_homepage' => true,
        'draft_config' => [],
        'published_config' => [boundStorefrontBlocks()[0]],
    ]);

    $this->get('http://hydration-public.domain.localhost/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tenant/PublicPage')
            ->where('commerce_hydration.blocks.grid-one.status', 'ready')
            ->where('page.published_config.0.props.sourceKey', 'featured')
        );
});
