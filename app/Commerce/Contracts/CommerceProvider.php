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
}
