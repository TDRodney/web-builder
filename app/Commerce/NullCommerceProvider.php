<?php

namespace App\Commerce;

use App\Commerce\Contracts\CommerceProvider;
use App\Models\Tenant;

class NullCommerceProvider implements CommerceProvider
{
    public function key(): string
    {
        return 'null';
    }

    public function resolve(Tenant $tenant, string $resource, string $source, array $options = []): array
    {
        return [
            'status' => 'unavailable',
            'resource' => $resource,
            'source' => $source,
            'data' => null,
            'message' => 'Connect a commerce provider to hydrate this block.',
        ];
    }

    public function previewOptions(): array
    {
        return [];
    }

    public function cart(Tenant $tenant, array $state): array
    {
        return $this->unavailableCart($state);
    }

    public function addCartLine(Tenant $tenant, array $state, string $variantId, int $quantity): array
    {
        return $this->unavailableCart($state);
    }

    public function updateCartLine(Tenant $tenant, array $state, string $variantId, int $quantity): array
    {
        return $this->unavailableCart($state);
    }

    public function removeCartLine(Tenant $tenant, array $state, string $variantId): array
    {
        return $this->unavailableCart($state);
    }

    public function checkout(Tenant $tenant, array $state): array
    {
        return $this->unavailableCart($state);
    }

    /** @param array<string, mixed> $state */
    private function unavailableCart(array $state): array
    {
        return [
            'status' => 'unavailable',
            'message' => 'Connect a commerce provider to use the cart.',
            'state' => $state,
            'cart' => null,
        ];
    }
}
