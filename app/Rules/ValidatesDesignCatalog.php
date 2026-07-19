<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidatesDesignCatalog implements ValidationRule
{
    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            $fail('The :attribute must be a design catalog array.');

            return;
        }

        if (! isset($value['schema_version']) || ! is_int($value['schema_version']) || $value['schema_version'] < 1) {
            $fail('The :attribute must contain a positive integer schema_version.');
        }

        foreach (['styles', 'page_layouts', 'site_kits'] as $collection) {
            if (! isset($value[$collection]) || ! is_array($value[$collection])) {
                $fail("The :attribute must contain a {$collection} array.");
            }
        }

        if (! isset($value['styles'], $value['page_layouts'], $value['site_kits'])
            || ! is_array($value['styles'])
            || ! is_array($value['page_layouts'])
            || ! is_array($value['site_kits'])) {
            return;
        }

        $this->validateStyles($value['styles'], $fail);
        $this->validatePageLayouts($value['page_layouts'], $fail);
        $this->validateSiteKits($value['site_kits'], $value['styles'], $value['page_layouts'], $fail);
    }

    /**
     * @param  array<string, mixed>  $styles
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    private function validateStyles(array $styles, Closure $fail): void
    {
        foreach ($styles as $styleKey => $style) {
            if (! is_string($styleKey) || ! is_array($style)) {
                $fail('Every design style must have a stable string key and array definition.');

                continue;
            }

            if (! isset($style['label']) || ! is_string($style['label']) || $style['label'] === '') {
                $fail("The design style '{$styleKey}' must contain a non-empty label.");
            }

            if (! isset($style['theme_config']) || ! is_array($style['theme_config'])) {
                $fail("The design style '{$styleKey}' must contain a theme_config array.");
            }

            if (isset($style['navigation_config']) && ! is_array($style['navigation_config'])) {
                $fail("The design style '{$styleKey}' navigation_config must be an array.");
            }
        }
    }

    /**
     * @param  array<string, mixed>  $pageLayouts
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    private function validatePageLayouts(array $pageLayouts, Closure $fail): void
    {
        foreach ($pageLayouts as $layoutKey => $layout) {
            if (! is_string($layoutKey) || ! is_array($layout)) {
                $fail('Every shared page layout must have a stable string key and array definition.');

                continue;
            }

            if (! isset($layout['label']) || ! is_string($layout['label']) || $layout['label'] === '') {
                $fail("The shared page layout '{$layoutKey}' must contain a non-empty label.");
            }

            if (! isset($layout['blocks']) || ! is_array($layout['blocks'])) {
                $fail("The shared page layout '{$layoutKey}' must contain a blocks array.");

                continue;
            }

            $blockValidator = Validator::make(
                ['blocks' => $layout['blocks']],
                ['blocks' => ['required', 'array', new ValidatesBlockSchema]],
            );

            foreach ($blockValidator->errors()->all() as $message) {
                $fail("The shared page layout '{$layoutKey}' is invalid: {$message}");
            }

            $blockIds = [];
            $this->collectBlockIds($layout['blocks'], $blockIds);

            if (count($blockIds) !== count(array_unique($blockIds))) {
                $fail("The shared page layout '{$layoutKey}' must use unique block IDs.");
            }
        }
    }

    /**
     * @param  array<string, mixed>  $siteKits
     * @param  array<string, mixed>  $styles
     * @param  array<string, mixed>  $pageLayouts
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    private function validateSiteKits(
        array $siteKits,
        array $styles,
        array $pageLayouts,
        Closure $fail,
    ): void {
        foreach ($siteKits as $kitKey => $siteKit) {
            if (! is_string($kitKey) || ! is_array($siteKit)) {
                $fail('Every site kit must have a stable string key and array definition.');

                continue;
            }

            foreach (['label', 'industry', 'description', 'style_key'] as $requiredString) {
                if (! isset($siteKit[$requiredString]) || ! is_string($siteKit[$requiredString]) || $siteKit[$requiredString] === '') {
                    $fail("The site kit '{$kitKey}' must contain a non-empty {$requiredString}.");
                }
            }

            if (! isset($siteKit['tier']) || ! is_string($siteKit['tier']) || ! in_array($siteKit['tier'], ['free', 'premium'], true)) {
                $fail("The site kit '{$kitKey}' must declare tier as 'free' or 'premium'.");
            }

            if (isset($siteKit['style_key']) && is_string($siteKit['style_key']) && ! array_key_exists($siteKit['style_key'], $styles)) {
                $fail("The site kit '{$kitKey}' references unknown style '{$siteKit['style_key']}'.");
            }

            if (! isset($siteKit['pages']) || ! is_array($siteKit['pages']) || $siteKit['pages'] === []) {
                $fail("The site kit '{$kitKey}' must contain at least one page.");

                continue;
            }

            $this->validateSiteKitPages($kitKey, $siteKit['pages'], $pageLayouts, $fail);
        }
    }

    /**
     * @param  array<int, mixed>  $pages
     * @param  array<string, mixed>  $pageLayouts
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    private function validateSiteKitPages(
        string $kitKey,
        array $pages,
        array $pageLayouts,
        Closure $fail,
    ): void {
        $slugs = [];
        $homepageCount = 0;

        foreach ($pages as $pageIndex => $page) {
            if (! is_array($page)) {
                $fail("The site kit '{$kitKey}' page at index {$pageIndex} must be an array.");

                continue;
            }

            foreach (['title', 'slug', 'layout_key'] as $requiredString) {
                if (! isset($page[$requiredString]) || ! is_string($page[$requiredString]) || $page[$requiredString] === '') {
                    $fail("The site kit '{$kitKey}' page at index {$pageIndex} must contain a non-empty {$requiredString}.");
                }
            }

            if (isset($page['slug']) && is_string($page['slug'])) {
                if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $page['slug']) !== 1) {
                    $fail("The site kit '{$kitKey}' page slug '{$page['slug']}' must use lowercase URL-safe characters.");
                }

                $slugs[] = $page['slug'];
            }

            if (isset($page['layout_key']) && is_string($page['layout_key']) && ! array_key_exists($page['layout_key'], $pageLayouts)) {
                $fail("The site kit '{$kitKey}' references unknown page layout '{$page['layout_key']}'.");
            }

            if (! isset($page['is_homepage']) || ! is_bool($page['is_homepage'])) {
                $fail("The site kit '{$kitKey}' page at index {$pageIndex} must contain a boolean is_homepage value.");
            } elseif ($page['is_homepage']) {
                $homepageCount++;
            }
        }

        if (count($slugs) !== count(array_unique($slugs))) {
            $fail("The site kit '{$kitKey}' must use unique page slugs.");
        }

        if ($homepageCount !== 1) {
            $fail("The site kit '{$kitKey}' must define exactly one homepage.");
        }
    }

    /**
     * @param  array<int, mixed>  $nodes
     * @param  array<int, string>  $blockIds
     */
    private function collectBlockIds(array $nodes, array &$blockIds): void
    {
        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            if (isset($node['id']) && is_string($node['id'])) {
                $blockIds[] = $node['id'];
            }

            if (isset($node['children']) && is_array($node['children'])) {
                $this->collectBlockIds($node['children'], $blockIds);
            }
        }
    }
}
