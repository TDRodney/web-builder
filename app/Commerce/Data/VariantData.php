<?php

namespace App\Commerce\Data;

final readonly class VariantData
{
    public function __construct(public string $id, public string $title, public array $options, public MoneyData $price, public bool $available) {}
}
