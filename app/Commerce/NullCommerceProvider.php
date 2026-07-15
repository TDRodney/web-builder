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
}
