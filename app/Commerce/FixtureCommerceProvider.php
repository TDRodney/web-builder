<?php

namespace App\Commerce;

use App\Commerce\Contracts\CommerceProvider;
use App\Models\Tenant;

class FixtureCommerceProvider implements CommerceProvider
{
    public function key(): string
    {
        return 'fixture';
    }

    public function resolve(Tenant $tenant, string $resource, string $source, array $options = []): array
    {
        $data = match ($resource) {
            'product-list' => $this->productList($source, $options),
            'product' => config("commerce.fixtures.products.{$source}"),
            'collection-list' => $this->collectionList($source, $options),
            default => null,
        };

        if ($data === null) {
            return [
                'status' => 'unavailable',
                'resource' => $resource,
                'source' => $source,
                'data' => null,
                'message' => "Fixture resource [{$resource}:{$source}] was not found.",
            ];
        }

        return [
            'status' => 'ready',
            'resource' => $resource,
            'source' => $source,
            'data' => $data,
            'message' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{products: array<int, array<string, mixed>>, facets: array<int, array<string, mixed>>, pagination: array<string, int>}
     */
    private function productList(string $source, array $options): ?array
    {
        $handles = config("commerce.fixtures.product_sources.{$source}");

        if (! is_array($handles)) {
            return null;
        }

        $limit = max(1, min((int) ($options['limit'] ?? 12), 50));
        $products = collect($handles)
            ->map(fn (string $handle): mixed => config("commerce.fixtures.products.{$handle}"))
            ->filter(fn (mixed $product): bool => is_array($product))
            ->take($limit)
            ->values();

        return [
            'products' => $products->all(),
            'facets' => [
                [
                    'key' => 'category',
                    'label' => 'Category',
                    'values' => $products->groupBy('category')->map(fn ($items, string $value): array => [
                        'value' => $value,
                        'label' => $value,
                        'count' => $items->count(),
                    ])->values()->all(),
                ],
            ],
            'pagination' => [
                'page' => 1,
                'perPage' => $limit,
                'total' => $products->count(),
                'lastPage' => 1,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{collections: array<int, array<string, mixed>>}|null
     */
    private function collectionList(string $source, array $options): ?array
    {
        $handles = config("commerce.fixtures.collection_sources.{$source}");

        if (! is_array($handles)) {
            return null;
        }

        $limit = max(1, min((int) ($options['limit'] ?? 12), 50));

        return [
            'collections' => collect($handles)
                ->map(fn (string $handle): mixed => config("commerce.fixtures.collections.{$handle}"))
                ->filter(fn (mixed $collection): bool => is_array($collection))
                ->take($limit)
                ->values()
                ->all(),
        ];
    }
}
