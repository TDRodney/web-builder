<?php

namespace App\Commerce\Contracts;

use App\Commerce\Data\CartData;
use App\Commerce\Data\CollectionData;
use App\Commerce\Data\ProductData;

interface CommerceProvider
{
    public function product(string $handle): ProductData;

    public function collection(string $handle, array $query = []): CollectionData;

    public function cart(?string $cartId = null): CartData;

    public function addCartLine(string $cartId, string $variantId, int $quantity): CartData;

    public function updateCartLine(string $cartId, string $lineId, int $quantity): CartData;

    public function removeCartLine(string $cartId, string $lineId): CartData;

    public function checkout(string $cartId): string;
}
