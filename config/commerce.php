<?php

$money = static fn (int $amountMinor, string $formatted): array => [
    'amountMinor' => $amountMinor,
    'currency' => 'USD',
    'formatted' => $formatted,
];

$product = static function (
    string $handle,
    string $title,
    string $vendor,
    string $description,
    string $category,
    int $amountMinor,
    string $formatted,
    array $options,
    string $badge = '',
    ?array $compareAtPrice = null,
    bool $available = true,
) use ($money): array {
    return [
        'id' => "fixture-product-{$handle}",
        'handle' => $handle,
        'title' => $title,
        'vendor' => $vendor,
        'description' => $description,
        'category' => $category,
        'badge' => $badge,
        'available' => $available,
        'url' => "/product?commerce_product={$handle}",
        'images' => [
            ['src' => '', 'alt' => "{$title} product image"],
            ['src' => '', 'alt' => "{$title} detail image"],
        ],
        'price' => $money($amountMinor, $formatted),
        'compareAtPrice' => $compareAtPrice,
        'variants' => collect($options)->map(fn (string $option, int $index): array => [
            'id' => "fixture-variant-{$handle}-".($index + 1),
            'title' => $option,
            'options' => ['Style' => $option],
            'price' => $money($amountMinor, $formatted),
            'available' => $available && $index !== count($options) - 1,
        ])->all(),
    ];
};

return [
    'driver' => env('COMMERCE_DRIVER', 'fixture'),

    'fixtures' => [
        'products' => [
            'linen-throw' => $product('linen-throw', 'Linen throw', 'Fieldwork Studio', 'A relaxed everyday layer woven from washed European linen.', 'Home', 4800, '$48.00', ['Natural', 'Charcoal'], 'New'),
            'stoneware-cup' => $product('stoneware-cup', 'Stoneware cup', 'Common Form', 'Hand-finished stoneware with a comfortable, softly rounded handle.', 'Home', 2400, '$24.00', ['Chalk', 'Terracotta']),
            'canvas-tote' => $product('canvas-tote', 'Canvas tote', 'Field Notes', 'A generous carryall cut from heavyweight cotton canvas.', 'Wear', 3600, '$36.00', ['Natural', 'Ink']),
            'cedar-candle' => $product('cedar-candle', 'Cedar candle', 'Quiet Hours', 'Cedar, dry tobacco, and soft amber poured into reusable glass.', 'Home', 3200, '$32.00', ['8 oz'], 'Sale', $money(4000, '$40.00')),
            'wool-cap' => $product('wool-cap', 'Wool cap', 'North Standard', 'A soft six-panel cap made from brushed recycled wool.', 'Wear', 4200, '$42.00', ['Olive', 'Navy']),
            'oak-board' => $product('oak-board', 'Oak serving board', 'Common Form', 'Solid white oak shaped and finished by hand.', 'Home', 5800, '$58.00', ['Small', 'Large']),
            'paper-journal' => $product('paper-journal', 'Clothbound journal', 'Field Notes', 'Lay-flat pages bound in tactile book cloth.', 'Gift', 1800, '$18.00', ['Moss', 'Ochre']),
            'market-basket' => $product('market-basket', 'Market basket', 'Atelier Local', 'A handwoven palm basket for errands and home storage.', 'Gift', 6400, '$64.00', ['Natural'], '', null, false),
        ],
        'product_sources' => [
            'featured' => ['linen-throw', 'stoneware-cup', 'canvas-tote', 'cedar-candle'],
            'all' => ['linen-throw', 'stoneware-cup', 'canvas-tote', 'cedar-candle', 'wool-cap', 'oak-board', 'paper-journal', 'market-basket'],
            'related' => ['stoneware-cup', 'cedar-candle', 'oak-board', 'paper-journal'],
        ],
        'collections' => [
            'home' => ['id' => 'fixture-collection-home', 'handle' => 'home', 'title' => 'Home', 'subtitle' => 'Objects for considered rooms', 'imageSrc' => '', 'imageAlt' => 'Home collection', 'url' => '/shop?collection=home'],
            'wear' => ['id' => 'fixture-collection-wear', 'handle' => 'wear', 'title' => 'Wear', 'subtitle' => 'Useful pieces made to last', 'imageSrc' => '', 'imageAlt' => 'Wear collection', 'url' => '/shop?collection=wear'],
            'gift' => ['id' => 'fixture-collection-gift', 'handle' => 'gift', 'title' => 'Gift', 'subtitle' => 'Thoughtful finds for giving', 'imageSrc' => '', 'imageAlt' => 'Gift collection', 'url' => '/shop?collection=gift'],
        ],
        'collection_sources' => [
            'featured' => ['home', 'wear', 'gift'],
        ],
    ],
];
