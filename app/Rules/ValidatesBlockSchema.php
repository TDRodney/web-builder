<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidatesBlockSchema implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            $fail('The :attribute must be an array of blocks.');

            return;
        }

        foreach ($value as $index => $node) {
            $this->validateNode($node, "{$attribute}.{$index}", $fail);
        }
    }

    /**
     * Recursively validate a single block node.
     */
    protected function validateNode(mixed $node, string $path, Closure $fail): void
    {
        if (! is_array($node)) {
            $fail("The block at {$path} must be a valid array.");

            return;
        }

        if (! isset($node['id']) || ! is_string($node['id'])) {
            $fail("The block at {$path} is missing a valid string 'id'.");
        }

        if (! isset($node['type']) || ! is_string($node['type'])) {
            $fail("The block at {$path} is missing a valid string 'type'.");

            return;
        }

        $validTypes = ['HeroBlock', 'FeatureBlock', 'LayoutGrid', 'LayoutColumn', 'AtomicText'];
        if (! in_array($node['type'], $validTypes)) {
            $fail("The block at {$path} has an unrecognized type '{$node['type']}'.");
        }

        if (! isset($node['props']) || ! is_array($node['props'])) {
            $fail("The block at {$path} must contain a 'props' array.");
        }

        if (isset($node['children'])) {
            if (! is_array($node['children'])) {
                $fail("The block at {$path} contains 'children' which must be an array.");

                return;
            }

            foreach ($node['children'] as $index => $child) {
                $this->validateNode($child, "{$path}.children.{$index}", $fail);
            }
        }
    }
}
