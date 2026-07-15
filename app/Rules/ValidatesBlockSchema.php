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

        $validTypes = array_keys(config('blocks.definitions', []));
        if (! in_array($node['type'], $validTypes)) {
            $fail("The block at {$path} has an unrecognized type '{$node['type']}'.");
        }

        if (! isset($node['props']) || ! is_array($node['props'])) {
            $fail("The block at {$path} must contain a 'props' array.");
        } else {
            $this->validateCommerceBinding($node['props'], $path, $fail);
        }

        if (isset($node['children'])) {
            if (! is_array($node['children'])) {
                $fail("The block at {$path} contains 'children' which must be an array.");

                return;
            }

            $nestingRules = config('blocks.nesting', []);
            $parentType = $node['type'];
            $allowedChildren = $nestingRules[$parentType] ?? null;

            foreach ($node['children'] as $index => $child) {
                if (is_array($child) && isset($child['type']) && $allowedChildren !== null) {
                    if (! in_array($child['type'], $allowedChildren)) {
                        $fail("The block at {$path}.children.{$index} of type '{$child['type']}' is not allowed inside a parent '{$parentType}'.");
                    }
                }
                $this->validateNode($child, "{$path}.children.{$index}", $fail);
            }
        }
    }

    /**
     * Validate the additive commerce binding fields without changing legacy block schemas.
     *
     * @param  array<string, mixed>  $props
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    protected function validateCommerceBinding(array $props, string $path, Closure $fail): void
    {
        if (isset($props['bindingVersion']) && (! is_int($props['bindingVersion']) || $props['bindingVersion'] !== 1)) {
            $fail("The commerce binding at {$path} must use bindingVersion 1.");
        }

        if (isset($props['sourceKey']) && (! is_string($props['sourceKey']) || strlen($props['sourceKey']) > 100 || ($props['sourceKey'] !== '' && preg_match('/^[A-Za-z0-9._-]+$/', $props['sourceKey']) !== 1))) {
            $fail("The commerce sourceKey at {$path} must be a safe string of at most 100 characters.");
        }

        if (! isset($props['dataBinding'])) {
            return;
        }

        $binding = $props['dataBinding'];

        if (! is_array($binding)
            || ($binding['version'] ?? null) !== 1
            || ! is_string($binding['resource'] ?? null)
            || ! is_string($binding['source'] ?? null)
            || ($binding['source'] ?? '') === '') {
            $fail("The dataBinding at {$path} must contain version 1, resource, and source values.");
        }
    }
}
