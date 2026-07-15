<?php

namespace App\Commerce;

use App\Commerce\Contracts\CommerceProvider;
use App\Models\Tenant;

class CommerceHydrator
{
    /** @var array<string, string> */
    private const BLOCK_RESOURCES = [
        'CollectionListBlock' => 'collection-list',
        'ProductGridBlock' => 'product-list',
        'ProductDetailBlock' => 'product',
    ];

    public function __construct(private CommerceProvider $provider) {}

    /**
     * Build runtime data for bound blocks without changing their saved configuration.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array{schemaVersion: int, provider: string, blocks: array<string, array<string, mixed>>}
     */
    public function hydrate(Tenant $tenant, array $blocks, array $sourceOverrides = []): array
    {
        $hydratedBlocks = [];

        $this->hydrateNodes($tenant, $blocks, $hydratedBlocks, $sourceOverrides);

        return [
            'schemaVersion' => 1,
            'provider' => $this->provider->key(),
            'blocks' => $hydratedBlocks,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     * @param  array<string, array<string, mixed>>  $hydratedBlocks
     */
    private function hydrateNodes(Tenant $tenant, array $nodes, array &$hydratedBlocks, array $sourceOverrides): void
    {
        foreach ($nodes as $node) {
            $binding = $this->bindingFor($node);
            $nodeId = $node['id'] ?? null;

            if ($binding !== null && is_string($nodeId) && $nodeId !== '') {
                $binding['source'] = $sourceOverrides[$binding['resource']] ?? $binding['source'];
                $hydratedBlocks[$nodeId] = [
                    'binding' => $binding,
                    ...$this->provider->resolve(
                        $tenant,
                        $binding['resource'],
                        $binding['source'],
                        $binding['options'],
                    ),
                ];
            }

            $children = $node['children'] ?? [];

            if (is_array($children)) {
                $this->hydrateNodes($tenant, $children, $hydratedBlocks, $sourceOverrides);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $node
     * @return array{version: int, resource: string, source: string, options: array<string, mixed>}|null
     */
    private function bindingFor(array $node): ?array
    {
        $props = $node['props'] ?? [];

        if (! is_array($props)) {
            return null;
        }

        $structuredBinding = $props['dataBinding'] ?? null;

        if (is_array($structuredBinding)) {
            $resource = $structuredBinding['resource'] ?? null;
            $source = $structuredBinding['source'] ?? null;

            if (is_string($resource) && is_string($source) && $source !== '') {
                return [
                    'version' => (int) ($structuredBinding['version'] ?? 1),
                    'resource' => $resource,
                    'source' => $source,
                    'options' => is_array($structuredBinding['options'] ?? null) ? $structuredBinding['options'] : [],
                ];
            }
        }

        $type = $node['type'] ?? null;
        $source = $props['sourceKey'] ?? null;

        if (! is_string($type) || ! isset(self::BLOCK_RESOURCES[$type]) || ! is_string($source) || $source === '') {
            return null;
        }

        return [
            'version' => (int) ($props['bindingVersion'] ?? 1),
            'resource' => self::BLOCK_RESOURCES[$type],
            'source' => $source,
            'options' => [
                'limit' => max(1, min((int) ($props['limit'] ?? 12), 50)),
            ],
        ];
    }
}
