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

    public function previewOptions(): array
    {
        return collect(config('commerce.fixtures.products', []))
            ->map(fn (array $product, string $source): array => [
                'resource' => 'product',
                'source' => $source,
                'label' => $product['title'].($product['available'] ? '' : ' — unavailable'),
            ])
            ->values()
            ->all();
    }

    public function cart(Tenant $tenant, array $state): array
    {
        return $this->cartResult($state);
    }

    public function addCartLine(Tenant $tenant, array $state, string $variantId, int $quantity): array
    {
        $match = $this->findVariant($variantId);

        if ($match === null || ! $match['product']['available'] || ! $match['variant']['available']) {
            return $this->cartError($state, 'That option is no longer available.');
        }

        $lines = collect($state['lines'] ?? []);
        $existingIndex = $lines->search(fn (array $line): bool => ($line['variantId'] ?? null) === $variantId);

        if ($existingIndex === false) {
            $lines->push(['variantId' => $variantId, 'quantity' => $quantity]);
        } else {
            $line = $lines->get($existingIndex);
            $line['quantity'] = min(99, (int) $line['quantity'] + $quantity);
            $lines->put($existingIndex, $line);
        }

        return $this->cartResult(['lines' => $lines->values()->all()]);
    }

    public function updateCartLine(Tenant $tenant, array $state, string $variantId, int $quantity): array
    {
        $lines = collect($state['lines'] ?? []);

        if (! $lines->contains(fn (array $line): bool => ($line['variantId'] ?? null) === $variantId)) {
            return $this->cartError($state, 'That cart line was not found.');
        }

        $updated = $lines->map(fn (array $line): array => ($line['variantId'] ?? null) === $variantId
            ? ['variantId' => $variantId, 'quantity' => $quantity]
            : $line);

        return $this->cartResult(['lines' => $updated->values()->all()]);
    }

    public function removeCartLine(Tenant $tenant, array $state, string $variantId): array
    {
        $lines = collect($state['lines'] ?? [])
            ->reject(fn (array $line): bool => ($line['variantId'] ?? null) === $variantId)
            ->values()
            ->all();

        return $this->cartResult(['lines' => $lines]);
    }

    public function checkout(Tenant $tenant, array $state): array
    {
        $result = $this->cartResult($state);

        if (($result['cart']['itemCount'] ?? 0) === 0) {
            return $this->cartError($state, 'Add at least one item before checkout.');
        }

        if (! $result['cart']['checkoutAvailable']) {
            return $this->cartError($state, 'One or more cart items are unavailable.');
        }

        return [
            ...$result,
            'checkoutUrl' => route('tenant.commerce.fixture-checkout', ['tenant' => $tenant->subdomain]),
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
            ->filter(fn (mixed $product): bool => is_array($product));

        $products = match ($options['sort'] ?? 'featured') {
            'price-low' => $products->sortBy(fn (array $product): int => (int) ($product['price']['amountMinor'] ?? 0)),
            'price-high' => $products->sortByDesc(fn (array $product): int => (int) ($product['price']['amountMinor'] ?? 0)),
            'title' => $products->sortBy(fn (array $product): string => (string) ($product['title'] ?? ''), SORT_NATURAL | SORT_FLAG_CASE),
            default => $products,
        };

        $products = $products->take($limit)->values();

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

    /**
     * @return array{product: array<string, mixed>, variant: array<string, mixed>}|null
     */
    private function findVariant(string $variantId): ?array
    {
        foreach (config('commerce.fixtures.products', []) as $product) {
            foreach ($product['variants'] ?? [] as $variant) {
                if (($variant['id'] ?? null) === $variantId) {
                    return ['product' => $product, 'variant' => $variant];
                }
            }
        }

        return null;
    }

    /** @param array<string, mixed> $state */
    private function cartResult(array $state): array
    {
        $normalizedLines = collect($state['lines'] ?? [])->map(function (array $line): ?array {
            $variantId = $line['variantId'] ?? '';
            $match = is_string($variantId) ? $this->findVariant($variantId) : null;

            if ($match === null) {
                return null;
            }

            $quantity = max(1, min((int) ($line['quantity'] ?? 1), 99));
            $unitPrice = $match['variant']['price'];
            $lineAmount = (int) $unitPrice['amountMinor'] * $quantity;

            return [
                'id' => $variantId,
                'variantId' => $variantId,
                'productId' => $match['product']['id'],
                'title' => $match['product']['title'],
                'variantTitle' => $match['variant']['title'],
                'quantity' => $quantity,
                'image' => $match['product']['images'][0] ?? null,
                'unitPrice' => $unitPrice,
                'lineTotal' => $this->money($lineAmount),
                'available' => $match['product']['available'] && $match['variant']['available'],
            ];
        })->filter()->values();

        $subtotal = $normalizedLines->sum(fn (array $line): int => $line['lineTotal']['amountMinor']);
        $normalizedState = [
            'lines' => $normalizedLines->map(fn (array $line): array => [
                'variantId' => $line['variantId'],
                'quantity' => $line['quantity'],
            ])->all(),
        ];

        return [
            'status' => 'ready',
            'message' => null,
            'state' => $normalizedState,
            'cart' => [
                'id' => 'fixture-cart',
                'provider' => 'fixture',
                'currency' => 'USD',
                'lines' => $normalizedLines->all(),
                'itemCount' => $normalizedLines->sum('quantity'),
                'subtotal' => $this->money($subtotal),
                'total' => $this->money($subtotal),
                'checkoutAvailable' => $normalizedLines->every(fn (array $line): bool => $line['available']),
            ],
        ];
    }

    /** @param array<string, mixed> $state */
    private function cartError(array $state, string $message): array
    {
        return [
            ...$this->cartResult($state),
            'status' => 'error',
            'message' => $message,
        ];
    }

    /** @return array{amountMinor: int, currency: string, formatted: string} */
    private function money(int $amountMinor): array
    {
        return [
            'amountMinor' => $amountMinor,
            'currency' => 'USD',
            'formatted' => '$'.number_format($amountMinor / 100, 2),
        ];
    }
}
