<?php

namespace App\Actions\Designs;

/**
 * Builds lightweight gallery data for the shared page-layout picker.
 *
 * Returns only the metadata and a small preview slice needed by the
 * "Choose layout" step of the create-page modal. Full block trees are
 * never sent to the client; they are resolved server-side at apply time.
 */
class BuildPageLayouts
{
    /**
     * The number of top-level block nodes included in each layout preview.
     */
    private const PREVIEW_LIMIT = 3;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(): array
    {
        $pageLayouts = config('designs.page_layouts', []);

        return collect($pageLayouts)
            ->map(function (array $layout, string $key): array {
                $blocks = $layout['blocks'] ?? [];

                return [
                    'key' => $key,
                    'label' => $layout['label'],
                    'page_type' => $layout['page_type'] ?? 'general',
                    'industry' => $layout['industry'] ?? 'shared',
                    'preview_blocks' => array_slice($blocks, 0, self::PREVIEW_LIMIT),
                    'block_count' => count($blocks),
                ];
            })
            ->values()
            ->all();
    }
}
