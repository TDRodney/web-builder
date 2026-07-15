<?php

namespace App\Commerce\Contracts;

use App\Models\Tenant;

interface CommerceProvider
{
    public function key(): string;

    /**
     * Resolve a normalized, non-persisted commerce resource for one block.
     *
     * @param  array<string, mixed>  $options
     * @return array{status: 'ready'|'unavailable'|'error', resource: string, source: string, data: mixed, message: ?string}
     */
    public function resolve(Tenant $tenant, string $resource, string $source, array $options = []): array;

    /** @return array<int, array{resource: string, source: string, label: string}> */
    public function previewOptions(): array;

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function cart(Tenant $tenant, array $state): array;

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function addCartLine(Tenant $tenant, array $state, string $variantId, int $quantity): array;

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function updateCartLine(Tenant $tenant, array $state, string $variantId, int $quantity): array;

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function removeCartLine(Tenant $tenant, array $state, string $variantId): array;

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function checkout(Tenant $tenant, array $state): array;
}
