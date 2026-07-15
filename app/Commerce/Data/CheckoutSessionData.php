<?php

namespace App\Commerce\Data;

final readonly class CheckoutSessionData
{
    public function __construct(public string $url) {}
}
