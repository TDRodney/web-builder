<?php

use App\Rules\ValidatesDesignCatalog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

function validDesignCatalog(): array
{
    return [
        'schema_version' => 1,
        'styles' => [
            'editorial' => [
                'label' => 'Editorial',
                'theme_config' => ['colors' => ['primary' => '#111111']],
            ],
        ],
        'page_layouts' => [
            'standard-home' => [
                'label' => 'Standard Home',
                'blocks' => [
                    [
                        'id' => 'hero-root',
                        'type' => 'HeroBlock',
                        'props' => [
                            'padding' => 40,
                            'backgroundColor' => 'transparent',
                            'headline' => 'Welcome',
                            'subheadline' => 'A reusable starting point.',
                        ],
                    ],
                ],
            ],
        ],
        'site_kits' => [
            'example-industry' => [
                'label' => 'Example Industry',
                'industry' => 'Example',
                'description' => 'Fixture used to verify the catalog contract.',
                'style_key' => 'editorial',
                'pages' => [
                    [
                        'title' => 'Home',
                        'slug' => 'home',
                        'layout_key' => 'standard-home',
                        'is_homepage' => true,
                    ],
                ],
            ],
        ],
    ];
}

function designCatalogValidator(array $catalog): ValidationValidator
{
    return Validator::make(
        ['catalog' => $catalog],
        ['catalog' => [new ValidatesDesignCatalog]],
    );
}

function flattenDesignNodes(array $nodes): array
{
    $flattened = [];

    foreach ($nodes as $node) {
        if (! is_array($node)) {
            continue;
        }

        $flattened[] = $node;

        if (isset($node['children']) && is_array($node['children'])) {
            $flattened = [...$flattened, ...flattenDesignNodes($node['children'])];
        }
    }

    return $flattened;
}

test('the approved design catalog is valid', function () {
    expect(designCatalogValidator(config('designs'))->passes())->toBeTrue();
});

test('the catalog contains only the three approved initial kits and page inventories', function () {
    $siteKits = config('designs.site_kits');

    expect(array_keys($siteKits))->toBe(['restaurant', 'retail', 'hotel'])
        ->and(array_column($siteKits['restaurant']['pages'], 'slug'))->toBe(['home', 'menu', 'about', 'reservations'])
        ->and(array_column($siteKits['retail']['pages'], 'slug'))->toBe(['home', 'shop', 'about', 'contact'])
        ->and(array_column($siteKits['hotel']['pages'], 'slug'))->toBe(['home', 'rooms', 'amenities', 'contact']);
});

test('every approved page layout includes an editable media placeholder', function () {
    foreach (config('designs.page_layouts') as $layout) {
        $imageNodes = array_filter(
            flattenDesignNodes($layout['blocks']),
            static fn (array $node): bool => $node['type'] === 'ImageBlock',
        );

        expect($imageNodes)->not->toBeEmpty();

        foreach ($imageNodes as $imageNode) {
            expect($imageNode['props']['src'])->toBe('')
                ->and($imageNode['props']['alt'])->not->toBeEmpty()
                ->and($imageNode['props']['backgroundColor'])->toBe('transparent');
        }
    }
});

test('kit navigation links resolve to pages within the same kit', function () {
    $catalog = config('designs');

    foreach ($catalog['site_kits'] as $siteKit) {
        $pageSlugs = array_column($siteKit['pages'], 'slug');
        $navigation = $catalog['styles'][$siteKit['style_key']]['navigation_config']['header'];
        $navigationSlugs = array_column($navigation['items'], 'slug');
        $navigationSlugs[] = $navigation['ctaButton']['slug'];

        foreach ($navigationSlugs as $navigationSlug) {
            expect($pageSlugs)->toContain($navigationSlug);
        }
    }
});

test('a complete design catalog is valid', function () {
    expect(designCatalogValidator(validDesignCatalog())->passes())->toBeTrue();
});

test('site kits must reference registered styles and shared layouts', function () {
    $catalog = validDesignCatalog();
    $catalog['site_kits']['example-industry']['style_key'] = 'missing-style';
    $catalog['site_kits']['example-industry']['pages'][0]['layout_key'] = 'missing-layout';

    $validator = designCatalogValidator($catalog);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('catalog'))->toContain("unknown style 'missing-style'")
        ->and($validator->errors()->get('catalog'))->toContain("The site kit 'example-industry' references unknown page layout 'missing-layout'.");
});

test('shared layouts reuse the registered block schema and unique ids', function () {
    $catalog = validDesignCatalog();
    $catalog['page_layouts']['standard-home']['blocks'] = [
        ['id' => 'duplicate', 'type' => 'UnknownBlock', 'props' => []],
        ['id' => 'duplicate', 'type' => 'HeroBlock', 'props' => []],
    ];

    $validator = designCatalogValidator($catalog);

    expect($validator->fails())->toBeTrue()
        ->and(implode(' ', $validator->errors()->get('catalog')))->toContain("unrecognized type 'UnknownBlock'")
        ->and($validator->errors()->get('catalog'))->toContain("The shared page layout 'standard-home' must use unique block IDs.");
});

test('site kits require unique slugs and exactly one homepage', function () {
    $catalog = validDesignCatalog();
    $catalog['site_kits']['example-industry']['pages'][] = [
        'title' => 'Duplicate Home',
        'slug' => 'home',
        'layout_key' => 'standard-home',
        'is_homepage' => true,
    ];

    $validator = designCatalogValidator($catalog);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->get('catalog'))->toContain("The site kit 'example-industry' must use unique page slugs.")
        ->and($validator->errors()->get('catalog'))->toContain("The site kit 'example-industry' must define exactly one homepage.");
});
