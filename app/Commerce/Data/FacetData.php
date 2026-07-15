<?php

namespace App\Commerce\Data;

final readonly class FacetData
{
    public function __construct(public string $key, public string $label, public array $values) {}
}
