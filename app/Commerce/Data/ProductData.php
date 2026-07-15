<?php

namespace App\Commerce\Data;

final readonly class ProductData
{
    public function __construct(public string $id, public string $handle, public string $title, public string $description, public array $images, public array $variants, public ?string $templateKey = null) {}
}
