<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidatesCommerceTemplate implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value) || ($value['schemaVersion'] ?? null) !== 1 || ! is_array($value['sections'] ?? null)) {
            $fail('The :attribute must be a version 1 commerce template.');

            return;
        }

        $ids = [];

        foreach ($value['sections'] as $section) {
            if (! is_array($section)
                || ! is_string($section['id'] ?? null)
                || ! is_string($section['type'] ?? null)
                || ! is_array($section['settings'] ?? null)
                || ! is_array($section['blocks'] ?? null)
                || ! is_bool($section['disabled'] ?? null)) {
                $fail('Every commerce section must define id, type, settings, blocks, and disabled.');

                return;
            }

            if (in_array($section['id'], $ids, true)) {
                $fail('Commerce section IDs must be unique.');

                return;
            }

            $ids[] = $section['id'];
        }
    }
}
