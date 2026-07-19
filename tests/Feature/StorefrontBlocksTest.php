<?php

use App\Rules\ValidatesBlockSchema;
use Illuminate\Support\Facades\Validator;

function flattenStorefrontNodes(array $nodes): array
{
    return collect($nodes)->flatMap(fn (array $node) => [$node, ...flattenStorefrontNodes($node['children'] ?? [])])->all();
}

test('storefront blocks are registered for both layout parents and backend nesting', function () {
    $types = ['AnnouncementBlock', 'ImageWithTextBlock', 'CollectionListBlock', 'ProductGridBlock', 'ProductDetailBlock', 'CartBlock', 'NewsletterBlock', 'TrustValuesBlock'];

    foreach ($types as $type) {
        expect(config("blocks.definitions.{$type}.type"))->toBe($type)
            ->and(config('blocks.definitions.LayoutGrid.allowedChildren'))->toContain($type)
            ->and(config('blocks.definitions.LayoutColumn.allowedChildren'))->toContain($type)
            ->and(config('blocks.nesting.LayoutGrid'))->toContain($type)
            ->and(config('blocks.nesting.LayoutColumn'))->toContain($type);
    }
});

test('retail layouts form a complete editable storefront in the ordinary block schema', function () {
    $layouts = config('designs.page_layouts');
    $types = collect(['retail-home', 'retail-shop', 'retail-product', 'retail-cart'])
        ->flatMap(fn (string $key) => collect(flattenStorefrontNodes($layouts[$key]['blocks']))->pluck('type'));

    expect($types)->toContain('CollectionListBlock', 'ProductGridBlock', 'ProductDetailBlock', 'CartBlock', 'NewsletterBlock', 'TrustValuesBlock');

    foreach (['retail-home', 'retail-shop', 'retail-product', 'retail-cart'] as $key) {
        expect(Validator::make(['blocks' => $layouts[$key]['blocks']], ['blocks' => ['required', 'array', new ValidatesBlockSchema]])->passes())->toBeTrue();
    }
});

test('storefront starter content retains stable hydration keys and editable media placeholders', function () {
    $nodes = flattenStorefrontNodes(config('designs.page_layouts.retail-home.blocks'));
    $productGrid = collect($nodes)->firstWhere('type', 'ProductGridBlock');

    expect($productGrid['props']['sourceKey'])->toBe('featured')
        ->and(collect($productGrid['props']['products'])->pluck('key')->filter()->count())->toBe(4)
        ->and(collect($productGrid['props']['products'])->pluck('imageSrc')->unique()->all())->toBe(['']);
});
