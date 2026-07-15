<?php

namespace App\Actions\Designs;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class ApplySiteKit
{
    /**
     * Apply a site kit to an eligible tenant workspace.
     *
     * Deep-clones all referenced page-layout block trees, regenerates every
     * block ID, creates ordinary tenant pages (draft_config only), applies
     * the kit's style as theme and navigation defaults, and marks setup
     * complete. The entire operation runs inside a database transaction.
     *
     * @return array{status: string, homepage_slug: string}
     */
    public function handle(Tenant $tenant, string $kitKey): array
    {
        $catalog = config('designs');

        if (! isset($catalog['site_kits'][$kitKey])) {
            abort(404, 'Site kit not found.');
        }

        if (! $tenant->isEligibleForInitialSiteKit()) {
            abort(422, 'Workspace is no longer eligible for an initial site kit.');
        }

        $siteKit = $catalog['site_kits'][$kitKey];
        $style = $catalog['styles'][$siteKit['style_key']];
        $pageLayouts = $catalog['page_layouts'];

        $homepageSlug = DB::transaction(function () use ($tenant, $siteKit, $style, $pageLayouts) {
            foreach ($siteKit['pages'] as $index => $pageDef) {
                $layoutBlocks = $pageLayouts[$pageDef['layout_key']]['blocks'] ?? [];

                $clonedBlocks = CloneBlockTree::handle($layoutBlocks);

                $tenant->pages()->create([
                    'title' => $pageDef['title'],
                    'slug' => $pageDef['slug'],
                    'is_homepage' => $pageDef['is_homepage'],
                    'sort_order' => $index,
                    'draft_config' => $clonedBlocks,
                    'published_config' => null,
                ]);
            }

            $tenant->update([
                'theme_config' => $style['theme_config'],
                'navigation_config' => $style['navigation_config'] ?? [],
            ]);

            $tenant->markSiteSetupCompleted();

            return collect($siteKit['pages'])
                ->firstWhere('is_homepage', true)['slug'];
        });

        return [
            'status' => 'success',
            'homepage_slug' => $homepageSlug,
        ];
    }
}
