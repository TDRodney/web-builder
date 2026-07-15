<?php

namespace App\Actions\Designs;

/**
 * Deep-clones a block tree and regenerates every node ID.
 *
 * Used by both site-kit application and single-page layout application so
 * catalog definitions are never live-linked to tenant pages.
 */
class CloneBlockTree
{
    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    public static function handle(array $blocks): array
    {
        return collect($blocks)->map(function (array $node): array {
            $cloned = [
                'id' => 'blk-'.uniqid(),
                'type' => $node['type'],
                'props' => $node['props'],
            ];

            if (isset($node['children']) && is_array($node['children'])) {
                $cloned['children'] = self::handle($node['children']);
            }

            return $cloned;
        })->values()->all();
    }
}
