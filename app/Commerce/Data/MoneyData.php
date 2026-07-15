<?php

namespace App\Commerce\Data;

final readonly class MoneyData
{
    public function __construct(public string $amount, public string $currency, public string $formatted) {}
}
