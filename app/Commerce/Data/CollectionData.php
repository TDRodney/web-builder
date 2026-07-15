<?php

namespace App\Commerce\Data;

final readonly class CollectionData
{
    public function __construct(public string $id, public string $handle, public string $title, public array $products, public array $facets = [], public ?string $nextCursor = null, public ?string $templateKey = null) {}
}
