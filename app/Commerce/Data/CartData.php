<?php

namespace App\Commerce\Data;

final readonly class CartData
{
    public function __construct(public string $id, public array $lines, public MoneyData $subtotal, public bool $canCheckout) {}
}
