<?php

namespace App\Actions\Designs;

use App\Rules\ValidatesDesignCatalog;
use Illuminate\Support\Facades\Validator;
use LogicException;

class BuildDesignLibrary
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(): array
    {
        $catalog = config('designs');
        $validator = Validator::make(
            ['catalog' => $catalog],
            ['catalog' => [new ValidatesDesignCatalog]],
        );

        if ($validator->fails()) {
            throw new LogicException('The design catalog is invalid: '.$validator->errors()->first('catalog'));
        }

        return collect($catalog['site_kits'])
            ->map(function (array $siteKit, string $kitKey) use ($catalog): array {
                $style = $catalog['styles'][$siteKit['style_key']];
                $homepage = collect($siteKit['pages'])->firstWhere('is_homepage', true);

                return [
                    'key' => $kitKey,
                    'label' => $siteKit['label'],
                    'industry' => $siteKit['industry'],
                    'description' => $siteKit['description'],
                    'tier' => $siteKit['tier'],
                    'theme_config' => $style['theme_config'],
                    'navigation_config' => $style['navigation_config'] ?? [],
                    'pages' => collect($siteKit['pages'])
                        ->map(fn (array $page): array => [
                            'title' => $page['title'],
                            'slug' => $page['slug'],
                            'is_homepage' => $page['is_homepage'],
                            'preview_blocks' => $catalog['page_layouts'][$page['layout_key']]['blocks'],
                        ])
                        ->values()
                        ->all(),
                    'preview_blocks' => $catalog['page_layouts'][$homepage['layout_key']]['blocks'],
                ];
            })
            ->values()
            ->all();
    }
}
